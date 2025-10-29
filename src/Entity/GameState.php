<?php

namespace App\Entity;

use App\Repository\GameStateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameStateRepository::class)]
class GameState
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	private ?int $id = null;

	#[ORM\Column]
	private ?int $posX = null;

	#[ORM\Column]
	private ?int $posY = null;

	#[ORM\Column]
	private ?int $playerHealth = null;

	/**
	 * @var Collection<int, Moviemon>
	 */
	#[ORM\ManyToMany(targetEntity: Moviemon::class, inversedBy: 'gameStates')]
	private Collection $moviemons;

	#[ORM\ManyToOne(inversedBy: 'games')]
	#[ORM\JoinColumn(nullable: false)]
	private ?Player $player = null;

	#[ORM\Column]
	private ?\DateTimeImmutable $time = null;

    #[ORM\Column]
    private array $map = [];

	public function __construct()
	{
		$this->moviemons = new ArrayCollection();
		$this->map = [
				[0, 0, 0, 0, 0, 0],
				[0, 0, 0, 0, 0, 0],
				[0, 0, 0, 0, 0, 0],
				[0, 0, 0, 0, 0, 0],
				[0, 0, 0, 0, 0, 0],
				[0, 0, 0, 0, 0, 0],
		];
	}

	public function getId(): ?int
	{
		return $this->id;
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

	public function getPlayerHealth(): ?int
	{
		return $this->playerHealth;
	}

	public function setPlayerHealth(int $playerHealth): static
	{
		$this->playerHealth = $playerHealth;
		return $this;
	}

	/**
	 * @return Collection<int, Moviemon>
	 */
	public function getMoviemons(): Collection
	{
		return $this->moviemons;
	}

	public function getMoviemonByTitle(string $title): Moviemon
	{
		$movies = $this->getRemaining();
		foreach($movies as $movie)
		{
			if ($movie->getTitle() === $title)
				return $movie;
		}
		return new Moviemon();
	}

	public function addMoviemon(Moviemon $moviemon): static
	{
		if (!$this->moviemons->contains($moviemon)) {
			$this->moviemons->add($moviemon);
		}
		return $this;
	}

	public function removeMoviemon(Moviemon $moviemon): static
	{
		$this->moviemons->removeElement($moviemon);
		return $this;
	}

	/**
	 * @return Collection<int, Moviemon>
	 */
	public function getCaptured(): Collection
	{
		return $this->moviemons->filter(function(Moviemon $moviemon) {
			return $moviemon->isCaptured();
		});
	}

	/**
	 * @return Collection<int, Moviemon>
	 */
	public function getRemaining(): Collection
	{
		return $this->moviemons->filter
		(
			function(Moviemon $moviemon) 
			{
				return !$moviemon->isCaptured();
			}
		);
	}

	public function getPlayer(): ?Player
	{
		return $this->player;
	}

	public function setPlayer(?Player $player): static
	{
		$this->player = $player;
		return $this;
	}

	public function getTime(): ?\DateTimeImmutable
	{
		return $this->time;
	}

	public function setTime(\DateTimeImmutable $time): static
	{
		$this->time = $time;
		return $this;
	}

    public function getMap(): array
    {
        return $this->map;
    }

    public function setMap(array $map): static
    {
        $this->map = $map;

        return $this;
    }
}