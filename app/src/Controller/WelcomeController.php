<?php
namespace App\Controller;

use App\Enum\GameStatusEnum;
use App\Form\Type\CreateGameType;
use App\Form\Type\JoinGameType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\GameManipulator;
use App\Provider\PlayerProvider;
use App\Repository\GameRepository;
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
     * Used to init the player 1 hash in session
     * 
     * @var PlayerProvider $playerProvider
     */
    private $playerProvider;

    /**
     * Used to init the player 1 hash in session
     * 
     * @var GameRepository $gameRepository
     */
    private GameRepository $gameRepository;

    public function __construct(GameManipulator $gameManipulator, PlayerProvider $playerProvider, GameRepository $gameRepository)
    {
        $this->gameManipulator = $gameManipulator;
        $this->playerProvider = $playerProvider;
        $this->gameRepository = $gameRepository;
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

        $openGames = $this->gameRepository->findBy(['status' => GameStatusEnum::OPEN]);
        $joinForms = [];

        foreach ($openGames as $game) {
            $joinForms[] = $this->createForm(JoinGameType::class, [
                'game_hash' => $game->getHash()
            ])->createView();
        }
        
        $joinForm = $this->createForm(JoinGameType::class)->handleRequest($request);
        if ($joinForm->isSubmitted() && $joinForm->isValid()) {
                        
            $game = $this->gameManipulator->joinGame($joinForm->getData()['game_hash']);
            
            return $this->redirectToRoute('game_index', ['hash' => $game->getHash()]);
        }

        return $this->render('welcome.html.twig', [
            'join_forms' => $joinForms,
            'form' => $form->createView()
        ]);
    }
}
