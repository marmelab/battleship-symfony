<?php

namespace App\Entity;

use App\Repository\ShipRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShipRepository::class)
 */
class Ship
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="array")
     */
    private $coordinates = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game", inversedBy="ships")
     */
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="ships")
     */
    private $player;

     /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="products")
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }

    public function setCoordinates(array $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }

    public function addCoordinate(array $coordinate): self
    {
        $this->coordinates[] = $coordinate;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(Game $game): self
    {
        $this->game = $game;

        return $this;
    }
    
    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }
}
