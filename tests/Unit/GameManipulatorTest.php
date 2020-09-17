<?php

namespace App\Tests\Util;

use App\Entity\Game;
use App\Entity\Ship;
use App\Entity\Shoot;
use App\GameManipulator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameManipulatorTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /** @test */
    public function a_ship_has_sunk()
    {
        $gameRepository = $this->entityManager->getRepository((Game::class));

        $shipRepository = $this->entityManager
            ->getRepository(Ship::class);

        $shootRepository = $this->entityManager
            ->getRepository(Shoot::class);

        $game = $gameRepository->findOneBy([
            'hash' => 'test',
        ]);

        $ship = $shipRepository->findAll()[0];

        $gameManipulator = new GameManipulator($this->entityManager, $shipRepository, $shootRepository);

        $this->assertTrue($gameManipulator->isShipSunk($ship, $game));
    }
}