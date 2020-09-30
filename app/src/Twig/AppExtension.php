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
        return $ship->getGridAttributes();
    }
}