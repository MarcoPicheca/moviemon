<?php

namespace App\Entity;

use App\Repository\MoviemonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoviemonRepository::class)]
class Moviemon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?int $year = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poster = null;

    #[ORM\Column]
    private ?int $health = null;

    #[ORM\Column]
    private ?int $strength = null;

    #[ORM\Column]
    private bool $captured = false;

    /**
     * @var Collection<int, GameState>
     */
    #[ORM\ManyToMany(targetEntity: GameState::class, mappedBy: 'moviemons')]
    private Collection $gameStates;

    #[ORM\Column]
    private ?int $posX = null;

    #[ORM\Column]
    private ?int $posY = null;

    public function __construct()
    {
        $this->gameStates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;
        return $this;
    }

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(?string $poster): static
    {
        $this->poster = $poster;
        return $this;
    }

    public function getHealth(): ?int
    {
        return $this->health;
    }

    public function setHealth(int $health): static
    {
        $this->health = $health;
        return $this;
    }

    public function getStrength(): ?int
    {
        return $this->strength;
    }

    public function setStrength(int $strength): static
    {
        $this->strength = $strength;
        return $this;
    }

    public function isCaptured(): bool
    {
        return $this->captured;
    }

    public function setCaptured(bool $captured): static
    {
        $this->captured = $captured;
        return $this;
    }

    /**
     * @return Collection<int, GameState>
     */
    public function getGameStates(): Collection
    {
        return $this->gameStates;
    }

    public function addGameState(GameState $gameState): static
    {
        if (!$this->gameStates->contains($gameState)) {
            $this->gameStates->add($gameState);
            $gameState->addMoviemon($this);
        }
        return $this;
    }

    public function removeGameState(GameState $gameState): static
    {
        if ($this->gameStates->removeElement($gameState)) {
            $gameState->removeMoviemon($this);
        }
        return $this;
    }

    public function getPosX(): ?int
    {
        return $this->posX;
    }

    public function setPosX(int $posX): static
    {
        $this->posX = $posX;

        return $this;
    }

    public function getPosY(): ?int
    {
        return $this->posY;
    }

    public function setPosY(int $posY): static
    {
        $this->posY = $posY;

        return $this;
    }
}