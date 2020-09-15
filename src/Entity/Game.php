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
     * @ORM\Column(type="string", length=255)
     */
    private $hash;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Player", cascade={"persist"})
     */
    private $player1;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Player", cascade={"persist"})
     */
    private $player2;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Player", cascade={"persist"})
     */
    private $currentPlayer;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ship", mappedBy="game", cascade={"persist"})
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

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getPlayer1(): Player
    {
       return $this->player1;
    }

    public function setPlayer1(Player $player): self
    {
        $this->player1 = $player;

        return $this;
    }

    public function getPlayer2(): Player
    {
        return $this->player2;
    }

    public function setPlayer2(Player $player): self
    {
        $this->player2 = $player;

        return $this;
    }

    public function getCurrentPlayer(): Player
    {
        return $this->currentPlayer;
    }

    public function setCurrentPlayer(Player $player): self
    {
        $this->currentPlayer = $player;

        return $this;
    }

    public function getShips(): Collection
    {
        return $this->ships;
    }

    public function addShip(Ship $ship): self
    {
        $this->ships[] = $ship;
        $ship->setGame($this);

        return $this;
    }

    /**
     * Returns the ship at a position or null 
     * 
     * @param int $x
     * @param int $y
     * 
     * @return Ship|null
     */
    public function getShipAt(int $x, int $y): ?Ship
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

        return null;
    }
}
