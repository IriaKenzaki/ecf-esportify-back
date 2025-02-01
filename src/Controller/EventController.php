<?php

namespace App\Controller;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Service\BlacklistService;
use App\Entity\ListParticipant;
use App\Service\ParticipantService;
use DateTimeImmutable;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('api', name:'app_api_event_')]
class EventController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private BlacklistService $blacklistService,
        private ParticipantService $participantService,
        private EventRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ){
        $this->blacklistService = $blacklistService;
        $this->participantService = $participantService;
    }

    #[Route('/all', name: 'show_all', methods: 'GET')]
    #[OA\Get(
        path: "/api/all",
        summary: "Afficher touts les events en cours validés",
        tags: ["Public"])]
    #[OA\Response(
        response:200,
        description:"Evènement afficher avec sucés.",
        content: new OA\JsonContent(
            type:"object",
            properties : [new OA\Property(property:"id", type:"integer", example:"1"),
            new OA\Property(property:"title", type:"string", example:"Jeux de société"),
            new OA\Property(property:"description", type:"string", example:"Venez passé un moment"),
            new OA\Property(property:"players", type:"integer", example:"100"),
            new OA\Property(property:"createdAt", type:"dateTimeImmutable", example:"2025-10-01T12:00:00+00:00"),
            new OA\Property(property:"updatedAt", type:"dateTimeImmutable", example:"2025-10-01T13:00:00+00:00"),
            new OA\Property(property:"dateTimeStart", type:"dateTime", example:"2025-12-01T16:00:00"),
            new OA\Property(property:"dateTimeEnd", type:"dateTime", example:"2025-12-01T17:00:00"),
            new OA\Property(property:"createdBy", type:"string", example:"bibi"),
            new OA\Property(property:"game", type:"string", example:"Tetris"),
            new OA\Property(property:"image", type:"string", example:"Lien de l'image, pas d'obligation"),
            new OA\Property(property:"visibility", type:"bool", example:"true"),
            new OA\Property(property:"started", type:"bool", example:"true/false")]
        )
    )]
    #[OA\Response(
        response:404,
        description:"Aucun évènement n'as étais trouvé.",
    )]
    public function showAll(): JsonResponse
    {
        $events = $this->repository->findAll();
        $visibleEvents = array_filter($events, function($event) {
            $dateTimeEnd = $event->getDateTimeEnd();
            if (!$dateTimeEnd instanceof \DateTime) {
                $dateTimeEnd = new \DateTime($dateTimeEnd);
            }
            return $event->isVisibility() === true && $dateTimeEnd > new \DateTime();
        });
    
        if ($visibleEvents) {
            $responseData = $this->serializer->serialize($visibleEvents, 'json', ['groups' => 'user_events']);
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
    
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/event', name: 'event', methods: ['POST'])]
    #[IsGranted('ROLE_ORGANISATEUR')]
    #[OA\Post(
        path: "/api/event",
        summary: "Créer un nouveau event",
        tags: ["Events"],
    )]
    #[OA\RequestBody(
        required:true,
        description:"Données de l'évènement à créer",
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Soirée jeux"),
                    new OA\Property(property: "description", type: "string", example: "Venez pour un bon moment"),
                    new OA\Property(property: "players", type: "integer", example: "100"),
                    new OA\Property(property: "dateTimeStart", type: "string", example: "2025-12-01T16:00:00"),
                    new OA\Property(property: "dateTimeEnd", type: "string", example: "2025-12-01T17:00:00"),
                    new OA\Property(property: "game", type: "string", example: "Tetris"),
                    new OA\Property(property: "image", type: "string", format: "binary", description: "Image de l'événement"),
                    new OA\Property(property: "visibility", type: "bool", example: "false"),
                    new OA\Property(property: "started", type:"bool", example:"false")
                ]
            )
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Evènement créé avec succès.",
        content: new OA\JsonContent(
            type: "object",
            properties: [
                new OA\Property(property: "id", type: "integer", example: "1"),
                new OA\Property(property: "title", type: "string", example: "Soirée jeux de société"),
                new OA\Property(property: "description", type: "string", example: "Venez passer un bon moment"),
                new OA\Property(property: "players", type: "integer", example: "100"),
                new OA\Property(property: "createdAt", type: "string", example: "2025-10-01T10:00:00+00:00"),
                new OA\Property(property: "updatedAt", type: "string", example: "2025-10-01T11:00:00+00:00"),
                new OA\Property(property: "dateTimeStart", type: "string", example: "2025-12-01T12:00:00"),
                new OA\Property(property: "dateTimeEnd", type: "string", example: "2025-12-01T13:00:00"),
                new OA\Property(property: "createdBy", type: "string", example: "bibi"),
                new OA\Property(property: "game", type: "string", example: "Tetris"),
                new OA\Property(property: "image", type: "string", example: "Lien de l'image"),
                new OA\Property(property: "visibility", type: "bool", example: "false"),
                new OA\Property(property: "started", type:"bool", example:"false")
            ]
        )
    )]
    public function new(Request $request): JsonResponse
    {
        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $players = $request->request->get('players');
        $dateTimeStart = $request->request->get('dateTimeStart');
        $dateTimeEnd = $request->request->get('dateTimeEnd');
        $game = $request->request->get('game');
        
        $visibility = $request->request->get('visibility');
        $visibility = filter_var($visibility, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (is_null($visibility)) {
            $visibility = false;
        }
    
        $started = $request->request->get('started');
        $started = filter_var($started, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (is_null($started)) {
            $started = false;
        }
        
        $startDateTime = new \DateTime($dateTimeStart);
        $endDateTime = new \DateTime($dateTimeEnd);
    
        if ($startDateTime < new \DateTime()) {
            return new JsonResponse(['error' => 'La date de début ne peut pas être antérieure à la date actuelle.'], Response::HTTP_BAD_REQUEST);
        }
        if ($endDateTime < new \DateTime()) {
            return new JsonResponse(['error' => 'La date de fin ne peut pas être antérieure à la date actuelle.'], Response::HTTP_BAD_REQUEST);
        }
        if ($startDateTime > $endDateTime) {
            return new JsonResponse(['error' => 'La date de début ne peut pas être après la date de fin.'], Response::HTTP_BAD_REQUEST);
        }
    
        $event = new Event();
        $event->setTitle($title);
        $event->setDescription($description);
        $event->setPlayers($players);
        $event->setDateTimeStart($startDateTime);
        $event->setDateTimeEnd($endDateTime);
        $event->setGame($game);
        $event->setVisibility($visibility);
        $event->setStarted($started);
        
        /** @var UploadedFile $imageFile */
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            $imageFilename = uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move($this->getParameter('images_directory'), $imageFilename);
            $event->setImage($imageFilename);
        } else {
            $event->setImage(null);
        }
        
        $event->setCreatedAt(new \DateTimeImmutable());
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
        }
        
        $event->setCreatedBy($currentUser->getUserIdentifier());
        
        $listParticipant = new ListParticipant();
        $listParticipant->setEvent($event);
        
        $this->manager->persist($event);
        $this->manager->persist($listParticipant);
        $this->manager->flush();
        
        $responseData = $this->serializer->serialize($event, 'json');
        
        $location = $this->urlGenerator->generate(
            'app_api_event_show',
            ['id' => $event->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }


    #[Route('/{id}/details', name: 'show', methods: 'GET')]
    #[OA\Get(
        path: "/api/{id}/details",
        summary: "Afficher un event par son ID",
        tags: ["Events"],)]
        #[OA\Parameter(
            name:"id",
            in:"path",
            required:true,
            description:"Id de l'évent que vous voulez afficher",
            schema: new OA\Schema(type: "integer"))]
    #[OA\Response(
        response:200,
        description:"Evènement afficher avec sucés.",
        content: new OA\JsonContent(
            type:"object",
            properties : [new OA\Property(property:"id", type:"integer", example:"1"),
            new OA\Property(property:"title", type:"string", example:"Soirée jeux de société"),
            new OA\Property(property:"description", type:"string", example:"Venez passé un bon moment"),
            new OA\Property(property:"players", type:"integer", example:"100"),
            new OA\Property(property:"createdAt", type:"dateTimeImmutable", example:"2025-10-01T10:00:00+00:00"),
            new OA\Property(property:"updatedAt", type:"dateTimeImmutable", example:"2025-10-01T11:00:00+00:00"),
            new OA\Property(property:"dateTimeStart", type:"date-Time", example:"2025-12-01T18:00:00"),
            new OA\Property(property:"dateTimeEnd", type:"date-Time", example:"2025-12-01T19:00:00"),
            new OA\Property(property:"createdBy", type:"string", example:"bibi"),
            new OA\Property(property:"game", type:"string", example:"Tetris"),
            new OA\Property(property:"image", type:"string", example:"Lien de l'image, non obligatoire"),
            new OA\Property(property:"visibility", type:"bool", example:"true"),
            new OA\Property(property:"started", type:"bool", example:"true/false")
            ]
        )
)]
    #[OA\Response(
        response:404,
        description:"Aucun évènement n'as étais trouvé.",
    )]
    public function show(int $id): JsonResponse
    {
        $event = $this->repository->findOneBy(['id' => $id]);
        if ($event && $event->isVisibility() === true) {
            $responseData = $this->serializer->serialize($event, 'json', ['groups' => 'user_events']);
            
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
    
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
    
    #[Route('/{id}', name: 'edit_event', methods: ['PUT'])]
    #[IsGranted('ROLE_ORGANISATEUR')]
    #[OA\Put(
        path: "/api/{id}",
        summary: "Modifier un événement",
        tags: ["Events"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Soirée jeux modifiée"),
                    new OA\Property(property: "description", type: "string", example: "Venez passer un bon moment"),
                    new OA\Property(property: "players", type: "integer", example: "150"),
                    new OA\Property(property: "dateTimeStart", type: "string", example: "2025-12-01T16:00:00"),
                    new OA\Property(property: "dateTimeEnd", type: "string", example: "2025-12-01T18:00:00"),
                    new OA\Property(property: "game", type: "string", example: "Tetris"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 204,
        description: "Événement modifié avec succès"
    )]
    #[OA\Response(
        response: 404,
        description: "Événement non trouvé"
    )]
    public function editEvent(int $id, Request $request): JsonResponse
    {
        $event = $this->repository->findOneBy(['id' => $id]);
    
        if (!$event) {
            return new JsonResponse(['error' => 'Événement non trouvé'], Response::HTTP_NOT_FOUND);
        }
    
        $data = json_decode($request->getContent(), true);
    
        if (isset($data['title'])) {
            $event->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $event->setDescription($data['description']);
        }
        if (isset($data['players'])) {
            $event->setPlayers($data['players']);
        }
    
        if (isset($data['dateTimeStart'])) {
            $startDateTime = new \DateTime($data['dateTimeStart']);
            if ($startDateTime < new \DateTime()) {
                return new JsonResponse(['error' => 'La date de début ne peut pas être antérieure à la date actuelle.'], Response::HTTP_BAD_REQUEST);
            }
            $event->setDateTimeStart($startDateTime);
        }
        if (isset($data['dateTimeEnd'])) {
            $endDateTime = new \DateTime($data['dateTimeEnd']);
            if ($endDateTime < new \DateTime()) {
                return new JsonResponse(['error' => 'La date de fin ne peut pas être antérieure à la date actuelle.'], Response::HTTP_BAD_REQUEST);
            }
            if (isset($startDateTime) && $endDateTime < $startDateTime) {
                return new JsonResponse(['error' => 'La date de fin ne peut pas être avant la date de début.'], Response::HTTP_BAD_REQUEST);
            }
            $event->setDateTimeEnd($endDateTime);
        }
        if (isset($data['game'])) {
            $event->setGame($data['game']);
        }
    
        $this->manager->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    
    
    #[Route('/{id}/image', name: 'edit_event_image', methods: ['POST'])]
    #[IsGranted('ROLE_ORGANISATEUR')]
    #[OA\Post(
        path: "/api/{id}/image",
        summary: "Mettre à jour l'image de l'événement",
        tags: ["Events"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: "multipart/form-data",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(property: "image", type: "string", format: "binary", description: "Image de l'événement"),
                ]
            )
        )
    )]
    #[OA\Response(
        response: 204,
        description: "Image de l'événement mise à jour avec succès"
    )]
    #[OA\Response(
        response: 404,
        description: "Événement non trouvé"
    )]
    public function editEventImage(int $id, Request $request): JsonResponse
    {
        $event = $this->repository->findOneBy(['id' => $id]);
    
        if (!$event) {
            return new JsonResponse(['error' => 'Événement non trouvé'], Response::HTTP_NOT_FOUND);
        }
    
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            $imageFilename = uniqid() . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move($this->getParameter('images_directory'), $imageFilename);
            $event->setImage($imageFilename);
            $this->manager->flush();
        }
    
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    #[IsGranted('ROLE_ORGANISATEUR')]
    #[OA\Delete(
        path: '/api/{id}',
        summary: 'Supprimer un évènement par ID',
        tags: ["Events"],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: "ID de l'évènement à supprimer",
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: 'Evènement supprimé avec succès'
            ),
            new OA\Response(
                response: 404,
                description: 'Evènement non trouvé'
            )
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        $event = $this->repository->findOneBy(['id' => $id]);
        if ($event) {
            $this->manager->remove($event);
            $this->manager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
    
    #[Route('/events/{eventId}/add-participant', name: 'event_add_participant', methods: 'POST')]
    #[OA\Post(
        path: '/api/events/{eventId}/add-participant',
        summary: 'Inscription à un évènement',
        tags: ["Participants"],)]
    #[OA\Parameter(
        name: "eventId",
        in: "path",
        required: true,
        description: "ID de l'événement auquel vous voulez vous inscrire",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Inscription à l'évènement avec succès"
    )]
    #[OA\Response(
        response: 404,
        description: "Aucun événement n'a été trouvé"
    )]
    public function addParticipant(int $eventId): JsonResponse
    {
        $responseData = ['message' => 'User successfully added to event'];
        $statusCode = Response::HTTP_OK;
        $errorMessage = null;
    
        $event = $this->repository->find($eventId);
    
        if (!$event) {
            $errorMessage = 'Events not found';
            $statusCode = Response::HTTP_NOT_FOUND;
        } elseif (!$this->getUser()) {
            $errorMessage = 'User not connected';
            $statusCode = Response::HTTP_UNAUTHORIZED;
        } elseif ($this->participantService->isEventFull($event)) {
            $errorMessage = 'Event is full.';
            $statusCode = Response::HTTP_FORBIDDEN;
        } elseif ($this->participantService->isUserInEvent($event, $this->getUser())) {
            $errorMessage = 'User already in this Event.';
            $statusCode = Response::HTTP_FORBIDDEN;
        } elseif ($this->blacklistService->isUserBlacklisted($event, $this->getUser())) {
            $errorMessage = 'User is blacklisted from this event';
            $statusCode = Response::HTTP_FORBIDDEN;
        }
    
        if ($errorMessage) {
            return $this->createErrorResponse($errorMessage, $statusCode);
        }
    
        $currentUser = $this->getUser();
        $listParticipants = $event->getListParticipants();
        $listParticipant = null;
    
        foreach ($listParticipants as $lp) {
            if (!$lp->getParticipants()->contains($currentUser)) {
                $listParticipant = $lp;
                break;
            }
        }
    
        if (!$listParticipant) {
            $listParticipant = new ListParticipant();
            $listParticipant->setEvent($event);
            $this->manager->persist($listParticipant);
        }
    
        $listParticipant->addParticipant($currentUser);
        $this->manager->flush();
    
        return new JsonResponse($responseData, $statusCode);
    }
    
    private function createErrorResponse(string $message, int $statusCode): JsonResponse
    {
        return new JsonResponse(['error' => $message], $statusCode);
    }
    
    
    

    #[Route('/events/{eventId}/remove-participant', name: 'event_remove_participant', methods: 'DELETE')]
    #[OA\Delete(
        path: "/api/events/{eventId}/remove-participant",
        summary: "Supprimer un participant d'un évènement",
        tags: ["Participants"],
        description: "Permet de retirer un utilisateur d'un événement. L'utilisateur doit être authentifié pour effectuer cette action.",
    )]
    #[OA\Parameter(
        name: "eventId",
        in: "path",
        required: true,
        description: "ID de l'événement duquel vous souhaitez retirer un participant.",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "L'utilisateur a été retiré avec succès de l'événement."
    )]
    #[OA\Response(
        response: 404,
        description: "L'événement spécifié n'a pas été trouvé."
    )]
    #[OA\Response(
        response: 401,
        description: "L'utilisateur n'est pas connecté."
    )]
    
    public function removeParticipant(int $eventId): JsonResponse
    {
        $event = $this->repository->find($eventId);
        $currentUser = $this->getUser();
    
        $errorMessage = null;
    
        if (!$event || !$currentUser) {
            $errorMessage = 'Event not found or User not authenticated';
            $statusCode = Response::HTTP_NOT_FOUND;
        }
        elseif ($this->blacklistService->isUserBlacklisted($event, $currentUser)) {
            $errorMessage = 'User is blacklisted from this event';
            $statusCode = Response::HTTP_FORBIDDEN;
        }
        else {
            $listParticipant = $this->findListParticipantForUser($event, $currentUser);
    
            if (!$listParticipant) {
                $errorMessage = 'Participant not found in this event';
                $statusCode = Response::HTTP_NOT_FOUND;
            }
            else {
                $listParticipant->removeParticipant($currentUser);
                $this->manager->flush();
                return new JsonResponse(['message' => 'User successfully removed from the event'], Response::HTTP_OK);
            }
        }
    
        return new JsonResponse(['error' => $errorMessage], $statusCode);
    }
    
    private function findListParticipantForUser(Event $event, User $currentUser): ?ListParticipant
    {
        foreach ($event->getListParticipants() as $lp) {
            if ($lp->getParticipants()->contains($currentUser)) {
                return $lp;
            }
        }
    
        return null;
    }

    #[Route('/events/{id}/participants', name: 'event_participants', methods: 'GET')]
    #[OA\Get(
        path: "/api/events/{id}/participants",
        summary: "Récupérer la liste des participants d'un évènement",
        tags: ["Participants"],
        description: "Permet de récupérer la liste des participants inscrits à un évènement. Si l'évènement n'existe pas ou n'a pas de participants, une erreur sera retournée.",
    )]
    #[OA\Parameter(
        name: "id",
        in: "path",
        required: true,
        description: "ID de l'événement dont vous voulez obtenir les participants.",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "La liste des participants a été récupérée avec succès.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "participants",
                        type: "string",
                        description: "Liste des participants au format JSON"
                    )]
            ))
    )]
    #[OA\Response(
        response: 404,
        description: "L'événement spécifié n'a pas été trouvé ou il n'y a pas de participants pour cet événement."
    )]

    public function getListParticipants(int $id, EventRepository $eventRepository): JsonResponse
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], Response::HTTP_NOT_FOUND);
        }
        $listParticipants = $event->getListParticipants();
        if ($listParticipants->isEmpty()) {
            return new JsonResponse(['error' => 'No participants found for this event'], Response::HTTP_NOT_FOUND);
        }

        $responseData = [];

        foreach ($listParticipants as $listParticipant) {
            $participants = $listParticipant->getParticipants();
    
            foreach ($participants as $participant) {
                $responseData[] = [
                    'id' => $participant->getId(),
                    'username' => $participant->getUsername(),
                ];
            }
        }
    
        return new JsonResponse($responseData, Response::HTTP_OK);
    }


    #[Route('/my-events', name: 'user_events', methods: ['GET'])]
    #[OA\Get(
        path: "/api/my-events",
        summary: "Récupérer les événements de l'utilisateur connecté",
        tags: ["Event Utilisateur"],
        description: "Permet de récupérer la liste des événements auxquels l'utilisateur actuellement connecté participe. Si l'utilisateur n'est pas authentifié, ou si aucun événement n'est trouvé pour cet utilisateur, une erreur sera retournée.",
    )]
    #[OA\Response(
        response: 200,
        description: "La liste des événements de l'utilisateur a été récupérée avec succès.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties:[
                        new OA\Property(property: "id", type: "integer", description: "ID de l'évent"),
                        new OA\Property(property: "title", type: "string", description: "Titre de l'évent"),
                        new OA\Property(property: "description", type: "string", description: "Description de l'événement"),
                        new OA\Property(property: "game", type:"string", example:"Tetris"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time", description: "Date de création de l'évent"),
                        new OA\Property(property: "started", type:"bool", example:"true/false")
                        
                    ])
            )
        )
    ]
    #[OA\Response(
        response: 401,
        description: "L'utilisateur n'est pas authentifié."
    )]
    #[OA\Response(
        response: 404,
        description: "Aucun événement trouvé pour l'utilisateur."
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur interne du serveur (par exemple, problème de récupération des données de l'utilisateur)."
    )]
    
    public function getUserEvents(EntityManagerInterface $manager, SerializerInterface $serializer): JsonResponse
    {
        $responseData = null;
        $statusCode = Response::HTTP_OK;
    
        $currentUser = $this->getUser();
        if (!$currentUser) {
            $responseData = ['error' => 'User not authenticated'];
            $statusCode = Response::HTTP_UNAUTHORIZED;
        } elseif (!$currentUser instanceof User) {
            $responseData = ['error' => 'Invalid user instance'];
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        } else {
            $userId = $currentUser->getId();
            if (!$userId) {
                $responseData = ['error' => 'User ID not found'];
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $qb = $manager->createQueryBuilder();
                $qb->select('DISTINCT e')
                   ->from(Event::class, 'e')
                   ->join('e.listParticipants', 'lp')
                   ->join('lp.participants', 'p')
                   ->where('p.id = :userId')
                   ->andWhere('e.dateTimeEnd >= :currentDate')
                   ->setParameter('userId', $userId)
                   ->setParameter('currentDate', new \DateTime())
                   ->orderBy('e.createdAt', 'DESC');
    
                $events = $qb->getQuery()->getResult();
    
                if (empty($events)) {
                    $responseData = ['error' => 'No event found for user'];
                    $statusCode = Response::HTTP_NOT_FOUND;
                } else {
                    $responseData = $serializer->serialize($events, 'json', ['groups' => 'user_events']);
                }
            }
        }
    
        return new JsonResponse($responseData, $statusCode, [], $statusCode === Response::HTTP_OK);
    }

    #[Route('/my-created-events', name: 'user_created_events', methods: ['GET'])]
    #[IsGranted('ROLE_ORGANISATEUR')]
    #[OA\Get(
        path: "/api/my-created-events",
        summary: "Récupérer les événements créés en cours et terminé par l'utilisateur connecté",
        tags: ["Event Utilisateur"],
        description: "Permet de récupérer la liste des événements créés par l'utilisateur actuellement connecté. Si l'utilisateur n'est pas authentifié, ou si aucun événement n'a été créé par cet utilisateur, une erreur sera retournée.",
    )]
    #[OA\Response(
        response: 200,
        description: "La liste des événements créés par l'utilisateur a été récupérée avec succès.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                    properties: [
                        new OA\Property(property: "id", type: "integer", description: "ID de l'événement"),
                        new OA\Property(property: "title", type: "string", description: "Titre de l'événement"),
                        new OA\Property(property: "description", type: "string", description: "Description de l'événement"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time", description: "Date de création de l'événement"),
                        new OA\Property(property: "createdBy", type: "string", description: "Identifiant de l'utilisateur qui a créé l'événement"),
                        new OA\Property(property: "game", type:"string", example:"Tetris"),
                        new OA\Property(property: "started", type:"bool", example:"true/false")
                    ]
                )
        )
    )]
    #[OA\Response(
        response: 401,
        description: "L'utilisateur n'est pas authentifié ou n'a pas fourni de jeton valide."
    )]
    #[OA\Response(
        response: 404,
        description: "Aucun événement trouvé pour l'utilisateur."
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur interne du serveur (par exemple, problème de récupération des données de l'utilisateur)."
    )]
    public function getEventsCreatedBy(EntityManagerInterface $manager, SerializerInterface $serializer): JsonResponse
    {
        $responseData = null;
        $statusCode = Response::HTTP_OK;
    
        $currentUser = $this->getUser();
        if (!$currentUser) {
            $responseData = ['error' => 'User not authenticated'];
            $statusCode = Response::HTTP_UNAUTHORIZED;
        } elseif (!$currentUser instanceof User) {
            $responseData = ['error' => 'Invalid user instance'];
            $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        } else {
            $currentUserIdentifier = $currentUser->getUserIdentifier();
            if (!$currentUserIdentifier) {
                $responseData = ['error' => 'User identifier not found'];
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            } else {
                $qb = $manager->createQueryBuilder();
                $qb->select('e')
                   ->from(Event::class, 'e')
                   ->where('e.createdBy = :createdBy')
                   ->setParameter('createdBy', $currentUserIdentifier)
                   ->orderBy('e.createdAt', 'DESC');
    
                $events = $qb->getQuery()->getResult();
    
                if (empty($events)) {
                    $responseData = ['error' => 'No events found for user'];
                    $statusCode = Response::HTTP_NOT_FOUND;
                } else {
                    $responseData = $serializer->serialize($events, 'json', ['groups' => 'user_events']);
                }
            }
        }
        return new JsonResponse($responseData, $statusCode, [], $statusCode === Response::HTTP_OK);
    }

    #[Route('/my-created-events-participants', name: 'my_created_events_participants', methods: ['GET'])]
    #[IsGranted('ROLE_ORGANISATEUR')]
    #[OA\Get(
        path: "/api/my-created-events-participants",
        summary: "Récupérer les événements créés par l'utilisateur et leurs participants",
        tags: ["Event Utilisateur"],
        description: "Permet de récupérer la liste des événements créés par l'utilisateur connecté, ainsi que les participants à ces événements et la liste noire associée (le cas échéant). Si l'utilisateur n'est pas authentifié ou aucun événement n'est trouvé, une erreur sera retournée.",
    )]
    #[OA\Response(
        response: 200,
        description: "La liste des événements créés par l'utilisateur avec leurs participants et les détails de la liste noire a été récupérée avec succès.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(property: "id", type: "integer", description: "ID de l'événement"),
                    new OA\Property(property: "title", type: "string", description: "Titre de l'événement"),
                    new OA\Property(property: "createdAt", type: "string", format: "date-time", description: "Date de création de l'événement"),
                    new OA\Property(property: "createdBy", type: "string", description: "Identifiant de l'utilisateur qui a créé l'événement"),
                    new OA\Property(property:"game", type:"string", example:"Tetris"),
                    new OA\Property(property: "started", type:"bool", example:"true/false"),
                    new OA\Property(
                        property: "participants",
                        type: "object",
                        description: "Liste des participants à l'événement",
                        properties: [
                            new OA\Property(property: "id", type: "integer", description: "ID du participant"),
                            new OA\Property(property: "username", type: "string", description: "Nom d'utilisateur du participant"),
                        ]
                    ),
                    new OA\Property(
                        property: "blacklist",
                        type: "object",
                        description: "Liste noire des utilisateurs bannis de l'événement",
                        properties: [
                            new OA\Property(property: "id", type: "integer", description: "ID de l'utilisateur en blacklist"),
                            new OA\Property(property: "username", type: "string", description: "Nom d'utilisateur de l'utilisateur en blacklist"),
                        ]),
                ]))
    )]
    #[OA\Response(
        response: 401,
        description: "L'utilisateur n'est pas authentifié ou n'a pas fourni de jeton valide."
    )]
    #[OA\Response(
        response: 404,
        description: "Aucun événement trouvé pour l'utilisateur ou l'utilisateur ne participe à aucun événement."
    )]
    #[OA\Response(
        response: 500,
        description: "Erreur interne du serveur (par exemple, problème avec la récupération des données de l'utilisateur ou des événements)."
    )]
    public function getMyEventParticipants(EntityManagerInterface $manager, SerializerInterface $serializer): JsonResponse
    {
        $currentUser = $this->getUser();
    
        if (!$currentUser || !$currentUser instanceof User) {
            return new JsonResponse(['error' => 'User not authenticated or invalid'], Response::HTTP_UNAUTHORIZED);
        }
    
        $currentUserIdentifier = $currentUser->getUserIdentifier();
    
        $qb = $manager->createQueryBuilder();
        $qb->select('e', 'lp', 'p', 'b')
           ->from(Event::class, 'e')
           ->leftJoin('e.listParticipants', 'lp')
           ->leftJoin('lp.participants', 'p')
           ->leftJoin('e.blacklist', 'b')
           ->where('e.createdBy = :createdBy')
           ->andWhere('e.dateTimeEnd >= :currentDate')
           ->setParameter('createdBy', $currentUserIdentifier)
           ->setParameter('currentDate', new \DateTime())
           ->orderBy('e.createdAt', 'DESC');
    
        $events = $qb->getQuery()->getResult();
    
        if (empty($events)) {
            return new JsonResponse(['error' => 'No events found for user'], Response::HTTP_NOT_FOUND);
        }
    
        $responseData = $serializer->serialize($events, 'json', ['groups' => ['participant_details', 'blacklist_details']]);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/remove-participant/{eventId}/{participantId}', name: 'remove_participant', methods: ['DELETE'])]
    #[IsGranted('ROLE_ORGANISATEUR')]
    #[OA\Delete(
        path: "/api/remove-participant/{eventId}/{participantId}",
        summary: "Retirer un participant d'un événement et l'ajouter à la blacklist",
        tags: ["Participants"],
        description: "Cette route permet de retirer un participant d'un événement et de l'ajouter à la blacklist. Seul l'utilisateur qui a créé l'événement peut exécuter cette action.",
    )]
    #[OA\Parameter(
        name: "eventId",
        in: "path",
        required: true,
        description: "ID de l'événement duquel vous souhaitez retirer un participant.",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Parameter(
        name: "participantId",
        in: "path",
        required: true,
        description: "ID du participant que vous souhaitez retirer de l'événement.",
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 200,
        description: "Le participant a été retiré avec succès et ajouté à la blacklist.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "success",
                        type: "string",
                        description: "Message indiquant que le participant a été retiré et blacklisté"
                    ),]))
    )]
    #[OA\Response(
        response: 400,
        description: "Le participant n'a pas pu être retiré car il n'existe pas ou n'est pas associé à l'événement.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "error",
                        type: "string",
                        description: "Message d'erreur décrivant la raison de l'échec"
                    ),
                ]))
    )]
    #[OA\Response(
        response: 401,
        description: "L'utilisateur n'est pas authentifié.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "error",
                        type: "string",
                        description: "Message d'erreur indiquant que l'utilisateur n'est pas authentifié"
                    ),
                ]))
    )]
    #[OA\Response(
        response: 403,
        description: "L'utilisateur n'a pas la permission de modifier cet événement.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "error",
                        type: "string",
                        description: "Message d'erreur indiquant que l'utilisateur n'a pas la permission"
                    ),
                ]))
    )]
    #[OA\Response(
        response: 404,
        description: "L'événement spécifié n'a pas été trouvé.",
        content: new OA\MediaType(
            mediaType: "application/json",
            schema: new OA\Schema(
                type: "object",
                properties: [
                    new OA\Property(
                        property: "error",
                        type: "string",
                        description: "Message d'erreur indiquant que l'événement n'a pas été trouvé"
                    ),
                ]))
    )]
    public function removeEventParticipant(
        int $eventId,
        int $participantId,
        EntityManagerInterface $manager
    ): JsonResponse {
        $responseData = [];
        $statusCode = Response::HTTP_OK;
    
        $currentUser = $this->getUser();
        if (!$currentUser || !$currentUser instanceof User) {
            $responseData = ['error' => 'User not authenticated or invalid'];
            $statusCode = Response::HTTP_UNAUTHORIZED;
            return new JsonResponse($responseData, $statusCode);
        }
    
        $currentUserIdentifier = $currentUser->getUserIdentifier();
        $event = $manager->getRepository(Event::class)->find($eventId);
    
        if (!$event) {
            $responseData = ['error' => 'Event not found'];
            $statusCode = Response::HTTP_NOT_FOUND;
        } elseif ($event->getCreatedBy() !== $currentUserIdentifier) {
            $responseData = ['error' => 'You do not have permission to modify this event'];
            $statusCode = Response::HTTP_FORBIDDEN;
        } else {
            $participant = $manager->getRepository(User::class)->find($participantId);
    
            if (!$participant) {
                $responseData = ['error' => 'Participant not found'];
                $statusCode = Response::HTTP_BAD_REQUEST;
            } else {
                $listParticipant = $event->getListParticipants()->filter(function ($list) use ($participant) {
                    return $list->getParticipants()->contains($participant);
                })->first();
    
                if (!$listParticipant) {
                    $responseData = ['error' => 'Participant not found or not part of the event'];
                    $statusCode = Response::HTTP_BAD_REQUEST;
                } else {
                    $listParticipant->removeParticipant($participant);
                    $manager->persist($listParticipant);
    
                    $this->blacklistService->addToBlacklist($event, $participant);
    
                    $manager->flush();
    
                    $responseData = ['success' => 'Participant removed and blacklisted from the event'];
                }
            }
        }
    
        return new JsonResponse($responseData, $statusCode);
    }

    #[Route('/all/not-visible', name: 'show_all_not_visible', methods: 'GET')]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: "/api/all/not-visible",
        summary: "Afficher touts les events en cours et terminé non validés",
        tags: ["Admin"])]
    #[OA\Response(
        response:200,
        description:"Evènement afficher avec succés.",
        content: new OA\JsonContent(
            type:"object",
            properties : [new OA\Property(property:"title", type:"string", example:"Nouveau nom de l'évènement"),
            new OA\Property(property:"description", type:"string", example:"Nouvelle description"),
            new OA\Property(property:"players", type:"integer", example:"100"),
            new OA\Property(property:"dateTimeStart", type:"dateTime", example:"2025-12-01T18:00:00"),
            new OA\Property(property:"dateTimeEnd", type:"dateTime", example:"2025-12-01T19:00:00"),
            new OA\Property(property:"game", type:"string", example:"Tetris"),
            new OA\Property(property: "createdBy", type: "string", description: "Identifiant de l'utilisateur qui a créé l'événement"),
            new OA\Property(property:"image", type:"string", example:"Lien de l'image, non obligatoire"),
            new OA\Property(property:"visibility", type:"bool", example:"false"),
            new OA\Property(property: "started", type:"bool", example:"true/false")])
    )]
    
    #[OA\Response(
        response:404,
        description:"Aucun évènement n'as étais trouvé.",
    )]
    public function showAllNotVisible(): JsonResponse
    {
        $events = $this->repository->findAll();
        $visibleEvents = array_filter($events, fn($event) => $event->isVisibility()=== false);
        if ($visibleEvents) {
            $responseData = $this->serializer->serialize($visibleEvents, 'json', ['groups' => 'user_events']);
            
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
    
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    
    #[Route('/all/{id}', name: 'edit_all', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        path: '/api/all/{id}',
        summary: "Valider ou modifier la visibilité d'un évènement",
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID de l\'évènement à modifier',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données de l'évènement à mettre à jour",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: "visibility", type: "bool", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: 'Evènement modifié avec succès'
            ),
            new OA\Response(
                response: 404,
                description: 'Evènement non trouvé'
            )
        ]
    )]
    public function editVisibility(int $id, Request $request): JsonResponse
    {
        $event = $this->repository->find($id);
    
        if (!$event) {
            $response = [
                'status' => Response::HTTP_NOT_FOUND,
                'data' => ['message' => 'Evènement non trouvé']
            ];
        } else {
            $data = json_decode($request->getContent(), true);
    
            if (isset($data['visibility']) && is_bool($data['visibility'])) {
                $event->setVisibility($data['visibility']);
                $this->manager->flush();
    
                $response = ['status' => Response::HTTP_NO_CONTENT, 'data' => null];
            } elseif (!isset($data['visibility'])) {
                $response = [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'data' => ['message' => 'Paramètre de visibilité manquant']
                ];
            } else {
                $response = [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'data' => ['message' => 'Paramètre de visibilité invalide, attendu un booléen']
                ];
            }
        }
    
        return new JsonResponse($response['data'], $response['status']);
    }
    
    #[Route('/my-created-events/{id}', name: 'user_created_events_id', methods: ['PUT'])]
    #[IsGranted('ROLE_ORGANISATEUR')]
    #[OA\Put(
        path: '/api/my-created-events/{id}',
        summary: "Valider ou modifier de lancement d'un évènement",
        tags: ["Event Utilisateur"],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID de l\'évènement à modifier',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Nouvelles données de l'évènement à mettre à jour",
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(property: "started", type: "bool", example: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: 'Evènement modifié avec succès'
            ),
            new OA\Response(
                response: 403,
                description: 'Permission refusée: seul le créateur de l\'événement peut le modifier'
            ),
            new OA\Response(
                response: 404,
                description: 'Evènement non trouvé'
            )
        ]
    )]
    public function editStarted(int $id, Request $request): JsonResponse
    {
        $event = $this->repository->find($id);
        $currentUser = $this->getUser();
    
        if (!$event) {
            return new JsonResponse(['message' => 'Evènement non trouvé'], Response::HTTP_NOT_FOUND);
        }
        if (!$currentUser || $event->getCreatedBy() !== $currentUser->getUserIdentifier()) {
            return new JsonResponse(['message' => 'Permission refusée: seul le créateur de l\'événement peut le modifier'], Response::HTTP_FORBIDDEN);
        }
        if ($event->getDateTimeEnd() < new \DateTime()) {
            return new JsonResponse(['message' => 'Impossible de modifier le lancement d\'un événement dont la date de fin est déjà passée'], Response::HTTP_BAD_REQUEST);
        }
    
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['started'])) {
            return new JsonResponse(['message' => 'Paramètre de lancement manquant'], Response::HTTP_BAD_REQUEST);
        }
        if (!is_bool($data['started'])) {
            return new JsonResponse(['message' => 'Paramètre de lancement invalide, attendu un booléen'], Response::HTTP_BAD_REQUEST);
        }
        if ($data['started'] === true && $event->isVisibility() === false) {
            return new JsonResponse(['message' => "Impossible de lancer un événement qui n'a pas été validé par un administrateur"], Response::HTTP_BAD_REQUEST);
        }
    
        $event->setStarted($data['started']);
        $this->manager->flush();
    
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    
}
