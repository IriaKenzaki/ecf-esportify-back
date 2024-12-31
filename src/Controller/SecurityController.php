<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Repository\UserRepository;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'app_api_')]
class SecurityController extends AbstractController
{
    public function __construct(private EntityManagerInterface $manager, private SerializerInterface $serializer, private UserPasswordHasherInterface $passwordHasher, private UserRepository $userRepository)
    {
    }

    #[Route('/registration', name: 'registration', methods: 'POST')]
    #[OA\Post(
        path: "/api/registration",
        summary: "Créer un nouveau utilisateur",
        tags: ["Compte Utilisateur"],)]
        #[OA\RequestBody(
            required:true,
            description:"Données de l'utilisateur à inscrire",
            content: new OA\JsonContent(
                type:"object",
                properties:[new OA\Property(property:"email", type:"string", example:"adresse@email.com"),
                new OA\Property(property:"password", type:"string", example:"Mot de passe")]
            ))]
    #[OA\Response(
        response:201,
        description:"Utilisateur inscrit avec succès",
        content: new OA\JsonContent(
            type:"object",
            properties : [
            new OA\Property(property:"user", type:"string", example:"Mail d'utilisateur"),
            new OA\Property(property:"apiToken", type:"string", example:"31a023e212f116124a36af1r4ea0c1c3806b9378"),
            new OA\Property(property:"roles", type:"array", items:new OA\Items(type:"string", example:"ROLE_USER"))
            ]
        )
)]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));
        $user->setCreatedAt(new DateTimeImmutable());

        $this->manager->persist($user);
        $this->manager->flush();
        return new JsonResponse(
            ['user'  => $user->getEmail(), 'apiToken' => $user->getApiToken(), 'roles' => $user->getRoles()],
            Response::HTTP_CREATED
        );
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    #[OA\Post(
        path: "/api/login",
        summary: "Connecter un utilisateur",
        tags: ["Compte Utilisateur"],)]
        #[OA\RequestBody(
            required: true,
            description: "Données de l’utilisateur pour se connecter",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "username", type: "string", example: "adresse@email.com"),
                    new OA\Property(property: "password", type: "string", example: "Mot de passe")
                ]
            )
        )]
    #[OA\Response(
            response: 200,
            description: "Connexion réussie",
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                    new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                    new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string", example: "ROLE_USER"))
                ]
            )
    )]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return new JsonResponse(['message' => 'Les informations renseignées ne sont pas correctes'], Response::HTTP_UNAUTHORIZED);
        }

        if ($user) {
            $user->setLastLogin(new \DateTime());
            $this->manager->flush();
        }
        return new JsonResponse([
            'email'  => $user->getEmail(),
            'apiToken' => $user->getApiToken(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/account/me', name: 'me', methods: 'GET')]
    #[OA\Get(
        path: "/api/account/me",
        summary: "Récupérer les Informations de l'utilisateur",
        tags: ["Compte Utilisateur"],
    )]
    #[OA\Response(
        response: 200,
        description: "Retour de tous les champs",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "user", type: "string", example: "Nom d'utilisateur"),
                new OA\Property(property: "apiToken", type: "string", example: "31a023e212f116124a36af14ea0c1c3806eb9378"),
                new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string", example: "ROLE_USER"))
            ]
        )
    )]
    #[OA\Response(
        response: 401,
        description: "L'utilisateur n'est pas identifier"
    )]
    public function me(): JsonResponse
    {
        $user = $this->getUser();
        $responseData = $this->serializer->serialize($user, 'json', ['groups' => ["user_details"]]);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/account/edit', name: 'edit', methods: 'PUT')]
    #[OA\Put(
        path: "/api/account/edit",
        summary: "Modifier son compte utilisateur",
        tags: ["Compte Utilisateur"],
    )]
    #[OA\RequestBody(
        required: true,
        description: "Nouvelles données éventuelles de l'utilisateur à mettre à jour",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "email", type: "string", example: "example@example.com"),
                new OA\Property(property: "username", type: "string", example: "Benoit"),
                new OA\Property(property: "password", type: "string", example: "Benor")
            ]
        )
    )]
    #[OA\Response(
        response: 204,
        description: "Utilisateur modifié avec succès"
    )]
    #[OA\Response(
        response: 404,
        description: "Utilisateur non connecté"
    )]
    public function edit(Request $request): JsonResponse
    {
        $user = $this->serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $this->getUser()],
        );
        $user->setUpdatedAt(new DateTimeImmutable());

        if (isset($request->toArray()['password'])) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPassword()));
        }

        $this->manager->flush();

        return new JsonResponse([
            'email'  => $user->getEmail(),
            'apiToken' => $user->getApiToken(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route("/admin/users", name: "admin_list_users", methods: ["POST"])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: "/api/admin/users",
        summary: "Récupérer la liste des utilisateurs avec recherche par username",
        tags: ["Admin"],
        requestBody: new OA\RequestBody(
            description: "Requête pour rechercher les utilisateurs par username",
            required: false,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "username", type: "string", example: "john_doe")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Liste des utilisateurs récupérée avec succès",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "username", type: "string", example: "john_doe"),
                            new OA\Property(property: "email", type: "string", example: "john@example.com"),
                            new OA\Property(property: "roles", type: "array", items: new OA\Items(type: "string"), example: ["ROLE_USER"]),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: "Requête invalide"
            )
        ]
    )]
    public function listUsers(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;

        $users = $username
            ? $this->userRepository->findBy(['username' => $username])
            : $this->userRepository->findAll();

        $response = array_map(fn($user) => [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ], $users);

        return new JsonResponse($response, Response::HTTP_OK);
    }

    #[Route("/admin/users/edit", name: "admin_edit_user", methods: ["POST"])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: "/api/admin/users/edit",
        summary: "Modifier un utilisateur",
        tags: ["Admin"],
        requestBody: new OA\RequestBody(
            description: "Requête pour modifier un utilisateur",
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "username", type: "string", example: "john_doe"),
                    new OA\Property(property: "role", type: "string", enum: ["ROLE_USER", "ROLE_ORGANISATEUR", "ROLE_ADMIN"], example: "ROLE_ADMIN")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Utilisateur modifié avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Utilisateur introuvable"
            ),
            new OA\Response(
                response: 400,
                description: "Requête invalide"
            )
        ]
    )]
    public function editUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $response = [];
        $statusCode = Response::HTTP_BAD_REQUEST;

        $username = $data['username'] ?? null;
        $role = $data['role'] ?? null;

        if (!$this->validateRequestForEdit($username, $role)) {
            $response = ['message' => 'Requête invalide. Vérifiez les paramètres.'];
        } else {
            $user = $this->userRepository->findOneBy(['username' => $username]);

            if (!$user) {
                $response = ['message' => 'Utilisateur introuvable.'];
                $statusCode = Response::HTTP_NOT_FOUND;
            } else {
                [$response, $statusCode] = $this->updateUserRole($user, $role);
            }
        }

        return new JsonResponse($response, $statusCode);
    }

    private function validateRequestForEdit(?string $username, ?string $role): bool
    {
        if (!$username || !$role) {
            return false;
        }

        if (!in_array($role, ['ROLE_USER', 'ROLE_ORGANISATEUR', 'ROLE_ADMIN'])) {
            return false;
        }

        return true;
    }

    private function updateUserRole($user, string $role): array
    {
        $user->setRoles([$role]);
        $this->manager->flush();

        return [['message' => 'Rôle mis à jour avec succès.'], Response::HTTP_OK];
    }

    #[Route("/admin/users/{id}/delete", name: "admin_delete_user", methods: ["DELETE"])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        path: "/api/admin/users/{id}/delete",
        summary: "Supprimer un utilisateur",
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID de l'utilisateur à supprimer",
                required: true,
                schema: new OA\Schema(type : "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Utilisateur supprimé avec succès"
            ),
            new OA\Response(
                response: 404,
                description: "Utilisateur introuvable"
            ),
            new OA\Response(
                response: 400,
                description: "Requête invalide"
            )
        ]
    )]
    public function deleteUser(int $id): JsonResponse
    {
        $response = [];
        $user = $this->userRepository->find($id);
    
        if (!$user) {
            $response = ['message' => 'Utilisateur introuvable.'];
            $statusCode = Response::HTTP_NOT_FOUND;
        } else {
            $this->manager->remove($user);
            $this->manager->flush();
    
            $response = ['message' => 'Utilisateur supprimé avec succès.'];
            $statusCode = Response::HTTP_OK;
        }
    
        return new JsonResponse($response, $statusCode);
    }
    
}
