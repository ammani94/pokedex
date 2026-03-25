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
use App\Entity\TeamPokemon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\TeamPokemonRepository;

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

    #[Route('/addPokemonTeam')]
    public function addPokemonTeam(Request $request, EntityManagerInterface $entityManager, SessionInterface $session)
    {
        $data = json_decode($request->getContent(), true);
        $userId = $session->get('user_id');
        $team_pokemon = new TeamPokemon();
        $team_pokemon->setTeamId($data['team_id']);
        $team_pokemon->setPokemonId($data['pokemon_id']);
        $team_pokemon->setPosition(1);

        $entityManager->persist($team_pokemon);

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'pokemon ajouté à l\'équipe'
        ]);
    }

    #[Route('/getPokemonsInTeams/{id}')]
    public function getPokemonsInTeams(Request $request,EntityManagerInterface $entityManager, int $id, SessionInterface $session, TeamPokemonRepository $teamRepository)
    {
        $data = json_decode($request->getContent(), true);
        error_log(print_r($id,1));
        $pokemons = $teamRepository->findByExampleField($data['userId'], $id);
        error_log(print_r($pokemons,1));
        if (count($pokemons) > 0) {
            $PokemonsList = array_map(function ($pokemon) {
                return [
                    'id' => $pokemon->getId(),
                    'user_id' => $pokemon->getUserId(),
                    'api_id' => $pokemon->getApiId(),
                    'captured_at' => $pokemon->getCapturedAt()
                ];
            }, $pokemons);
            
            return $this->json([
                'success' => true,
                'pokemons' => $PokemonsList,
            ]);
        }
        return $this->json([
            'success' => false,
            'pokemons' => null,
        ]);

        
    }
}