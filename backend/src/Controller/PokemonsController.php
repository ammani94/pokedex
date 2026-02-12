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

    #[Route('/signup', name: 'app_signup', methods: ['POST'])]
    public function CreateAccount(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Email ou mot de passe manquant',
            ], 400);
        }

        $existingUser = $entityManager->getRepository(Users::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser !== null) {
            return $this->json([
                'success' => false,
                'message' => 'Un compte avec cet email existe déjà',
            ], 400);
        }

        $user = new Users();
        $user->setEmail($data['email']);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);
        $user->setRole('normal');

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Compte créé avec succès',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ],
        ]);
    }


    #[Route('/logout')]
    public function logout(SessionInterface $session): Response
    {
        $session->clear();

        return $this->json([
            'success' => true,
            'message' => 'Déconnexion réussie',
        ]);
    }

    #[Route('/user')]
    public function GetUserData(SessionInterface $session): Response
    {
        $userId = $session->get('user_id');
        $userEmail = $session->get('user_email');
        
        if ($userId === null) {
            return $this->json([
                'success' => false,
                'message' => 'Aucune session active',
            ], 401);
        }

        return $this->json([
            'success' => true,
            'user' => [
                'id' => $userId,
                'email' => $userEmail,
            ],
        ]);
    }
}