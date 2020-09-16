<?php 

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('alpha', [$this, 'toAlpha']),
            new TwigFunction('match_coordinate', [$this, 'matchCoordinate']),
            new TwigFunction('grid_row_start', [$this, 'getGridRowStart']),
            new TwigFunction('grid_row_end', [$this, 'getGridRowEnd']),
            new TwigFunction('grid_col_start', [$this, 'getGridColumnStart']),
            new TwigFunction('grid_col_end', [$this, 'getGridColumnEnd']),
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

    public function getGridRowStart($ship) {
        $coordinates = $ship->getCoordinates();
        $first = $coordinates[0];
        $last = $coordinates[1];

        if ($ship->isHorizontal()) {
            return $first[0] + 1;
        }

        return $first[0] + 1;
    }

    public function getGridRowEnd($ship) {
        $coordinates = $ship->getCoordinates();
        $first = $coordinates[0];
        $last = $coordinates[1];

        if ($ship->isHorizontal()) {
            return $first[0] + 1;
        }

        return $first[0] + 1 + $ship->length();
    }

    public function getGridColumnStart($ship) {
        $coordinates = $ship->getCoordinates();
        $first = $coordinates[0];
        $last = $coordinates[1];

        return $first[1] + 1;
    }

    public function getGridColumnEnd($ship) {
        $coordinates = $ship->getCoordinates();
        $first = $coordinates[0];
        $last = $coordinates[1];

        if ($ship->isHorizontal()) {
            return $first[1] + 1 + $ship->length();
        }

        return $first[1] + 1; 
    }
}