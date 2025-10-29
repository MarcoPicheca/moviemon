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
	public function attack(GameState $game, Moviemon $moviemon, EntityManagerInterface $em): Response
	{
		$player_attack = $game->getPlayer()->getStrength();
		$player_health = $game->getPlayer()->getHealth();
		$player = $game->getPlayer();
		$movie_health = $moviemon->getHealth() - $player_attack;
		if ($movie_health <= 0)
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
            $map[$moviemon->getPosY()][$moviemon->getPosX()] = 0; // ← CORREZIONE: getPosX() non getPosY()
            $game->setMap($map);
            $game->getMoviemons()->toArray();
            // DEBUG: Verifica prima del flush
            $em->flush();
            dump($moviemon->isCaptured()); // Dovrebbe essere true
            dump($game->getCaptured()->count()); // torna sempre 0
            exit();
            
            return $this->redirectToRoute('app_world_map', [
                'id' => $game->getId()
            ]);
		}
		$moviemon->setHealth($movie_health);
		$em->flush();
		return $this->redirectToRoute('app_fight', [
			'id' => $game->getId(),
			'moviemon_id' => $moviemon->getId()]
		);
	}

	#[Route(path:'/fight/escape/{id}', name:'app_fight_escape')]
	public function escape(GameState $game)
	{
		return $this->redirectToRoute('app_world_map', [
			'id' => $game->getId()
		]);
	}
}
