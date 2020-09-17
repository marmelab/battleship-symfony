<?php

namespace App;

use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\Shoot;
use App\Entity\Player;
use App\Constants\GameConstants;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ShipRepository;
use App\Repository\ShootRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Enum\GameStatusEnum;

class GameManipulator
{
    private $entityManager;
    private $shipRepository;
    private $shootRepository;

    public function __construct(EntityManagerInterface $entityManager, ShipRepository $shipRepository, ShootRepository $shootRepository)
    {
        $this->entityManager = $entityManager;
        $this->shipRepository = $shipRepository;
        $this->shootRepository = $shootRepository;
    }

    /**
     * Create a new game
     * 
     * @return Game
     */
    public function createGame(): Game
    {
        $game = new Game();
        $game->setHash($this->generateRandomGameHash());
        
        $player1 = new Player();
        $player1->setName('PLAYER 1');

        $player2 = new Player();
        $player2->setName('PLAYER 2');

        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setCurrentPlayer($player1);
        
        $game->setStatus(GameStatusEnum::RUNNING);

        $config = [
            'ships' => [5,4,3,3,2],
            'grid' => 10
        ];

        $orientations = [GameConstants::VERTICAL, GameConstants::HORIZONTAL];

        foreach ($config['ships'] as $shipLength)
        {
            $ship = $this->getRandShip($shipLength, $orientations[array_rand($orientations)], $game);
            $ship->setPlayer($player1);
            $game->addShip($ship);
        }

        foreach ($config['ships'] as $shipLength)
        {
            $ship = $this->getRandShip($shipLength, $orientations[array_rand($orientations)], $game);
            $ship->setPlayer($player2);
            $game->addShip($ship);
        }

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return $game;
    }

    /**
     * Abandon a game finish it and update the status
     * 
     * @param Game $game
     * 
     * @return Game
     */
    public function abandonGame(Game $game): Game
    {
        $game->setStatus(GameStatusEnum::ABANDONED);
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return $game;
    }

    /**
     * Check if a game is abandoned
     * 
     * @param Game $game
     * 
     * @return bool
     */
    public function isAbandoned(Game $game): bool {
        return $game->getStatus() === GameStatusEnum::ABANDONED;
    }

    /**
     * Shoot at opponent's fleet.
     * Creates a shoot
     * 
     * @param array $coordinates
     * @param Game $game
     * 
     * @return Shoot
     */
    public function shoot($coordinates, $game): Shoot
    {
        $shoot = new Shoot();
        $shoot->setGame($game);
        $shoot->setPlayer($game->getCurrentPlayer());
        $shoot->setCoordinates($coordinates);

        $this->entityManager->persist($shoot);
        $this->entityManager->flush();

        return $shoot;
    }

    /**
     * Get current player's opponent
     * 
     * @param Game $game
     * 
     * @return Player
     */
    public function getOpponentPlayer(Game $game): Player
    {
        if ($game->getCurrentPlayer() === $game->getPlayer1()) {
            return $game->getPlayer2();
        }
        
        return $game->getPlayer1();
    }

    /**
     * Get all the current player shoots that hit an opponent ship
     * 
     * @param Game $game
     * 
     * @return array
     */
    public function getCurrentPlayerHits(Game $game): array
    {        
        $hits = [];
    
        $opponentShips = $this->shipRepository
            ->getPlayerShips($game, $this->getOpponentPlayer($game));

        $collection = new ArrayCollection($opponentShips);

        $opponentShipsCoordinates = $collection->map(function($ship) {
            return $ship->getCoordinates();
        });

        $shoots = new ArrayCollection(
            $this->shootRepository->getCurrentPlayerShoots($game)
        );
        
        foreach ($opponentShips as $ship) {
            foreach ($ship->getCoordinates() as $shipCoord) {
                foreach ($shoots as $shoot) {
                    if ($shipCoord == $shoot->getCoordinates()) {
                        $hits[] = $shoot;
                    }
                }
            }
        }

        return $hits;
    }

