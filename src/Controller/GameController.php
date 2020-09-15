<?php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\Ship;
use App\Constants\GameConstants;
use App\GameManipulator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GameController extends AbstractController
{
    private $gameManipulator;

    public function __construct(GameManipulator $gameManipulator)
    {
        $this->gameManipulator = $gameManipulator;
    }

    public function random(): Response
    {        
        $game = $this->gameManipulator->createGame();

        return $this->render('game.html.twig', [
            'game' => $game,
        ]);
    }
}
