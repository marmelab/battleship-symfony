<?php

namespace App;

use App\Entity\Game;
use App\Repository\ShootRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AI
{

    private $gameManager;
    private $client;
    private $shootRepository;
    private $gameManipulator;

    public function __construct(HttpClientInterface $client, ShootRepository $shootRepository, GameManipulator $gameManipulator) {
        $this->client = $client;
        $this->shootRepository = $shootRepository;
        $this->gameManipulator = $gameManipulator;
    }

    private function post($body, $endpoint)
    {
        $response = $this->client->request(
            'POST',
            'http://api:8383' . $endpoint,
            ['body' => $body]
        );

        return $response;
    }

    public function getHeatMap(Game $game): array
    {
        $body = $this->getBodyFromGame($game);
        $response = $this->post($body, '');
        return $response->toArray();
    }

    public function getNextShots(Game $game): array
    {
        $body = $this->getBodyFromGame($game);
        $response = $this->post($body, '/next-shots');
        return $response->toArray();
    }

    private function getBodyFromGame(Game $game): string
    {
        $shoots = $this->shootRepository->getCurrentPlayerShoots($game);

        // dump($shoots);
        // die();
        $body = [
            'Size' => 10,
            // 'Ships' => $this->formatShips($game->getShips()),
            'Shots' => $this->formatShots($shoots, $game),
        ];

        // dump($body);
        // die();

        return json_encode($body);
    }

    private function formatShots($shots, $game)
    {
        $array = [];

        foreach ($shots as $shot) {
            $array[] = [
                'Cell' => [
                    'Row' => intval($shot->getCoordinates()[0]),
                    'Column' => intval($shot->getCoordinates()[1]),
                ],
                'State' => $this->gameManipulator->didShootHitOpponentShip($shot, $game, $game->getCurrentPlayer()) ? 'HIT' : ''
            ];
        }

        return $array;
    }

    private function formatShips($ships)
    {
        $array = [];

        foreach ($ships as $ship) {
            $array[] = [
                'Length' => count($ship->getCoordinates()),
                'Cells' => $ship->getCoordinates()
            ];
        }

        return $array;
    }
}