    /**
     * Switch game current player
     * 
     * @param Game $game
     * 
     * @return Game
     */
    public function switchCurrentPlayer($game): Game
    {
        if ($game->getCurrentPlayer() === $game->getPlayer1()) {
            $game->setCurrentPlayer($game->getPlayer2());
        } else {
            $game->setCurrentPlayer($game->getPlayer1());
        }

        $this->entityManager->persist($game);
        $this->entityManager->flush();
    
        return $game;
    }

    /**
     * Return the ship hit by a shoot or null
     * 
     * @param Shoot $shoot
     * @param Game $game
     * 
     * @return Ship|null
     */
    public function didShootHitOpponentShip(Shoot $shoot, Game $game): ?Ship
    {
        $opponentShips = $this->shipRepository->getPlayerShips($game, $this->getOpponentPlayer($game));

        foreach ($opponentShips as $ship) {
            foreach ($ship->getCoordinates() as $coordinates) {
                if ($coordinates == $shoot->getCoordinates()) {
                    return $ship;
                }
            }
        }

        return null;
    }

    /**
     * Check if the given ship has sunk.
     * It means all its coordinates should exist in the currentPlayer shoots property.
     * 
     * @param Ship $ship
     * @param Game $game
     * 
     * @return bool
     */
    public function isShipSunk(Ship $ship, Game $game): bool
    {
        foreach ($ship->getCoordinates() as $coordinates) {
            $isCoordHit = false;

            foreach ($this->shootRepository->getCurrentPlayerShoots($game) as $shoot) {
                if ($shoot->getCoordinates() == $coordinates) {
                    $isCoordHit = true;
                    break;
                }
            }

            if (!$isCoordHit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get opponent ships that have been sunk
     * 
     * @param Game $game 
     *  
     * @return array
     */
    public function getOpponentShipsSunk(Game $game): array {
        $sunkShips = [];

        $opponentShips = $this
            ->shipRepository
            ->getPlayerShips($game, $this->getOpponentPlayer($game));

        foreach ($opponentShips as $ship) {
            if ($this->isShipSunk($ship, $game)) {
                $sunkShips[] = $ship;
            }
        }

        return $sunkShips;
    }

    /**
     * Random string generator for game hash
     * 
     * @param int $length
     * 
     * @return string
     */
    private function generateRandomGameHash(int $length = 10): string {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Get a ship with random coordinates
     * 
     * @param int $length
     * @param int $orientation
     * @param int $game
     * 
     * @return Ship
     */
    public function getRandShip(int $length, string $orientation, Game $game): Ship
    {
        $positionOk = false;

        if ($orientation === GameConstants::HORIZONTAL)
        {

            while (!$positionOk)
            {
                $coordinates = [];
                $allCoordsChecked = true;

                // horizontal ship
                $line = rand(0, 9);
                $column = rand(0, 10 - $length);
                
                for ($i=$column; $i < $column + $length; $i++)
                {
                    if ($game->getShipAt($line, $i))
                    {
                        $allCoordsChecked = false;
                        break;
                    }                

                    $coordinates[] = [$line, $i];
                }

                if ($allCoordsChecked)
                {
                    $positionOk = true;
                }
            }
        } 
        else 
        {
            while (!$positionOk)
            {
                $coordinates = [];
                $allCoordsChecked = true;

                // vertical ship
                $line = rand(0, 10 - $length);
                $column = rand(0, 9);

                for ($i=$line; $i < $line + $length; $i++)
                {
                    if ($game->getShipAt($i, $column))
                    {
                        $allCoordsChecked = false;
                        break;
                    }                

                    $coordinates[] = [$i, $column];
                }

                if ($allCoordsChecked)
                {
                    $positionOk = true;
                }
            }
        }

        $ship = new Ship();
        $ship->setCoordinates($coordinates);

        return $ship;
    }
}