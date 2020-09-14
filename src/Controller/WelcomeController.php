<?php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\Ship;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WelcomeController extends AbstractController
{
    public function index(): Response
    {        
        return $this->render('welcome.html.twig');
    }
}
