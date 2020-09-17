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

    public function __construct(GameManipulator $gameManipulator)
    {
        $this->gameManipulator = $gameManipulator;
    }

    /**
     * @ParamConverter("game", options={"mapping": {"hash": "hash"}})
     */
    public function index(Request $request, Game $game, TranslatorInterface $translator): Response
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

        $triggers = $this->createTriggersForms($game);

        $trigger = $this->createForm(ShootShipType::class)->handleRequest($request);

        if ($trigger->isSubmitted() && $trigger->isValid()) {
            $data = $trigger->getData();

            $coordinates = explode(',', $data['coordinates']);

            $shoot = $this->gameManipulator->shoot($coordinates, $game);

            $shipHit = $this->gameManipulator->didShootHitOpponentShip($shoot, $game);
            if ($shipHit) {
                if ($this->gameManipulator->isShipSunk($shipHit, $game)) {
                    $this->addFlash('shoot_result', $translator->trans('You have SINK a ship! Well done!'));
                } else {
                    $this->addFlash('shoot_result', $translator->trans('You hit a ship!'));
                }
            } else {
                $this->gameManipulator->switchCurrentPlayer($game);
                $this->addFlash('shoot_result', $translator->trans('You hit the water...'));
            }

            return $this->redirectToRoute('game_index', ['hash' => $game->getHash()]);
        }

        $triggerViews = [];
        foreach ($triggers as $trigger) {
            $triggerViews[] = $trigger->createView();
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
     * Creates all the forms for shooting the opponent
     * 
     * @param Game $game
     * 
     * @return array
     */
    private function createTriggersForms(Game $game)
    {
        $triggers = [];

        foreach (range(0, 9) as $x) {
            foreach (range(0, 9) as $y) {
                $triggers[] = $this->createForm(ShootShipType::class, [
                    'game_hash' => $game->getHash(),
                    'coordinates' => join(",", [$x, $y])
                ]);
            }
        }

        return $triggers;
    }
}
