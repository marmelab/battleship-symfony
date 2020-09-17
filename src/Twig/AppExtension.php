<?php 

namespace App\Twig;

use App\Entity\Ship;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\ShipManipulator;

class AppExtension extends AbstractExtension
{
    private $shipManipulator;

    public function __construct(ShipManipulator $shipManipulator)
    {
        $this->shipManipulator = $shipManipulator;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('alpha', [$this, 'toAlpha']),
            new TwigFunction('match_coordinate', [$this, 'matchCoordinate']),
            new TwigFunction('grid_attributes', [$this, 'getGridAttributes']),
        ];
    }

    /**
     * Get the letter corresponding to a column
     * 
     * @param int $num
     * 
     * @return string
     */
    public function toAlpha(int $num): string
    {
        return chr(65 + $num);
    }

    /**
     * Check if a coordinate is a hit
     * 
     * @param array $coordinate
     * @param bool $shoots
     * 
     * @return boolean
     */
    public function matchCoordinate(array $coordinates, array $shoots): bool
    {
        foreach ($shoots as $shoot) {
            if ($shoot->getCoordinates() == $coordinates) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get css grid attributes for positioning ships
     * 
     * @param Ship $ship
     * 
     * @return string
     */
    public function getGridAttributes(Ship $ship): string
    {
        $gridRowStart = $this->getGridRowStart($ship);
        $gridRowEnd = $this->getGridRowEnd($ship);
        $gridColumnStart = $this->getGridColumnStart($ship);
        $gridColumnEnd = $this->getGridColumnEnd($ship);

        return "grid-row-start: ${gridRowStart}; grid-row-end: ${gridRowEnd}; grid-column-start: ${gridColumnStart}; grid-column-end: ${gridColumnEnd}";
    }

    /**
     * Get grid-row-start attribute for a ship
     * 
     * @param Ship $ship
     * 
     * @return string
     */
    private function getGridRowStart(Ship $ship): string {
        $coordinates = $ship->getCoordinates();
        $first = $coordinates[0];

        if ($this->shipManipulator->isHorizontal($ship)) {
            return $first[0] + 1;
        }

        return $first[0] + 1;
    }

    /**
     * Get grid-row-end attribute for a ship
     * 
     * @param Ship $ship
     * 
     * @return string
     */
    private function getGridRowEnd(Ship $ship): string {
        $coordinates = $ship->getCoordinates();
        $first = $coordinates[0];

        if ($this->shipManipulator->isHorizontal($ship)) {
            return $first[0] + 1;
        }

        return $first[0] + 1 + $this->shipManipulator->length($ship);
    }

    /**
     * Get grid-column-start attribute for a ship
     * 
     * @param Ship $ship
     * 
     * @return string
     */
    private function getGridColumnStart(Ship $ship): string {
        $coordinates = $ship->getCoordinates();
        $first = $coordinates[0];

        return $first[1] + 1;
    }

    /**
     * Get grid-column-end attribute for a ship
     * 
     * @param Ship $ship
     * 
     * @return string
     */
    private function getGridColumnEnd(Ship $ship): string {
        $coordinates = $ship->getCoordinates();
        $first = $coordinates[0];

        if ($this->shipManipulator->isHorizontal($ship)) {
            return $first[1] + 1 + $this->shipManipulator->length($ship);
        }

        return $first[1] + 1; 
    }
}