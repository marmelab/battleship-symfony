<?php

namespace App;

use App\Entity\Ship;

class ShipManipulator
{
    /**
     * Get the length of the boat
     * 
     * @param Ship $ship
     * 
     * @return bool
     */
    public function length(Ship $ship): int
    {
        return count($ship->getCoordinates());
    }

    /**
     * Returns true if the ship is horizontally placed on the board
     * 
     * @param Ship $ship
     * 
     * @return bool
     */
    public function isHorizontal(Ship $ship): bool
    {
        return $ship->getCoordinates()[0][0] == $ship->getCoordinates()[1][0];
    }
}