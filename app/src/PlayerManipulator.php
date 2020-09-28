<?php

namespace App;

use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use Utils;

class PlayerManipulator
{
    /**
     * Used to persist entities
     * 
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * Inject dependencies
     * 
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Update player hash
     * 
     * @param Player $player
     * 
     * @return bool
     */
    public function generatePlayerHash(Player $player): Player
    {
        $player->setHash(Utils::generateRandomGameHash(50));
        $this->entityManager->persist($player);
        $this->entityManager->flush();

        return $player;
    }

    
}