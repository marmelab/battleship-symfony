<?php
namespace App\Controller;

use App\Form\Type\CreateGameType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\GameManipulator;
use App\Provider\PlayerProvider;
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
     * Used to init the player 1 hash in session
     * 
     * @var PlayerProvider $playerProvider
     */
    private $playerProvider;

    public function __construct(GameManipulator $gameManipulator, PlayerProvider $playerProvider)
    {
        $this->gameManipulator = $gameManipulator;
        $this->playerProvider = $playerProvider;
    }

    public function index(Request $request): Response
    {        
        $form = $this->createForm(CreateGameType::class, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $game = $this->gameManipulator->createGame();

            $this->playerProvider->initFirstPlayerSession($game);

            return $this->redirectToRoute('game_index', ['hash' => $game->getHash()]);
        }

        return $this->render('welcome.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
