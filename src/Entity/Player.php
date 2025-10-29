<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $health = null;

    #[ORM\Column]
    private ?int $strength = null;

    /**
     * @var Collection<int, GameState>
     */
    #[ORM\OneToMany(targetEntity: GameState::class, mappedBy: 'player')]
    private Collection $games;

    public function __construct()
    {
        $this->games = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

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

    /**
     * @return Collection<int, GameState>
     */
    public function getGames(): Collection
    {
        return $this->games;
    }

    public function addGame(GameState $game): static
    {
        if (!$this->games->contains($game)) {
            $this->games->add($game);
            $game->setPlayer($this);
        }

        return $this;
    }

    public function removeGame(GameState $game): static
    {
        if ($this->games->removeElement($game)) {
            if ($game->getPlayer() === $this) {
                $game->setPlayer(null);
            }
        }

        return $this;
    }
}
