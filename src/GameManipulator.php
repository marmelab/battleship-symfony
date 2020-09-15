<?php

namespace App;

use App\Entity\Game;
use App\Entity\Ship;
use App\Constants\GameConstants;

class GameManipulator
{
    /**
     * Create a new game
     * 
     * @return Game
     */
    public function createGame(): Game
    {
        $game = new Game();

        $config = [
            'ships' => [5,4,3,3,2],
            'grid' => 10
        ];

        $orientations = [GameConstants::VERTICAL, GameConstants::HORIZONTAL];

        foreach ($config['ships'] as $shipLength)
        {
            $ship = $this->getRandShip($shipLength, $orientations[array_rand($orientations)], $game);
            $game->addShip($ship);
        }

        return $game;
    }

    /**
     * Get a ship with random coordinates
     * 
     * @param int $length
     * @param int $orientation
     * @param int $game
     * 
     * @return Ship
     */
    public function getRandShip(int $length, string $orientation, Game $game): Ship
    {
        $positionOk = false;

        if ($orientation === GameConstants::HORIZONTAL)
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