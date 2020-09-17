<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Entity\Player;
use App\Entity\Ship;
use App\Entity\Shoot;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $game = new Game();
        $game->setHash(('test'));
        $game->setStatus(('RUNNING'));
        
        $player1 = new Player();
        $player1->setName('Bob');

        $player2 = new Player();
        $player2->setName('John');

        $game->setPlayer1($player1);
        $game->setPlayer2($player2);

        $game->setCurrentPlayer($player1);

        $shoot = new Shoot;
        $shoot->setGame($game);
        $shoot->setPlayer($player1);
        $shoot->setCoordinates([0,0]);

        $ship = new Ship();
        $ship->setPlayer($player2);
        $ship->setCoordinates([
            [0,0], [0,1]
        ]);

        $game->addShip($ship);

        $shoot = new Shoot();
        $shoot->setGame($game);
        $shoot->setPlayer($player1);
        $shoot->setCoordinates([0,0]);
        $manager->persist($shoot);

        $shoot = new Shoot();
        $shoot->setGame($game);
        $shoot->setPlayer($player1);
        $shoot->setCoordinates([0,1]);
        $manager->persist($shoot);

        $manager->persist($game);

        $manager->flush();
    }
}
