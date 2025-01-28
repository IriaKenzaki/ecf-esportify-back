<?php

namespace App\Controller;

use App\Document\Review;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api', name:'app_api_')]
class ReviewController extends AbstractController
{
    private $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;

    }

    #[Route("/avis", methods: "POST")]
    #[OA\Post(
        path: "/api/avis",
        summary: "Créer un nouveau avis",
        tags: ["Avis"],
    )]
    #[OA\RequestBody(
        required: true,
        description: "Données de l'avis à créer",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "title", type: "string", example: "Voici mon avis"),
                new OA\Property(property: "content", type: "string", example: "Contenu de l'avis"),
                new OA\Property(property: "rating", type: "integer", example: 5)
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Avis créé avec succès.",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "title", type: "string", example: "Voici mon avis"),
                new OA\Property(property: "content", type: "string", example: "Contenu de l'avis"),
                new OA\Property(property: "rating", type: "integer", example: 5),
                new OA\Property(property: "user", type: "string", example: "Connie")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Données manquantes ou invalides"
    )]
    #[OA\Response(
        response: 401,
        description: "L'utilisateur n'est pas connecté."
    )]
    public function createAvis(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        $response = null;
    
        if (!$currentUser) {
            $response = new JsonResponse(['error' => 'L\'utilisateur n\'est pas connecté.'], JsonResponse::HTTP_UNAUTHORIZED);
        } else {
            $data = json_decode($request->getContent(), true);
    
            $validationError = $this->validateReviewData($data);
            if ($validationError) {
                $response = new JsonResponse(['error' => $validationError], JsonResponse::HTTP_BAD_REQUEST);
            } else {
                $review = new Review();
                $review->setTitle($data['title']);
                $review->setContent($data['content']);
                $review->setRating($data['rating']);
                $review->setUser($currentUser->getUserIdentifier());
    
                $this->dm->persist($review);
                $this->dm->flush();
    
                $response = new JsonResponse(['status' => 'Avis créé'], JsonResponse::HTTP_CREATED);
            }
        }
    
        return $response;
    }
    
    private function validateReviewData(array $data): ?string
    {
        $error = null;
    
        if (!isset($data['title'], $data['content'], $data['rating'])) {
            $error = 'Données manquantes';
        } elseif (strlen($data['title']) < 3 || strlen($data['title']) > 255) {
            $error = 'Le titre doit être compris entre 3 et 255 caractères';
        } elseif (strlen($data['content']) < 10) {
            $error = 'Le contenu doit faire au moins 10 caractères';
        } elseif ($data['rating'] < 1 || $data['rating'] > 5) {
            $error = 'La note doit être comprise entre 1 et 5';
        }
    
        return $error;
    }



    #[Route("/reviews", methods: "GET")]
    #[OA\Get(
        path: "/api/reviews",
        summary: "Obtenir la liste des avis",
        tags: ["Avis"]
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des avis",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(property: "id", type: "string", example: "1"),
                    new OA\Property(property: "title", type: "string", example: "Voici mon avis"),
                    new OA\Property(property: "content", type: "string", example: "Contenu de l'avis"),
                    new OA\Property(property: "rating", type: "integer", example: 5),
                    new OA\Property(property: "user", type: "string", example: "Connie"),
                    new OA\Property(property: "createdAt", type: "string", format: "date-time", example: "2025-01-02T12:00:00Z")
                ]
            )
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Aucun avis trouvé"
    )]
    public function getAvis(): JsonResponse
    {
        $reviews = $this->dm->getRepository(Review::class)->findAll();

        if (empty($reviews)) {
            return new JsonResponse(['message' => 'Aucun avis trouvé'], 404);
        }

        $response = [];
        foreach ($reviews as $review) {
            $response[] = [
                'id' => $review->getId(),
                'title' => $review->getTitle(),
                'content' => $review->getContent(),
                'rating' => $review->getRating(),
                'user' => $review->getUser(),
                'createdAt' => $review->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse($response);
    }

    #[Route("/avis/{id}", methods: "DELETE")]
    #[IsGranted('ROLE_USER')]
    #[OA\Delete(
        path: "/api/avis/{id}",
        summary: "Supprimer un avis par son ID",
        tags: ["Avis"]
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID de l'avis à supprimer",
        schema: new OA\Schema(type: "string")
    )]
    #[OA\Response(
        response: 200,
        description: "Avis supprimé avec succès."
    )]
    #[OA\Response(
        response: 404,
        description: "Avis introuvable."
    )]
    #[OA\Response(
        response: 401,
        description: "L'utilisateur n'est pas connecté."
    )]
    public function deleteAvis(string $id): JsonResponse
    {
        $currentUser = $this->getUser();

        if (!$currentUser) {
            return new JsonResponse(['error' => 'L\'utilisateur n\'est pas connecté.'], JsonResponse::HTTP_UNAUTHORIZED);
        }
        $review = $this->dm->getRepository(Review::class)->find($id);
        if (!$review) {
            return new JsonResponse(['error' => 'Avis introuvable.'], JsonResponse::HTTP_NOT_FOUND);
        }
        if (!$this->isGranted('ROLE_ADMIN') && $review->getUser() !== $currentUser->getUserIdentifier()) {
            return new JsonResponse(['error' => 'Vous n\'avez pas la permission de supprimer cet avis.'], JsonResponse::HTTP_FORBIDDEN);
        }
        $this->dm->remove($review);
        $this->dm->flush();

        return new JsonResponse(['message' => 'Avis supprimé avec succès.'], JsonResponse::HTTP_OK);
    }
}
