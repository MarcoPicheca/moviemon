<?php

namespace App\Controller;

use App\Entity\GameState;
use App\Entity\Moviemon;
use App\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class FightController extends AbstractController
{
	#[Route('/fight/{game_id}/{moviemon_id}', name: 'app_fight')]
	public function index(int $game_id, int $moviemon_id, EntityManagerInterface $em): Response
	{
		$game = $em->getRepository(GameState::class)->find($game_id);
		$moviemon = $em->getRepository(Moviemon::class)->find($moviemon_id);
		$player = $game->getPlayer();

		if ($player->getHealth() <= 0)
		{
			$em->remove($game);
			$em->flush();

			return $this->render('world_map/endGame.html.twig', [
				'win' => 0,
				'id' => $game->getId()
			]);
		}

		return $this->render('fight/index.html.twig', [
			'fight_title' => 'FightController',
			'player' => $player,
			'game' => $game,
			'moviemon' => $moviemon
		]);
	}

	#[Route(path:'/fight/attack/{game_id}/{moviemon_id}', name:'app_fight_attack')]
	public function attack(int $game_id, int $moviemon_id, EntityManagerInterface $em): Response
	{
		$game = $em->getRepository(GameState::class)->find($game_id);
		$moviemon = $em->getRepository(Moviemon::class)->find($moviemon_id);
		
		if (!rand(0, 100) % 3)
			return $this->redirectToRoute('app_fight', [
				'game_id' => $game->getId(),
				'moviemon_id' => $moviemon->getId()]
			);
		$player_attack = $game->getPlayer()->getStrength();
		$player = $game->getPlayer();
		$movie_health = $moviemon->getHealth() - $player_attack;
		if ($moviemon != null && $movie_health <= 0)
		{
			// moviemon get captured
			$moviemon->setCaptured(true)
				->setHealth(0)
				->setStrength(0)
				->setPosX(-1)
				->setPosY(-1);

			// increasing player strength
			$player->setStrength($player->getStrength() + 3);
			$player->setHealth(min(100, $player->getHealth() + 3));
			
			// update the positining where the moviemon was
			$map = $game->getMap();
			$map[$moviemon->getPosY()][$moviemon->getPosX()] = $player->getId();
			$game->setMap($map);

			$em->flush();
			return $this->redirectToRoute('app_world_map', [
				'id' => $game_id
			]);
		}
		$moviemon->setHealth($movie_health);
		$em->flush();
		return $this->redirectToRoute('app_fight', [
			'game_id' => $game->getId(),
			'moviemon_id' => $moviemon->getId()]
		);
	}

	#[Route(path:'/fight/escape/{game_id}/{moviemon_id}', name:'app_fight_escape')]
	public function escape(int $game_id, int $moviemon_id, EntityManagerInterface $em): Response
	{
		$game = $em->getRepository(GameState::class)->find($game_id);
		$moviemon = $em->getRepository(Moviemon::class)->find($moviemon_id);
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
	
	#[Route(path:'/fight/enemy-attack/{game_id}/{moviemon_id}', name:'app_fight_enemy_attack')]
	public function enemyAttack(int $game_id, int $moviemon_id, EntityManagerInterface $em): JsonResponse
	{
		$game = $em->getRepository(GameState::class)->find($game_id);
		$moviemon = $em->getRepository(Moviemon::class)->find($moviemon_id);
		$player = $game->getPlayer();
		$damage = $moviemon->getStrength();

		$player->setHealth(max(0, $player->getHealth() - $damage));

		if ($player->getHealth() <= 0) {
			$em->remove($game);
			$em->flush();
			return new JsonResponse([
				'status' => 'game_over',
				'player_health' => 0
			]);
		}

		$em->flush();

		return new JsonResponse([
			'status' => 'ok',
			'player_health' => $player->getHealth()
		]);
}
}
