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

class ApiAuthController extends AbstractController
{
    #[Route('/login')]
    public function login(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, SessionInterface $session): Response
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Nom d\'utilisateur ou mot de passe manquant',
            ], 400);
        }

        $user = $entityManager->getRepository(Users::class)->findOneBy(['email' => $data['email']]);

         if (!$user || !$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Nom d\'utilisateur ou mot de passe incorrect',
            ], 401);
        }
        $session->set('user_id', $user->getId());
        $session->set('user_email', $user->getEmail());
        
        return $this->json([
            'success' => true,
            'message' => 'Connexion réussie',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
            ],
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
    public function GetUserData(SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $userId = $session->get('user_id');
        $userEmail = $session->get('user_email');
        $pokemons = $entityManager->getRepository(Pokemons::class)->findBy(['user_id' => $userId]);
        $PokemonsList = array_map(function ($pokemon) {
            return [
                'id' => $pokemon->getId(),
                'user_id' => $pokemon->getUserId(),
                'api_id' => $pokemon->getApiId(),
                'captured_at' => $pokemon->getCapturedAt()
            ];
        }, $pokemons);
        if ($userId === null) {
            return $this->json([
                'success' => false,
                'message' => 'Aucune session active',
            ], 401);
        }
        return $this->json([
            'success' => true,
            'pokemons_count' => count($pokemons),
            'pokemons' => $PokemonsList,
            'user' => [
                'id' => $userId,
                'email' => $userEmail,
            ],
        ]);
    }
}