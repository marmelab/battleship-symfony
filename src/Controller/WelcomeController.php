<?php
namespace App\Controller;

use App\Form\Type\CreateGameType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\GameManipulator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class WelcomeController extends AbstractController
{
    /**
     * Used to create the game
     * 
     * @var GameManipulator $gameManipulator
     */
    private $gameManipulator;

    /**
     * Used to init the player 1 hash
     * 
     * @var SessionInterface $session
     */
    private $session;

    public function __construct(GameManipulator $gameManipulator, SessionInterface $session)
    {
        $this->gameManipulator = $gameManipulator;
        $this->session = $session;
    }

    public function index(Request $request): Response
    {        
        $form = $this->createForm(CreateGameType::class, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $game = $this->gameManipulator->createGame();

            $this->session->set('player_hash', $game->getPlayer1()->getHash());

            return $this->redirectToRoute('game_index', ['hash' => $game->getHash()]);
        }

        return $this->render('welcome.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
