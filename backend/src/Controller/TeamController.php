<?php

namespace App\Controller;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Entity\Users;
use App\Entity\Pokemons;
use App\Entity\UserPokemon;
use App\Entity\Team;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TeamController extends AbstractController
{
    #[Route('/create_team')]
    public function CreateTeam(Request $request, EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['name'])) {
            return $this->json([
                'success' => false,
                'message' => 'Nom d\'équipe manquant',
            ]);
        }

        $team = $entityManager->getRepository(Team::class)->findBy(['name' => $data['name']]);

        if ($team) {
            return $this->json([
                'success' => false,
                'message' => 'L\'équipe '.$data['name'].' existe déjà',
            ]);
        }

        $userId = $session->get('user_id');
        $team = new Team();
        $team->setName($data['name']);
        $team->setUserId($userId);

        $entityManager->persist($team);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Équipe créée',
            'team' => [
                'id' => $team->getId(),
                'name' => $team->getName(),
            ],
        ]);
    }

    #[Route('/getTeams')]
    public function GetTeams(Request $request, EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $data = json_decode($request->getContent(), true);
        $userId = $session->get('user_id');
        $teams = $entityManager->getRepository(Team::class)->findBy(
            ['user_id' => 1]
        );
        $TeamList = array_map(function ($team) {
            return [
                'id' => $team->getId(),
                'user_id' => $team->getUserId(),
                'name' => $team->getName()
            ];
        }, $teams);

        return $this->json([
            'success' => true,
            'teams' => $TeamList,
            'user_id' => $userId
        ]);
    }
}