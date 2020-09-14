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
            'ships' => [5,2],
            'grid' => 10
        ];

        $orientations = [self::VERTICAL, self::HORIZONTAL];

        for ($i=0; $i<2; $i++)
        {
            $ship = $this->getRandShip(3, $orientations[array_rand($orientations)], $game->getShips());
            $game->addShip($ship);
        }

        return $this->render('game.html.twig', [
            // this array defines the variables passed to the template,
            // where the key is the variable name and the value is the variable value
            // (Twig recommends using snake_case variable names: 'foo_bar' instead of 'fooBar')
            'game' => $game,
        ]);

        // return new Response(
        //     '<html><body>Lucky number: '.$randCoord.'</body></html>'
        // );
    }

    private function getRandShip($length, $orientation, $ships)
    {
        $coordinates = [];
    
        if ($orientation === self::HORIZONTAL)
        {
            // horizontal ship
            $line = rand(0, 9);
            $column = rand(0, 10 - $length);
            
            for ($i=$column; $i < $column + $length; $i++)
            {
                $coordinates[] = [$line, $i];
            }
        } 
        else 
        {
            // vertical ship
            $line = rand(0, 10 - $length);
            $column = rand(0, 9);

            for ($i=$line; $i < $line + $length; $i++)
            {
                $coordinates[] = [$i, $column];
            }
        }

        $ship = new Ship();
        $ship->setCoordinates($coordinates);

        return $ship;
    }
}
