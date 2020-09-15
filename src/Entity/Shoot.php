<?php

namespace App\Entity;

use App\Repository\ShootRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShootRepository::class)
 */
class Shoot
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Game", inversedBy="shoots", cascade={"persist"})
     */
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Player", inversedBy="shoots", cascade={"persist"})
     */
    private $player;

    /**
     * @ORM\Column(type="array")
     */
    private $coordinates = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): Game
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

    public function getCoordinates(): ?array
    {
        return $this->coordinates;
    }

    public function setCoordinates(array $coordinates): self
    {
        $this->coordinates = $coordinates;

        return $this;
    }
}
