<?php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\Shoot;
use App\Constants\GameConstants;
use App\GameManipulator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Form\Type\ShootShipType;
use App\Form\Type\AbandonGameType;
use Symfony\Component\HttpFoundation\Request;

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

        $triggers = $this->createTriggersForms($game);

        $trigger = $this->createForm(ShootShipType::class)->handleRequest($request);

        if ($trigger->isSubmitted() && $trigger->isValid()) {
            $data = $trigger->getData();

            $game = $this->getDoctrine()
                ->getRepository(Game::class)
                ->findOneByHash($data['game_hash']);

            if (!$game) {
                throw $this->createNotFoundException(
                    'No game found for hash '.$data['game_hash']
                );
            }
            
            $coordinates = explode(',', $data['coordinates']);

            $shoot = $this->gameManipulator->shoot($coordinates, $game);

            $shipHit = $this->gameManipulator->didShootHitOpponentShip($shoot, $game);
            if ($shipHit) {
                if ($this->gameManipulator->isShipSunk($shipHit, $game)) {
                    $this->addFlash('success', 'COULEEEEEEEE!');
                } else {
                    $this->addFlash('success', 'TOUCHE!');
                }
            } else {
                $this->gameManipulator->switchCurrentPlayer($game);
                $this->addFlash('success', 'LOUPE!');
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
            'opponentShips' => $opponentShips,
            'hits' => $hits,
            'shoots' => $shoots,
            'triggers' => $triggerViews,
            'abandon_form' => $abandonForm->createView()
        ]);
    }

    private function createTriggersForms($game)
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
