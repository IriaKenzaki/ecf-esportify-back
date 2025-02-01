<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use OpenApi\Attributes as OA;

#[Route('api', name:'app_api_')]
class MessageController extends AbstractController
{
    #[Route('/contact', methods: ['POST'])]
    #[OA\Post(
        path: "/api/contact",
        summary: "Créer un nouveau message",
        tags: ["Contact"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: "object",
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Problème de connexion"),
                    new OA\Property(property: "text", type: "string", example: "Je n'arrive pas à me connecter à mon compte"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Message enregistré avec succès",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "title", type: "string", example: "Problème de connexion"),
                new OA\Property(property: "user", type: "string", example: "Agrata"),
                new OA\Property(property: "message", type: "string", example: "Message enregistré avec succès")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: "Données incomplètes ou non valides"
    )]
    public function createMessage(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        if (!$user || !isset($data['title'], $data['text'])) {
            return new JsonResponse(['error' => 'Données incomplètes'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $message = new Message();
        $message->setUser($user->getUsername());
        $message->setTitle($data['title']);
        $message->setText($data['text']);

        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
        }

        $em->persist($message);
        $em->flush();

        return new JsonResponse(['message' => 'Message enregistré avec succès'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/contact', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: "/api/contact",
        summary: "Récupérer tous les messages",
        tags: ["Contact"]
    )]
    #[OA\Response(
        response: 200,
        description: "Liste des messages récupérée avec succès",
        content: new OA\JsonContent(
            type: "array",
            items: new OA\Items(
                type: "object",
                properties: [
                    new OA\Property(property: "id", type: "integer", example: "1"),
                    new OA\Property(property: "user", type: "string", example: "john_doe"),
                    new OA\Property(property: "title", type: "string", example: "Problème de connexion"),
                    new OA\Property(property: "text", type: "string", example: "Je n'arrive pas à me connecter à mon compte"),
                ]
            )
        )
    )]
    public function getMessages(EntityManagerInterface $em): JsonResponse
    {
        $messages = $em->getRepository(Message::class)->findAll();
        $data = [];

        foreach ($messages as $message) {
            $data[] = [
                'id' => $message->getId(),
                'user' => $message->getUser(),
                'title' => $message->getTitle(),
                'text' => $message->getText(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/contact/{id}', methods: ['DELETE'])] 
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        path: "/api/contact/{id}",
        summary: "Supprimer un message",
        tags: ["Contact"],)]
        #[OA\Parameter(
            name:"id",
            in:"path",
            required:true,
            description:"Id du message que vous voulez afficher",
            schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response: 200,
        description: "Message supprimé avec succès",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "message", type: "string", example: "Message supprimé avec succès")
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Message non trouvé"
    )]
    public function deleteMessage(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Récupérer le message avec l'ID
        $message = $em->getRepository(Message::class)->find($id);
    
        // Vérifier si le message existe
        if (!$message) {
            return new JsonResponse(['error' => 'Message non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        // Supprimer le message
        $em->remove($message);
        $em->flush();
    
        return new JsonResponse(['message' => 'Message supprimé avec succès'], JsonResponse::HTTP_OK);
    }


}
