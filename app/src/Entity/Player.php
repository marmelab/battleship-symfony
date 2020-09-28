<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=PlayerRepository::class)
 */
class Player
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
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ship", mappedBy="player")
     */
    private $ships;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Shoot", mappedBy="player")
     */
    private $shoots;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hash;

    public function __construct()
    {
        $this->shoots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getShoots(): Collection
    {
        return $this->shoots;
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
}
