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
        ];
    }

    public function toAlpha(int $num)
    {
        return chr(65 + $num);
    }
}