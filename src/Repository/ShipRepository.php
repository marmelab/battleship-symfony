<?php

namespace App\Repository;

use App\Entity\Ship;
use App\Entity\Game;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @method Ship|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ship|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ship[]    findAll()
 * @method Ship[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ship::class);
    }

    /**
     * Get current player fleet
     * 
     * @param Game $game
     * 
     * @return array
     */
    public function getCurrentPlayerShips(Game $game): array
    {
        return $this->getPlayerShips($game, $game->getCurrentPlayer());
    }

    /**
     * Get one player fleet
     * 
     * @param Game $game
     * 
     * @return array
     */
    public function getPlayerShips(Game $game, Player $player): array
    {
        $ships = $this->findBy(['game' => $game, 'player' => $player]);    
        return $ships;
    }
}
