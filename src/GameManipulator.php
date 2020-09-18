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
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Utils;

class GameManipulator
{
    /**
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * @var GameRepository $gameRepository
     */
    private $gameRepository;

    /**
     * @var ShipRepository $shipRepository
     */
    private $shipRepository;

    /**
     * @var ShootRepository $shootRepository
     */
    private $shootRepository;

    /**
     * @var PlayerRepository $playerRepository
     */
    private $playerRepository;

    /**
     * @var SessionInterface $session
     */
    private $session;

    /**
     * Inject repositories
     * 
     * @param EntityManagerInterface $entityManager
     * @param ShipRepository $shipRepository
     * @param ShootRepository $shootRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        GameRepository $gameRepository,
        ShipRepository $shipRepository,
        ShootRepository $shootRepository,
        PlayerRepository $playerRepository,
        SessionInterface $session
    ) {
        $this->entityManager = $entityManager;
        $this->gameRepository = $gameRepository;
        $this->shipRepository = $shipRepository;
        $this->shootRepository = $shootRepository;
        $this->playerRepository = $playerRepository;
        $this->session = $session;
    }

    /**
     * Create a new game
     * 
     * @return Game
     */
    public function createGame(): Game
    {
        $game = new Game();
        $game->setHash(Utils::generateRandomGameHash());

        $player1 = null;

        $hash = $this->session->get('player_hash');
        if ($hash) {
            $player1 = $this->playerRepository->findOneBy(['hash' => $hash]);
        }

        if (!$player1) {
            $player1 = new Player();
            $player1->setName('PLAYER 1');
            $player1->setHash(Utils::generateRandomGameHash(50));
        }

        $player2 = new Player();
        $player2->setName('PLAYER 2');

        $game->setPlayer1($player1);
        $game->setPlayer2($player2);
        $game->setCurrentPlayer($player1);

        $game->setStatus(GameStatusEnum::OPEN);

        $game = $this->initShipsForPlayer($game, $player1);

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return $game;
    }

    /**
     * Create randomly positioned ships and add them to the player
     * 
     * @param Game $game
     * @param Player $player
     * 
     * @return Game
     */
    public function initShipsForPlayer(Game $game, Player $player): Game
    {
        $config = [
            'ships' => [5, 4, 3, 3, 2],
            'grid' => 10
        ];

        $orientations = [GameConstants::VERTICAL, GameConstants::HORIZONTAL];

        foreach ($config['ships'] as $shipLength) {
            $ship = $this->getRandShip($shipLength, $orientations[array_rand($orientations)], $game);
            $ship->setPlayer($player);
            $game->addShip($ship);
        }

        return $game;
    }

    /**
     * Add a second player to the game
     * 
     * @param string $gameHash
     * 
     * @return Game|null
     */
    public function joinGame(string $gameHash): ?Game
    {
        $game = $this->gameRepository->findOneBy(['hash' => $gameHash]);

        if (!$game) {
            return null;
        }

        if ($this->isRunningGame($game)) {
            return null;
        }

        $player2 = $game->getPlayer2();

        $hash = $this->session->get('player_hash');

        if ($hash) {
            $player2 = $this->playerRepository->findOneBy(['hash' => $hash]);
        }

        if (!$player2) {
            $player2 = new Player();
            $player2->setName('PLAYER 2');

            $hash = Utils::generateRandomGameHash(50);
            $player2->setHash($hash);
            $this->session->set('player_hash', $hash);
        }

        $game->setPlayer2($player2);
        $game = $this->initShipsForPlayer($game, $player2);
        $game->setStatus(GameStatusEnum::RUNNING);

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return $game;
    }

