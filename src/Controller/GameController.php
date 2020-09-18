<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Player;
use App\Entity\Ship;
use App\Entity\Shoot;
use App\GameManipulator;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Form\Type\ShootShipType;
use App\Form\Type\AbandonGameType;
use App\PlayerManipulator;
use App\Provider\PlayerProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameController extends AbstractController
{
    /**
     * Used to manage game's action coming from the players
     * 
     * @var GameManipulator $gameManipulator
     */
    private $gameManipulator;

    /**
     * Translate the wording displayed
     * 
     * @var TranslatorInterface $translator
     */
    private $translator;

    /**
     * @var PlayerProvider $playerProvider
     */
    private $playerProvider;

    /**
     * Inject and initialize the services used in the controller
     * 
     * @param GameManipulator $gameManipulator
     * @param TranslatorInterface $translator
     * @param PlayerProvider $playerProvider
     */
    public function __construct(
        GameManipulator $gameManipulator,
        PlayerManipulator $playerManipulator,
        TranslatorInterface $translator,
        PlayerProvider $playerProvider,
        SessionInterface $session
    ) {
        $this->gameManipulator = $gameManipulator;
        $this->playerManipulator = $playerManipulator;
        $this->translator = $translator;
        $this->playerProvider = $playerProvider;
        $this->session = $session;
    }

    /**
     * @ParamConverter("game", options={"mapping": {"hash": "hash"}})
     */
    public function index(Request $request, Game $game): Response
    {
        if ($this->gameManipulator->isAbandoned($game)) {
            return $this->render('abandoned.html.twig');
        }

        $winner = $this->gameManipulator->hasPlayerWon($game);
        if ($winner) {
            return $this->render('win.html.twig', ['winner' => $winner]);
        }

        $gamePlayer = $this->playerProvider->getPlayer($game);
        $currentPlayer = $game->getCurrentPlayer();

        $abandonForm = $this->createForm(AbandonGameType::class);
        $abandonForm->handleRequest($request);

        if ($abandonForm->isSubmitted() && $abandonForm->isValid()) {
            $this->gameManipulator->abandonGame($game);

            return $this->redirectToRoute('index');
        }

        // TODO: extract all the view variables creation
        $triggerViews = [];
        if ($currentPlayer == $gamePlayer) {
            $triggerViews = $this->createTriggersForms($game);
            $trigger = $this->createForm(ShootShipType::class)->handleRequest($request);

            if ($trigger->isSubmitted() && $trigger->isValid()) {
                $this->shootOpponentFleet($trigger->getData()['coordinates'], $game, $gamePlayer);

                return $this->redirectToRoute('game_index', ['hash' => $game->getHash()]);
            }
        }

        $hits = $this->gameManipulator->getPlayerHits($game, $gamePlayer);

        $ships = $this
            ->getDoctrine()
            ->getRepository(Ship::class)
            ->getPlayerShips($game, $gamePlayer);

        $shoots = $this
            ->getDoctrine()
            ->getRepository(Shoot::class)
            ->getPlayerShoots($game, $gamePlayer);

        $opponentShips = $this
            ->getDoctrine()
            ->getRepository(Ship::class)
            ->getPlayerShips($game, $this->gameManipulator->getOpponentPlayer($game, $gamePlayer));

        $opponentSunkShips = $this->gameManipulator->getOpponentShipsSunk($game, $gamePlayer);

        return $this->render('game.html.twig', [
            'gamePlayer' => $gamePlayer,
            'game' => $game,
            'ships' => $ships,
            'opponent_ships' => $opponentShips,
            'opponent_ships_sunk' => $opponentSunkShips,
            'hits' => $hits,
            'shoots' => $shoots,
            'triggers' => $triggerViews,
            'abandon_form' => $abandonForm->createView()
        ]);
    }

    /**
     * Creates all the form views for shooting the opponent
     * 
     * @param Game $game
     * 
     * @return array
     */
    private function createTriggersForms(Game $game)
    {
        $triggerViews = [];

        foreach (range(0, 9) as $x) {
            foreach (range(0, 9) as $y) {
                $triggerViews[] = $this->createForm(ShootShipType::class, [
                    'game_hash' => $game->getHash(),
                    'coordinates' => join(",", [$x, $y])
                ])->createView();
            }
        }

        return $triggerViews;
    }

    /**
     * Shoot at opponent fleet and set correct message for the player
     * 
     * @param array $data
     * @param Game $game
     * 
     * @return null
     */
    private function shootOpponentFleet(string $coordinates, Game $game, Player $player): void
    {
        $coordinates = explode(',', $coordinates);

        $shoot = $this->gameManipulator->shoot($coordinates, $game);

        $shipHit = $this->gameManipulator->didShootHitOpponentShip($shoot, $game, $player);
        if ($shipHit) {
            if ($this->gameManipulator->isShipSunk($shipHit, $game, $player)) {
                $this->addFlash('shoot_result', $this->translator->trans('You have SUNK a ship! Well done!'));
            } else {
                $this->addFlash('shoot_result', $this->translator->trans('You hit a ship!'));
            }
        } else {
            $this->gameManipulator->switchCurrentPlayer($game);
            $this->addFlash('shoot_result', $this->translator->trans('You hit the water...'));
        }

        return;
    }
}
