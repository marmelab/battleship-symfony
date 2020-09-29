<?php

namespace App\Controller\Api;

use App\Entity\Game;
use App\GameManipulator;
use App\Repository\GameRepository;
use App\Repository\ShipRepository;
use App\Twig\AppExtension;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class GameController extends AbstractController
{
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
            'game_hash' => $game->getHash()
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
    public function show(Game $game, ShipRepository $shipRepository, AppExtension $appExtension)
    {
        $ships = $shipRepository->getCurrentPlayerShips($game);

        $array =[];

        foreach ($ships as $ship) {
            $array[] = [
                'coordinates' => $ship->getCoordinates(),
                'grid_attributes' => $appExtension->getGridAttributes($ship)
            ];  
        }

        return $this->json([
            'status' => 200,
            'success' => "Game fetched successfully",
            'game_hash' => $game->getHash(),
            'ships' => $array
        ]);
    }
}
