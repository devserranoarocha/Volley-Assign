<?php 

namespace App\Controller;

use App\Entity\AvailabilityRequest;
use App\Entity\AvailabilityRequestPeriod;
use App\Repository\AvailabilityRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/availability-requests')]
class AvailabilityRequestController extends AbstractController
{
    // 1. POST: Crear una petición con sus períodos (Flujo del Gestor)
    #[Route('', name: 'api_availability_request_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validación básica
        if (!$data || !isset($data['title']) || !isset($data['startDate']) || !isset($data['endDate'])) {
            return new JsonResponse(['error' => 'Datos incompletos o JSON inválido.'], Response::HTTP_BAD_REQUEST);
        }

        // Crear la entidad principal
        $availabilityRequest = new AvailabilityRequest();
        $availabilityRequest->setTitle($data['title']);
        $availabilityRequest->setStartDate(new \DateTime($data['startDate']));
        $availabilityRequest->setEndDate(new \DateTime($data['endDate']));
        $availabilityRequest->setStatus($data['status'] ?? 'Abierta');
        $availabilityRequest->setDescription($data['description'] ?? null);

        // Procesar y asociar los períodos si vienen en la petición
        if (isset($data['periods']) && is_array($data['periods'])) {
            foreach ($data['periods'] as $periodData) {
                if (isset($periodData['date']) && isset($periodData['period'])) {
                    $period = new AvailabilityRequestPeriod();
                    $period->setDate(new \DateTime($periodData['date']));
                    $period->setPeriod($periodData['period']); // Ej: "Mañana", "Tarde"
                    
                    // Gracias al cascade y orphanRemoval, esto asocia y prepara el guardado automáticamente
                    $availabilityRequest->addPeriod($period);
                }
            }
        }

        $em->persist($availabilityRequest);
        $em->flush();

        return new JsonResponse([
            'message' => 'Petición de disponibilidad creada con éxito.',
            'id' => $availabilityRequest->getId()
        ], Response::HTTP_CREATED);
    }

    // 2. GET: Listar todas las peticiones (Útil para el Gestor y para que los Árbitros vean qué hay activo)
    #[Route('', name: 'api_availability_request_list', methods: ['GET'])]
    public function list(AvailabilityRequestRepository $repository): JsonResponse
    {
        $requests = $repository->findAll();
        $responseData = [];

        foreach ($requests as $req) {
            $periodsData = [];
            foreach ($req->getPeriods() as $period) {
                $periodsData[] = [
                    'id' => $period->getId(),
                    'date' => $period->getDate()->format('Y-m-d'),
                    'period' => $period->getPeriod(),
                ];
            }

            $responseData[] = [
                'id' => $req->getId(),
                'title' => $req->getTitle(),
                'startDate' => $req->getStartDate()->format('Y-m-d'),
                'endDate' => $req->getEndDate()->format('Y-m-d'),
                'status' => $req->getStatus(),
                'description' => $req->getDescription(),
                'periods' => $periodsData
            ];
        }

        return new JsonResponse($responseData, Response::HTTP_OK);
    }
}