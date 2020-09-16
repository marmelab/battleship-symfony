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
            new TwigFunction('to_array', [$this, 'toArray']),
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
     * Transforms string coordinates to an array
     * 
     * @param string $coordinates
     * 
     * @return array
     */
    public function toArray(string $coordinates)
    {
        return explode(',', $coordinates);
    }
}