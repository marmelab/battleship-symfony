<?php

namespace App\Controller\Api;

use App\GameManipulator;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * Create a game and returns its hash to the player
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
            'game_hash' => $game->getHash()
        ]);
    }
}
