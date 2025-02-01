<?php


namespace App\Controller;
use App\Entity\Score;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\ScoreRepository;
use App\Repository\UserRepository;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api', name:'app_api_')]
class ScoreController extends AbstractController{
    public function __construct(
        private EntityManagerInterface $manager,
        private EventRepository $eventRepository,
        private ScoreRepository $scoreRepository,
        private SerializerInterface $serializer,
        private UserRepository $userRepository){}

    #[Route('/{eventId}/add-scores', name: 'add_scores', methods: 'POST')]
    #[OA\Post(
        path: "/api/{eventId}/add-scores",
        summary: "Ajouter des scores pour un événement",
        description: "Cette route permet d'ajouter des scores pour les participants d'un événement spécifique.",
        tags: ["Scores"],
    )]
    #[OA\Parameter(
        name: "eventId",
        in: "path",
        required: true,
        description: "ID de l'événement pour lequel les scores doivent être ajoutés.",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        description: "Données des scores à ajouter. Le format doit inclure un tableau de scores contenant les identifiants des utilisateurs et leurs scores.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(property: "scores",type: "array",description: "Liste des scores à ajouter",
                    items: new OA\Items(type: "object",
                            properties: [
                                new OA\Property(property: "username", type: "string", description: "Username de l'utilisateur pour lequel le score doit être ajouté"),
                                new OA\Property(property: "score", type: "integer", description: "Le score attribué à l'utilisateur")
                            ])
                    )]
            ))
    )]
    #[OA\Response(
        response: 201,
        description: "Scores ajoutés avec succès.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(property: "message", type: "string", description: "Message de succès indiquant que les scores ont été ajoutés")
                ]))
    )]
    #[OA\Response(
        response: 400,
        description: "Format de données invalide.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(property: "error", type: "string", description: "Message d'erreur décrivant la raison de l'échec")
                ]))
    )]
    #[OA\Response(
        response: 404,
        description: "Événement introuvable.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(property: "error", type: "string", description: "Message d'erreur indiquant que l'événement est introuvable")
                ]))
    )]
    public function addScores(Request $request, int $eventId): JsonResponse
    {
        $responseData = [];
        $statusCode = Response::HTTP_CREATED;
        $event = $this->eventRepository->find($eventId);
        if (!$event) {
            $responseData = ['error' => 'Event not found'];
            $statusCode = Response::HTTP_NOT_FOUND;
        } else {
            $data = json_decode($request->getContent(), true);
            if (!$this->isValidScoresPayload($data)) {
                $responseData = ['error' => 'Invalid data format'];
                $statusCode = Response::HTTP_BAD_REQUEST;
            } else {
                $errors = $this->processScores($data['scores'], $event);
                if (!empty($errors)) {
                    $responseData = ['error' => implode(', ', $errors)];
                    $statusCode = Response::HTTP_BAD_REQUEST;
                } else {
                    $this->manager->flush();
                    $responseData = ['message' => 'Scores added successfully'];
                }
            }
        }
        return new JsonResponse($responseData, $statusCode);
    }

    private function isValidScoresPayload(?array $data): bool
    {
        return isset($data['scores']) && is_array($data['scores']);
    }

    private function processScores(array $scores, Event $event): array
    {
        $errors = [];
        foreach ($scores as $scoreData) {
            if (!$this->isValidScoreData($scoreData)) {
                $errors[] = 'Invalid username or score format';
                continue;
            }
            $user = $this->userRepository->findOneBy(['username' => $scoreData['username']]);
            if (!$user) {
                $errors[] = "User with Username {$scoreData['username']} not found";
                continue;
            }

            $existingScore = $this->scoreRepository->findOneBy(['event' => $event, 'user' => $user]);
            if ($existingScore) {
                $errors[] = "User with Username {$scoreData['username']} already has a score for this event";
                continue;
            }

            $score = new Score();
            $score->setEvent($event);
            $score->setUser($user);
            $score->setScore($scoreData['score']);
            $this->manager->persist($score);
        }
        return $errors;
    }
    private function isValidScoreData(array $scoreData): bool
    {
        return isset($scoreData['username'], $scoreData['score']) && is_int($scoreData['score']);
    }

    #[Route('/scores', name: 'get_user_scores', methods: 'GET')]
    #[OA\Get(
        path: "/api/scores",
        summary : "Récupèrer Tous les scores de l'utilisateur",
        tags: ["Scores"],
    )]
    #[OA\Response(
        response: 200,
        description: 'Retour de tous les champs',
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "score", type: 'integer', example: "100"),
                new OA\Property(property: "createdAt", type: 'date-time', example: "2024-12-16 14:50:00"),
                new OA\Property(property: "eventId", type: "integer", example: "1"),
                new OA\Property(property: 'eventTitle', type: "string", example: "Soirée de folie")
            ])
    )]
    #[OA\Response(
        response:404,
        description:"Aucun évènement n'as étais trouvé.",
    )]
    public function getUserScores(): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser){
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }
        $scores = $this->manager->getRepository(Score::class)->findBy(
            ['user' => $currentUser],
            ['createdAt' => 'DESC']
        );

        $data = array_map(function (Score $score){
            return [
            'eventId' => $score->getEvent()->getId(),
            'eventTitle'=> $score->getEvent()->getTitle(),
            'score' => $score->getScore(),
            'createdAt' => $score->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        },
    $scores);

        return new JsonResponse($data, Response::HTTP_OK, []);
    }

}
