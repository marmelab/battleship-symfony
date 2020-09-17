<?php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\Ship;
use App\Form\Type\CreateGameType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\GameManipulator;
use Symfony\Component\HttpFoundation\Request;

class WelcomeController extends AbstractController
{
    /**
     * Used to create the game
     * 
     * @var GameManipulator $gameManipulator
     */
    private $gameManipulator;

    /**
     * Inject GameManipulator for the game creation
     * 
     * @param GameManipulator $gameManipulator
     */
    public function __construct(GameManipulator $gameManipulator)
    {
        $this->gameManipulator = $gameManipulator;
    }

    public function index(Request $request): Response
    {        
        $form = $this->createForm(CreateGameType::class, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $game = $this->gameManipulator->createGame();

            return $this->redirectToRoute('game_index', ['hash' => $game->getHash()]);
        }

        return $this->render('welcome.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
