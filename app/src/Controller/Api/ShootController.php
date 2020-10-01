<?php

namespace App\Controller\Api;

use App\GameManipulator;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\ShootRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ShootController extends AbstractController
{
    /**
     * Shoot at a position
     * 
     * @param GameRepository $gameRepository
     * @Route("/api/shoots", name="shoots_create", methods={"POST"})
     */
    public function create(Request $request, GameRepository $gameRepository, GameManipulator $gameManipulator, ShootRepository $shootRepository, PlayerRepository $playerRepository)
    {
        $content = $request->getContent();
        $parametersAsArray = json_decode($content, true);

        $game = $gameRepository->findOneBy(['hash' => $parametersAsArray['gameHash']]);

        $player = $playerRepository->findOneBy(['hash' => $parametersAsArray['playerHash']]);

        if ($game->getCurrentPlayer() !== $player) {
            return $this->json([
                'status' => 403,
                'error' => "This is not your turn",
            ]);
        }

        $shoot = $gameManipulator->shoot($parametersAsArray['coordinates'], $game);

        $shipHit = $gameManipulator->didShootHitOpponentShip($shoot, $game, $player);
        if (!$shipHit) {
            $gameManipulator->switchCurrentPlayer($game);
        }

        $hits = $gameManipulator->getPlayerHits($game, $player);
        $shoots = $shootRepository->getPlayerShoots($game, $player);

        return $this->json([
            'status' => 200,
            'success' => "Shoot created successfully",
            'game_hash' => $game->getHash(),
            'current_player' => $game->getCurrentPlayer(),
            'hits' => $hits,
            'shoots' => $shoots,
        ]);
    }
}
