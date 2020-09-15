<?php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\Ship;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GameController extends AbstractController
{
    const VERTICAL = 'VERTICAL';
    const HORIZONTAL = 'HORIZONTAL';

    public function random(): Response
    {        
        $game = new Game();

        $config = [
            'ships' => [5,4,3,3,2],
            'grid' => 10
        ];

        $orientations = [self::VERTICAL, self::HORIZONTAL];

        foreach ($config['ships'] as $shipLength)
        {
            $ship = $this->getRandShip($shipLength, $orientations[array_rand($orientations)], $game);
            $game->addShip($ship);
        }

        return $this->render('game.html.twig', [
            'game' => $game,
        ]);
    }

    private function getRandShip($length, $orientation, $game)
    {
        $positionOk = false;

        if ($orientation === self::HORIZONTAL)
        {

            while (!$positionOk)
            {
                $coordinates = [];
                $allCoordsChecked = true;

                // horizontal ship
                $line = rand(0, 9);
                $column = rand(0, 10 - $length);
                
                for ($i=$column; $i < $column + $length; $i++)
                {
                    if ($game->getShipAt($line, $i))
                    {
                        $allCoordsChecked = false;
                        break;
                    }                

                    $coordinates[] = [$line, $i];
                }

                if ($allCoordsChecked)
                {
                    $positionOk = true;
                }
            }
        } 
        else 
        {
            while (!$positionOk)
            {
                $coordinates = [];
                $allCoordsChecked = true;

                // vertical ship
                $line = rand(0, 10 - $length);
                $column = rand(0, 9);

                for ($i=$line; $i < $line + $length; $i++)
                {
                    if ($game->getShipAt($i, $column))
                    {
                        $allCoordsChecked = false;
                        break;
                    }                

                    $coordinates[] = [$i, $column];
                }

                if ($allCoordsChecked)
                {
                    $positionOk = true;
                }
            }
        }

        $ship = new Ship();
        $ship->setCoordinates($coordinates);

        return $ship;
    }

}
