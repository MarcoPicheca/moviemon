<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Player;
use App\Entity\GameState;
use App\Entity\Moviemon;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\HttpClient\HttpClient;

final class WorldMapController extends AbstractController
{
	#[Route('/world/map/{id}', name: 'app_world_map')]
	public function index(int $id, EntityManagerInterface $em): Response
	{
		$game = $em->getRepository(GameState::class)->find($id);

		if ($game->getCaptured() != null && $game->getCaptured()->count() == 11)
			return $this->render('world_map/endGame.html.twig', [
					'win' => 1,
					'id' => $game->getId()
				]);

		if ($game->getRemaining()->isEmpty())
		{
			// populating the moviemon collection
			$titles = [
				"The Shawshank Redemption",
				"The Godfather",
				"The Dark Knight",
				"Pulp Fiction",
				"Forrest Gump",
				"Inception",
				"Fight Club",
				"The Lord of the Rings: The Fellowship of the Ring",
				"The Matrix",
				"Gladiator",
				"Titanic",
			];

			// client request to get the json from OMDb API
			$client = HttpClient::create();
			foreach ($titles as $title)
			{
				$url = 'https://www.omdbapi.com/?apikey=' . $_ENV['OMBD_API_KEY'] . '&t=' . urlencode($title);
			    $response = $client->request('GET', $url);
				$response->getStatusCode() == 200 ? $movie = $response->toArray() : $movie = null;
				if($movie != null && $movie['Response'] == 'True')
				{
					$moviemon = new Moviemon();
					$moviemon->setTitle($movie['Title'])
						->setYear($movie['Year'])
						->setHealth(rand(20, 100))
						->setStrength(rand(2, 30))
						->setPoster($movie['Poster'])
						->setCaptured(false)
						->setPosX(3)
						->setPosY(3);
					$game->addMoviemon($moviemon);
					$em->persist($moviemon);
				}
			}

			// populating the map in GameState with moviemons
			$moviemons = $game->getMoviemons();
			$map = $game->getMap();		
			$player_X = $game->getPosX();
			$player_Y = $game->getPosY();
			$map[$player_Y][$player_X] = $game->getPlayer()->getId();
			$y = 0;
			$free_cells = [];
			while ($y < 6)
			{
				$x = 0;
				while ($x < 6)
				{
					if ($map[$y][$x] === 0)
					{
						$free_cells []= ['y' => $y, 'x' => $x];
					}
					$x++;
				}
				$y++;
			}
			shuffle($free_cells);
			for ($i = 0; $i < count($moviemons) && $i < count($free_cells); $i++)
			{
				$y = $free_cells[$i]['y'];
				$x = $free_cells[$i]['x'];
				$moviemons[$i]->setPosX($x);
				$moviemons[$i]->setPosY($y);
				$map[$y][$x] = $moviemons[$i]->getId();
			}
			$game->setMap($map);
			$em->flush();
		}

		// check if the player is engaging a fight
		$game->getPlayer()->setHealth(100);
		$em->flush();
		$player_x = $game->getPosX();
		$player_y = $game->getPosY();
		foreach ($game->getRemaining() as $movie)
		{
			if ($movie->getPosX() == $player_x && $movie->getPosY() == $player_y)
			{
				$game->getPlayer()
					->setHealth(100)
					->setStrength(50);

				return $this->redirectToRoute('app_fight', [
					'game_id' => $game->getId(),
					'moviemon_id' => $movie->getId()
				]);
			}
		}

		$captured = $game->getCaptured()->toArray();
		$remainings = $game->getRemaining()->toArray();
		$em->flush();

		return $this->render('world_map/index.html.twig', [
			'controller_name' => 'World Map',
			'game' => $game,
			'captured' => $captured,
			'remaining' => $remainings,
		]);
	}

	#[Route('/world/map/move/{id}/{direction}', name: 'app_worldmap_move')]
	public function move(int $id, string $direction, EntityManagerInterface $em): Response
	{
		$game = $em->getRepository(GameState::class)->find($id);
		$x = $game->getPosX();
		$y = $game->getPosY();

		switch ($direction) {
			case 'up':
				$y = max(0, $y - 1);
				break;
			case 'down':
				$y = min(5, $y + 1);
				break;
			case 'left':
				$x = max(0, $x - 1);
				break;
			case 'right':
				$x = min(5, $x + 1);
				break;
		}

		$game->setPosX($x);
		$game->setPosY($y);
		$game->setTime(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Rome')));

		$em->flush();

		return $this->redirectToRoute('app_world_map', [
			'id' => $game->getId()
		]);
	}
}
