<?php

namespace App\Entity;

use App\Repository\ShipRepository;
use App\Twig\AppExtension;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

/**
 * @ORM\Entity(repositoryClass=ShipRepository::class)
 */
class Ship implements JsonSerializable
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

    /**
     * Get css grid attributes for positioning ships
     * 
     * @param Ship $ship
     * 
     * @return string
     */
    public function getGridAttributes(): string
    {
        $gridRowStart = $this->getGridRowStart();
        $gridRowEnd = $this->getGridRowEnd();
        $gridColumnStart = $this->getGridColumnStart();
        $gridColumnEnd = $this->getGridColumnEnd();

        return "grid-row-start: ${gridRowStart}; grid-row-end: ${gridRowEnd}; grid-column-start: ${gridColumnStart}; grid-column-end: ${gridColumnEnd}";
    }

    /**
     * Get grid-row-start attribute for a ship
     * 
     * @param Ship $ship
     * 
     * @return string
     */
    private function getGridRowStart(): string {
        $coordinates = $this->getCoordinates();
        $first = $coordinates[0];

        if ($this->isHorizontal($this)) {
            return $first[0] + 1;
        }

        return $first[0] + 1;
    }

    /**
     * Get grid-row-end attribute for a ship
     * 
     * @param 
     * 
     * @return string
     */
    private function getGridRowEnd(): string {
        $coordinates = $this->getCoordinates();
        $first = $coordinates[0];

        if ($this->isHorizontal($this)) {
            return $first[0] + 1;
        }

        return $first[0] + 1 + $this->length($this);
    }

    /**
     * Get grid-column-start attribute for a ship
     * 
     * @param 
     * 
     * @return string
     */
    private function getGridColumnStart(): string {
        $coordinates = $this->getCoordinates();
        $first = $coordinates[0];

        return $first[1] + 1;
    }

    /**
     * Get grid-column-end attribute for a ship
     * 
     * @param 
     * 
     * @return string
     */
    private function getGridColumnEnd(): string {
        $coordinates = $this->getCoordinates();
        $first = $coordinates[0];

        if ($this->isHorizontal($this)) {
            return $first[1] + 1 + $this->length($this);
        }

        return $first[1] + 1; 
    }

    /**
     * Get the length of the boat
     * 
     * @param Ship $ship
     * 
     * @return bool
     */
    public function length(): int
    {
        return count($this->getCoordinates());
    }

    /**
     * Returns true if the ship is horizontally placed on the board
     * 
     * @param Ship $ship
     * 
     * @return bool
     */
    public function isHorizontal(): bool
    {
        return $this->getCoordinates()[0][0] == $this->getCoordinates()[1][0];
    }

    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "coordinates" => $this->getCoordinates(),
            "grid_attributes" => $this->getGridAttributes($this)
        ];
    }
}
