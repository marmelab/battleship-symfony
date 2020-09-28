<?php

namespace App\Repository;

use App\Entity\Shoot;
use App\Entity\Game;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Shoot|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shoot|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shoot[]    findAll()
 * @method Shoot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShootRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Shoot::class);
    }

    /**
     * Returns the shoots of the current player
     * 
     * @param Game $game
     * 
     * @return array
     */
    public function getCurrentPlayerShoots(Game $game): array
    {
        return $this->findBy(['game' => $game, 'player' => $game->getCurrentPlayer()]);    
    }

    /**
     * Returns the shoots of a player
     * 
     * @param Game $game
     * @param Player $player
     * 
     * @return array
     */
    public function getPlayerShoots(Game $game, Player $player): array
    {
        return $this->findBy(['game' => $game, 'player' => $player]);    
    }
}
