<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=GameRepository::class)
 */
class Game
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ship", mappedBy="game")
     */
    private $ships;

    public function __construct()
    {
        $this->ships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShips(): Collection
    {
        return $this->ships;
    }

    public function addShip(Ship $ship): self
    {
        $this->ships[] = $ship;

        return $this;
    }

    public function getShipAt(int $x, int $y)
    {
        foreach ($this->ships as $ship)
        {
            foreach ($ship->getCoordinates() as $coord)
            {
                if ($coord[0] === $x && $coord[1] === $y)
                {
                    return $ship;
                }
            }
        }

        return false;
    }
}
