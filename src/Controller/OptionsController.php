<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\GameState;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\Migrations\Configuration\Migration\JsonFile;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Text;
use Exception;
use Monolog\Formatter\JsonFormatter;
use PhpParser\Node\Name;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;


final class OptionsController extends AbstractController
{
	#[Route('/', name: 'app_options')]
	public function index(Request $request, EntityManagerInterface $em): Response
	{
		$player = new Player();
		$form = $this->createFormBuilder($player)
			->add('name', TextType::class, [
				'label' => 'Player Name',
				'attr' => [
					'placeholder' => 'Enter your name',
				],
				'constraints' => [
					new Assert\Length([
                		'min' => 3,
                		'max' => 20,
                		'minMessage' => 'Nome troppo corto',
                		'maxMessage' => 'Nome troppo lungo',
					])
				]
			])
			->add('start', SubmitType::class, [
				'label' => 'New Game',
			])
			->getForm();
		
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$gameState = new GameState();
			$player->setHealth(100)->setStrength(20);
			$gameState->setTime(new \DateTimeImmutable('now', new \DateTimeZone('Europe/Rome')))
			->setPlayer($player)
			->setPosX(3)
			->setPosY(3)
			->setPlayerHealth($player->getHealth());
			$em->persist($player);
			$em->persist($gameState);
			$em->flush();
			return $this->redirectToRoute('app_world_map', [
				'id' => $gameState->getId(),
			]);
		}
		$gamesRepo = $em->getRepository(GameState::class);
		$games = null;
		try{
			if ($games = $gamesRepo->findAll())
				;
			else
			{
				throw new Exception('No games Repository found by Entity Manager!');
			}
		}
		catch(Exception $e){
			$e->getMessage();
		}
		return $this->render('options/index.html.twig', [
			'controller_name' => 'Options Page',
			'form' => $form,
			'games' => $games,
		]);
	}

	#[Route('/load/{id}', name: 'app_load_game')]
	public function loadSelected(int $id, EntityManagerInterface $em) : Response
	{
		$game = $em->getRepository(GameState::class)->find($id);
		return $this->redirectToRoute('app_world_map', [
			'id' => $game->getId(),
		]);
	}

	#[Route('/cancel', name: 'app_cancel_game')]
	public function cancel(EntityManagerInterface $em) : Response
	{
		$games = $em->getRepository(GameState::class)->findAll();
		$time = new DateTimeImmutable('2025-10-28 10:00:00', new DateTimeZone('Europe/Rome'));
		$game = null;
		foreach($games as $game_tmp)
		{
			if ($game_tmp->getTime() > $time)
			{
				$time = $game_tmp->getTime();
				$game = $game_tmp;
			}
		}
		return $this->redirectToRoute('app_world_map', [
			'id' => $game->getId(),
		]);
	}

	#[Route(path: '/save', name: 'app_save_game')]
	public function saveGameToJson(EntityManagerInterface $em) : Response
	{
		if (!is_dir("../history"))
		{
			mkdir("../history", 0777);
		}
		$games = $em->getRepository(GameState::class)->findAll();
		$time = new DateTimeImmutable('2025-10-28 10:00:00', new DateTimeZone('Europe/Rome'));
		$game = null;
		foreach($games as $game_tmp)
		{
			if ($game_tmp->getTime() > $time)
			{
				$time = $game_tmp->getTime();
				$game = $game_tmp;
			}
		}
		$captured = [];
		$remainings = [];
		$moviemons = array_values($game->getMoviemons()->toArray());

		foreach($moviemons as $moviemon)
		{
			if ($moviemon->isCaptured())
			{
				$captured []= [
					'id' => $moviemon->getId(),
					'Title' => $moviemon->getTitle(),
					'Year' => $moviemon->getYear(),
				];
			}
			else
			{
				$remainings []= [
					'id' => $moviemon->getId(),
					'Title' => $moviemon->getTitle(),
					'Year' => $moviemon->getYear(),
				];
			}
		}
		$json_game = [
			'id' => $game->getId(),
			'Player_pos_x' => $game->getPosX(),
			'Player_pos_y' => $game->getPosY(),
			'Player_name' => $game->getPlayer()->getName(),
			'Player_id' => $game->getPlayer()->getId(),
			'captured'  => $captured,
			'remaining' => $remainings,
		];
		$json_data = json_encode($json_game, JSON_PRETTY_PRINT);
		try{
			$path = '../history/' . $json_game['Player_name'] . '_' . $json_game['id'];
			file_put_contents($path, $json_data);
		}
		catch(Exception $e){
			echo 'Message:' . $e->getMessage();
		}
		return $this->redirectToRoute('app_options');
	}
}