    /**
     * Check if the game has already started
     * 
     * @param Game $game
     * 
     * @return bool
     */
    public function isRunningGame(Game $game): bool
    {
        return $game->getStatus() == GameStatusEnum::RUNNING;
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
    public function isAbandoned(Game $game): bool
    {
        return $game->getStatus() === GameStatusEnum::ABANDONED;
    }

    /**
     * Tells if one of the player won the game
     * 
     * @param Game $game
     * 
     * @return Player|null
     */
    public function getWinner(Game $game): ?Player
    {
        if (!$game->getPlayer1() || !$game->getPlayer2()) {
            return null;
        }
        
        if ($this->hasSunkenFleet($game, $game->getPlayer1())) {
            return $game->getPlayer2();
        } elseif ($this->hasSunkenFleet($game, $game->getPlayer2())) {
            return $game->getPlayer1();
        }

        return null;
    }

    /**
     * Update the status of the game to OVER
     * 
     * @param Game $game
     * 
     * @return Game
     */
    public function setGameAsWon(Game $game, Player $player): Game
    {
        $game->setWinner($player);
        $game->setStatus(GameStatusEnum::OVER);
        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return $game;
    }

    /**
     * Check if the game is already finished
     * 
     * @param Game $game
     * 
     * @return bool
     */
    public function isGameOver($game): bool
    {
        return $game->getStatus() == GameStatusEnum::OVER;
    }

    /**
     * Used to know if all the ships of a player have sunk
     * 
     * @param Game $game
     * @param Player $player
     * 
     * @return bool
     */
    public function hasSunkenFleet(Game $game, Player $player): bool
    {
        $ships = $this->shipRepository->getPlayerShips($game, $player);
        foreach ($ships as $ship) {
            if (!$this->isShipSunk($ship, $game, $this->getOpponentPlayer($game, $player))) {
                return false;
            }
        }

        return true;
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
    public function shoot(array $coordinates, Game $game): Shoot
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
     * @param Player $player
     * 
     * @return Player
     */
    public function getOpponentPlayer(Game $game, Player $player): Player
    {
        if ($player === $game->getPlayer1()) {
            return $game->getPlayer2();
        }

        return $game->getPlayer1();
    }

    /**
     * Get all the current player shoots that hit an opponent ship
     * 
     * @param Game $game
     * @param Player $player
     * 
     * @return array
     */
    public function getPlayerHits(Game $game, Player $player): array
    {
        $hits = [];

        $opponentShips = $this->shipRepository
            ->getPlayerShips($game, $this->getOpponentPlayer($game, $player));

        $collection = new ArrayCollection($opponentShips);

        $opponentShipsCoordinates = $collection->map(function ($ship) {
            return $ship->getCoordinates();
        });

        $shoots = new ArrayCollection(
            $this->shootRepository->getPlayerShoots($game, $player)
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
    public function switchCurrentPlayer(Game $game): Game
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
     * @param Player $player
     * 
     * @return Ship|null
     */
    public function didShootHitOpponentShip(Shoot $shoot, Game $game, Player $shootingPlayer): ?Ship
    {
        $opponentShips = $this->shipRepository->getPlayerShips($game, $this->getOpponentPlayer($game, $shootingPlayer));

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
     * @param Player $player
     * 
     * @return bool
     */
    public function isShipSunk(Ship $ship, Game $game, Player $shootingPlayer): bool
    {
        foreach ($ship->getCoordinates() as $coordinates) {
            $isCoordHit = false;

            foreach ($this->shootRepository->getPlayerShoots($game, $shootingPlayer) as $shoot) {
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
     * @param Player $player
     *  
     * @return array
     */
    public function getOpponentShipsSunk(Game $game, Player $shootingPlayer): array
    {
        $sunkShips = [];

        $opponentShips = $this
            ->shipRepository
            ->getPlayerShips($game, $this->getOpponentPlayer($game, $shootingPlayer));

        foreach ($opponentShips as $ship) {
            if ($this->isShipSunk($ship, $game, $shootingPlayer)) {
                $sunkShips[] = $ship;
            }
        }

        return $sunkShips;
    }

    /**
     * Get a ship with random coordinates
     * 
     * @param int $length
     * @param string $orientation
     * @param Game $game
     * 
     * @return Ship
     */
    public function getRandShip(int $length, string $orientation, Game $game): Ship
    {
        $positionOk = false;

        if ($orientation === GameConstants::HORIZONTAL) {

            while (!$positionOk) {
                $coordinates = [];
                $allCoordsChecked = true;

                // horizontal ship
                $line = rand(0, 9);
                $column = rand(0, 10 - $length);

                for ($i = $column; $i < $column + $length; $i++) {
                    if ($game->getShipAt($line, $i)) {
                        $allCoordsChecked = false;
                        break;
                    }

                    $coordinates[] = [$line, $i];
                }

                if ($allCoordsChecked) {
                    $positionOk = true;
                }
            }
        } else {
            while (!$positionOk) {
                $coordinates = [];
                $allCoordsChecked = true;

                // vertical ship
                $line = rand(0, 10 - $length);
                $column = rand(0, 9);

                for ($i = $line; $i < $line + $length; $i++) {
                    if ($game->getShipAt($i, $column)) {
                        $allCoordsChecked = false;
                        break;
                    }

                    $coordinates[] = [$i, $column];
                }

                if ($allCoordsChecked) {
                    $positionOk = true;
                }
            }
        }

        $ship = new Ship();
        $ship->setCoordinates($coordinates);

        return $ship;
    }
}
