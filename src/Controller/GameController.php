<?php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\Shoot;
use App\GameManipulator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Form\Type\ShootShipType;
use App\Form\Type\AbandonGameType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class GameController extends AbstractController
{
    private $gameManipulator;

    private $translator;

    public function __construct(GameManipulator $gameManipulator, TranslatorInterface $translator)
    {
        $this->gameManipulator = $gameManipulator;
        $this->translator = $translator;
    }

    /**
     * @ParamConverter("game", options={"mapping": {"hash": "hash"}})
     */
    public function index(Request $request, Game $game): Response
    {
        if ($game->isAbandoned()) {
            return $this->render('abandoned.html.twig');
        }

        $abandonForm = $this->createForm(AbandonGameType::class);
        $abandonForm->handleRequest($request);

        if ($abandonForm->isSubmitted() && $abandonForm->isValid()) {
            $this->gameManipulator->abandonGame($game);

            return $this->redirectToRoute('index');
        }

        $triggerViews = $this->createTriggersForms($game);

        $trigger = $this->createForm(ShootShipType::class)->handleRequest($request);

        if ($trigger->isSubmitted() && $trigger->isValid()) {

            $this->manageShoot($trigger->getData(), $game);

            return $this->redirectToRoute('game_index', ['hash' => $game->getHash()]);
        }

        $hits = $this->gameManipulator->getCurrentPlayerHits($game);

        $ships = $this
            ->getDoctrine()
            ->getRepository(Ship::class)
            ->getCurrentPlayerShips($game);

        $shoots = $this
            ->getDoctrine()
            ->getRepository(Shoot::class)
            ->getCurrentPlayerShoots($game);
            
        $opponentShips = $this
            ->getDoctrine()
            ->getRepository(Ship::class)
            ->getPlayerShips($game, $this->gameManipulator->getOpponentPlayer($game));

        return $this->render('game.html.twig', [
            'game' => $game,
            'ships' => $ships,
            'opponent_ships' => $opponentShips,
            'opponent_ships_sunk' => $this->gameManipulator->getOpponentShipsSunk($game),
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
     * Shoot at opponent fleet and set correct message fro the player
     * 
     * @param array $data
     * @param Game $game
     * 
     * @return null
     */
    private function manageShoot(array $data, Game $game): void
    {
        $coordinates = explode(',', $data['coordinates']);

        $shoot = $this->gameManipulator->shoot($coordinates, $game);

        $shipHit = $this->gameManipulator->didShootHitOpponentShip($shoot, $game);
        if ($shipHit) {
            if ($this->gameManipulator->isShipSunk($shipHit, $game)) {
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
