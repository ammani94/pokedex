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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PokemonsController extends AbstractController
{
    #[Route('/catch')]
    public function catch(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $data = json_decode($request->getContent(), true);
        $userId = $session->get('user_id');

        $pokemons = $entityManager->getRepository(Pokemons::class)->findBy([
            'api_id' => $data['api_id'],
            'user_id' => $userId,
        ]);
        if ($pokemons) {
            return $this->json([
                'success' => false,
                'message' => 'Pokémon '.$data['name']. ' déjà capturé',
            ]);
        }

        $pokemon = new Pokemons();
        $pokemon->setApiId($data['api_id']);
        $pokemon->setUserId($userId);
        $pokemon->setCapturedAt(new \DateTime());
        $entityManager->persist($pokemon);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Pokémon '.$data['name']. ' capturé',
        ]);
    }

    #[Route('/getpokemons')]
    public function GetPokemons(EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        $userId = $session->get('user_id');

        $pokemons = $entityManager->getRepository(Pokemons::class)->findBy(['user_id' => 1]);
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

    #[Route('/deletePokemon/{id}')]
    public function deletePokemons(EntityManagerInterface $entityManager, int $id, SessionInterface $session)
    {
        $pokemon = $entityManager->getRepository(Pokemons::class)->findBy([
            'user_id' => 1,
            'api_id' => $id
        ]);
        if ($pokemon) {
            $entityManager->remove($pokemon[0]);
            $entityManager->flush();
            return $this->GetPokemons($entityManager, $session);
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Élément non trouvé'
            ]);
        }
    }
}