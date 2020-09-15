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
        $game->setHash($this->generateRandomString());

        $player1 = new Player();
        $player1->setName('PLAYER 1');

        $player2 = new Player();
        $player2->setName('PLAYER 2');

        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setCurrentPlayer($player1);

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

    public function shoot($coordinates, $game) 
    {
        $shoot = new Shoot();
        $shoot->setGame($game);
        $shoot->setPlayer($game->getCurrentPlayer());
        $shoot->setCoordinates($coordinates);

        $this->entityManager->persist($shoot);
        $this->entityManager->flush();
    }

    public function getOpponentPlayer(Game $game)
    {
        if ($game->getCurrentPlayer() === $game->getPlayer1()) {
            return $game->getPlayer2();
        } else {
            return $game->getPlayer1();
        }
    }

    public function getCurrentPlayerHits(Game $game): array
    {        
        $opponentShips = $this->shipRepository
            ->getPlayerShips($game, $this->getOpponentPlayer($game));


        $collection = new ArrayCollection($opponentShips);

        $opponentShipsCoordinates = $collection->map(function($ship) {
            return $ship->getCoordinates();
        });


        $shoots = new ArrayCollection(
            $this->shootRepository->getCurrentPlayerShoots($game)
        );
        dump($opponentShipsCoordinates);
        die();
        $shoots->filter(function($shoot) use ($opponentShipsCoordinates) {
            return $opponentShipsCoordinates->exists(function($key, $coords) use ($shoot) {
                dump($coords, $shoot->getCoordinates());
                die();
                return $coords === $shoot->getCoordinates();
            });
        });

        dump($opponentShipsCoordinates);
        die();
    }

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
     * Random string generator for game hash
     * 
     * @param int $length
     * 
     * @return string
     */
    private function generateRandomString(int $length = 10) {
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