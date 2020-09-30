<?php

namespace App\Controller\Api;

use App\Entity\Game;
use App\Enum\GameStatusEnum;
use App\GameManipulator;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\ShipRepository;
use App\Repository\ShootRepository;
use App\Twig\AppExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class GameController extends AbstractController
{
    /**
     * Get open games
     * 
     * @param GameRepository $gameRepository
     * @return JsonResponse
     * @Route("/api/games", name="games_index", methods={"GET"})
     */
    public function index(GameRepository $gameRepository)
    {
        $games = $gameRepository->findBy(['status' => GameStatusEnum::OPEN]);

        return $this->json([
            'status' => 200,
            'success' => "Open games fetched successfully",
            'games' => $games
        ]);
    }

    /**
     * Create a game and return its hash to the player
     * 
     * @param GameRepository $gameRepository
     * @return JsonResponse
     * @Route("/api/games", name="games_create", methods={"POST"})
     */
    public function create(GameManipulator $gameManipulator)
    {
        $game = $gameManipulator->createGame();

        return $this->json([
            'status' => 200,
            'success' => "Game created successfully",
            'game_hash' => $game->getHash(),
            'player_hash' => $game->getPlayer1()->getHash()
        ]);
    }

    /**
     * Get a game
     * 
     * @ParamConverter("game", options={"mapping": {"hash": "hash"}})
     * @param GameRepository $gameRepository
     * @return JsonResponse
     * @Route("/api/games/{hash}", name="games_show", methods={"GET"})
     */
    public function show(Request $request, Game $game, PlayerRepository $playerRepository, ShipRepository $shipRepository, ShootRepository $shootRepository, GameManipulator $gameManipulator, AppExtension $appExtension)
    {
        $player = $playerRepository->findOneBy(['hash' => $request->query->get('player_hash')]);

        $ships = $shipRepository->getPlayerShips($game, $player);
        $hits = $gameManipulator->getPlayerHits($game, $player);
        $shoots = $shootRepository->getPlayerShoots($game, $player);
        $opponentSunkShips = $gameManipulator->getOpponentShipsSunk($game, $player);

        return $this->json([
            'status' => 200,
            'success' => "Game fetched successfully",
            'game' => $game->getHash(),
            'current_player' => $game->getCurrentPlayer(),
            'player1' => $game->getPlayer1(),
            'player2' => $game->getPlayer2(),
            'ships' => $ships,
            'shoots' => $shoots,
            'hits' => $hits,
            'opponent_sunk_ships' => $opponentSunkShips,
        ]);
    }

    /**
     * Join a game
     * 
     * @ParamConverter("game", options={"mapping": {"hash": "hash"}})
     * @param GameManipulator $gameManipulator
     * @return JsonResponse
     * @Route("/api/games/{hash}/join", name="games_join", methods={"PUT"})
     */
    public function join(Game $game, GameManipulator $gameManipulator) 
    {
        $game = $gameManipulator->joinGame($game->getHash());

        if (!$game) {
            return $this->json([
                'status' => 404,
                'error' => 'Game not found'
            ], 404);
        }

        return $this->json([
            'status' => 200,
            'success' => "Game joined successfully",
            'game_hash' => $game->getHash(),
            'player_hash' => $game->getPlayer2()->getHash()
        ]);
    }
}
