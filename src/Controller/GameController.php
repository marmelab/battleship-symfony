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
    public function random(Request $request, Game $game): Response
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

            $this->gameManipulator->shoot($coordinates, $game);
            
            $this->gameManipulator->switchCurrentPlayer($game);

            return $this->redirectToRoute('random_game', ['hash' => $game->getHash()]);
        }

        $triggerViews = [];
        foreach ($triggers as $trigger) {
            $triggerViews[] = $trigger->createView();
        }

        // $this->gameManipulator->getCurrentPlayerHits($game);

        $ships = $this
            ->getDoctrine()
            ->getRepository(Ship::class)
            ->getCurrentPlayerShips($game);

        $shoots = $this
            ->getDoctrine()
            ->getRepository(Shoot::class)
            ->getCurrentPlayerShoots($game);
            

        return $this->render('game.html.twig', [
            'game' => $game,
            'ships' => $ships,
            // 'hits' => $hits,
            'shoots' => $shoots,
            'triggers' => $triggerViews
        ]);
    }
}
