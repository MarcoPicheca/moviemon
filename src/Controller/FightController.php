<?php

namespace App\Controller;

use App\Entity\GameState;
use App\Entity\Moviemon;
use App\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

final class FightController extends AbstractController
{
	#[Route('/fight/{id}/{moviemon_id}', name: 'app_fight')]
	public function index(GameState $game, Moviemon $moviemon, EntityManagerInterface $em): Response
	{
		$player = $game->getPlayer();
		if ($player->getHealth() <= 0)
		{
			$movies = $game->getCaptured();
			$map = $game->getMap();
			foreach ($movies as $movie)
			{
				$movie->setCaptured(false);
				$y = $movie->getPosY();
				$x = $movie->getPosX();
				$map[$y][$x] = $movie;
			}
			$game->setMap($map);
			$em->flush();

			return $this->render('world_map/endGame.html.twig', [
				'win' => 0,
				'id' => $game->getId()
			]);
		}
		return $this->render('fight/index.html.twig', [
			'fight_title' => 'FightController',
			'player' => $player,
			'game' => $game->getId(),
			'moviemon' => $moviemon
		]);
	}

	#[Route(path:'/fight/attack/{id}/{moviemon_id}', name:'app_fight_attack')]
	public function attack(GameState $game, int $moviemon_id, EntityManagerInterface $em): Response
	{
		$player_attack = $game->getPlayer()->getStrength();
		$player = $game->getPlayer();
		$moviemon_try = $em->getRepository(Moviemon::class)->find($moviemon_id);
		$moviemon = $game->getMoviemonByTitle($moviemon_try->getTitle());

		$movie_health = $moviemon->getHealth() - $player_attack;
		if ($moviemon != null && $movie_health <= 0)
		{
            // Cattura il moviemon
            $moviemon->setCaptured(true)
                ->setHealth(0)
                ->setStrength(0)
                ->setPosX(-1)
                ->setPosY(-1);
            
            // Potenzia il player
            $player->setStrength($player->getStrength() + 3);
            $player->setHealth(min(100, $player->getHealth() + 3)); // Non superare 100
            
            // Aggiorna la mappa
            $map = $game->getMap();
            $map[$moviemon->getPosY()][$moviemon->getPosX()] = $player->getId();
            $game->setMap($map);
            // DEBUG: Verifica prima del flush
            $em->flush();
			// dump($moviemon->getId());
			// dump($moviemon->getTitle());
			// dump($game->getId());
			// dump($game->getRemaining());
			// exit();
			$captured = $game->getCaptured()->toArray();
			$remainings = $game->getRemaining()->toArray();
			return $this->render('world_map/index.html.twig', [
				'controller_name' => 'World Map',
				'game' => $game,
				'captured' => $captured,
				'remaining' => $remainings,
			]);
		}
		$moviemon->setHealth($movie_health);
		$em->flush();
		return $this->redirectToRoute('app_fight', [
			'id' => $game->getId(),
			'moviemon_id' => $moviemon->getId()]
		);
	}

	#[Route(path:'/fight/escape/{id}/{moviemon_id}', name:'app_fight_escape')]
	public function escape(GameState $game, int $moviemon_id, EntityManagerInterface $em): Response
	{		
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
		$moviemon = $em->getRepository(Moviemon::class)->find($moviemon_id);
		$moviemon->setPosY($free_cells[1]['y']);
		$moviemon->setPosX($free_cells[1]['x']);
		$em->flush();
		return $this->redirectToRoute('app_world_map', [
			'id' => $game->getId()
		]);
	}
}
