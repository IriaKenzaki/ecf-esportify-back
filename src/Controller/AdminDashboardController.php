<?php


namespace App\Controller;

use App\Service\StatisticsService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('api', name:'app_api_')]
class AdminDashboardController extends AbstractController
{
    private $statisticsService;

    public function __construct(StatisticsService $statisticsService)
    {
        $this->statisticsService = $statisticsService;
    }

    
    #[Route("/admin/dashboard", name: "admin_dashboard", methods: "GET")]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: "/api/admin/dashboard",
        summary: "Obtenir des statistiques pour le tableau de bord admin",
        tags: ["Admin"],
        parameters: [
            new OA\Parameter(
                name: "startDate",
                in: "query",
                required: false,
                description: "Date de début (au format Y-m-d) pour filtrer les statistiques",
                schema: new OA\Schema(type: "string", format: "date", example: "2023-01-01")
            ),
            new OA\Parameter(
                name: "endDate",
                in: "query",
                required: false,
                description: "Date de fin (au format Y-m-d) pour filtrer les statistiques",
                schema: new OA\Schema(type: "string", format: "date", example: "2023-12-31")
            ),
            new OA\Parameter(
                name: "results",
                in: "query",
                required: false,
                description: "Type de résultat à retourner (eventsCreated, usersCreated, usersConnected, ou all)",
                schema: new OA\Schema(
                    type: "string",
                    enum: ["eventsCreated", "usersCreated", "usersConnected", "all"],
                    example: "all"
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Statistiques retournées avec succès",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(property: "eventsCreated", type: "integer", example: 42),
                        new OA\Property(property: "usersCreated", type: "integer", example: 150),
                        new OA\Property(property: "usersConnected", type: "integer", example: 75),
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Requête invalide (par exemple, mauvais format de date)"
            ),
            new OA\Response(
                response: 500,
                description: "Erreur interne du serveur"
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        $results = $request->query->get('results', 'all');
    
        $startDateObj = $startDate ? \DateTime::createFromFormat('Y-m-d', $startDate) : null;
        $endDateObj = $endDate ? \DateTime::createFromFormat('Y-m-d', $endDate) : null;
    
        if ($startDate && !$startDateObj || $endDate && !$endDateObj) {
            return new JsonResponse(['message' => 'Format de date invalide. Utilisez Y-m-d.'], Response::HTTP_BAD_REQUEST);
        }
    
        $responseData = [];
    
        if ($results === 'eventsCreated' || $results === 'all') {
            $responseData['eventsCreated'] = $this->statisticsService->getEventCount($startDateObj, $endDateObj);
        }
    
        if ($results === 'usersCreated' || $results === 'all') {
            $responseData['usersCreated'] = $this->statisticsService->getUserCount($startDateObj, $endDateObj);
        }
    
        if ($results === 'usersConnected' || $results === 'all') {
            $responseData['usersConnected'] = $this->statisticsService->getUserConnectedCount($startDateObj, $endDateObj);
        }
    
        return new JsonResponse($responseData);
    }

}
