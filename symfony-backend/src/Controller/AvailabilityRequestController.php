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

    // 3. GET: Obtener una única petición por su ID
    #[Route('/{id}', name: 'api_availability_request_show', methods: ['GET'])]
    public function show(?AvailabilityRequest $availabilityRequest): JsonResponse
    {
        // El ParamConverter de Symfony busca automáticamente el ID en la BD
        if (!$availabilityRequest) {
            return new JsonResponse(['error' => 'Petición de disponibilidad no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $periodsData = [];
        foreach ($availabilityRequest->getPeriods() as $period) {
            $periodsData[] = [
                'id' => $period->getId(),
                'date' => $period->getDate()->format('Y-m-d'),
                'period' => $period->getPeriod(),
            ];
        }

        return new JsonResponse([
            'id' => $availabilityRequest->getId(),
            'title' => $availabilityRequest->getTitle(),
            'startDate' => $availabilityRequest->getStartDate()->format('Y-m-d'),
            'endDate' => $availabilityRequest->getEndDate()->format('Y-m-d'),
            'status' => $availabilityRequest->getStatus(),
            'description' => $availabilityRequest->getDescription(),
            'periods' => $periodsData
        ], Response::HTTP_OK);
    }

    // 4. PUT: Actualizar una petición y sincronizar sus períodos
    #[Route('/{id}', name: 'api_availability_request_update', methods: ['PUT'])]
    public function update(Request $request, ?AvailabilityRequest $availabilityRequest, EntityManagerInterface $em): JsonResponse
    {
        if (!$availabilityRequest) {
            return new JsonResponse(['error' => 'Petición de disponibilidad no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'JSON inválido.'], Response::HTTP_BAD_REQUEST);
        }

        // Actualizar datos de la petición principal si vienen en el JSON
        if (isset($data['title'])) $availabilityRequest->setTitle($data['title']);
        if (isset($data['startDate'])) $availabilityRequest->setStartDate(new \DateTime($data['startDate']));
        if (isset($data['endDate'])) $availabilityRequest->setEndDate(new \DateTime($data['endDate']));
        if (isset($data['status'])) $availabilityRequest->setStatus($data['status']);
        if (isset($data['description'])) $availabilityRequest->setDescription($data['description']);

        // Sincronización de períodos (si se envía la clave 'periods')
        if (isset($data['periods']) && is_array($data['periods'])) {
            // 1. Limpiar los períodos actuales que no estén en la nueva lista
            $keepPeriodIds = [];
            foreach ($data['periods'] as $pData) {
                if (isset($pData['id'])) {
                    $keepPeriodIds[] = $pData['id'];
                }
            }

            foreach ($availabilityRequest->getPeriods() as $currentPeriod) {
                if (!in_array($currentPeriod->getId(), $keepPeriodIds)) {
                    // Al quitarlo de la colección con orphanRemoval=true, Doctrine hará un DELETE en la BD
                    $availabilityRequest->removePeriod($currentPeriod);
                }
            }

            // 2. Añadir nuevos o actualizar existentes
            foreach ($data['periods'] as $pData) {
                if (!isset($pData['date']) || !isset($pData['period'])) {
                    continue; 
                }

                if (isset($pData['id'])) {
                    // Si viene ID, es una actualización de un período existente
                    foreach ($availabilityRequest->getPeriods() as $currentPeriod) {
                        if ($currentPeriod->getId() === $pData['id']) {
                            $currentPeriod->setDate(new \DateTime($pData['date']));
                            $currentPeriod->setPeriod($pData['period']);
                        }
                    }
                } else {
                    // Si no viene ID, es un período totalmente nuevo que añade el gestor
                    $newPeriod = new AvailabilityRequestPeriod();
                    $newPeriod->setDate(new \DateTime($pData['date']));
                    $newPeriod->setPeriod($pData['period']);
                    $availabilityRequest->addPeriod($newPeriod);
                }
            }
        }

        $em->flush();

        return new JsonResponse(['message' => 'Petición de disponibilidad actualizada con éxito.'], Response::HTTP_OK);
    }

    // 5. DELETE: Eliminar una petición por completo
    #[Route('/{id}', name: 'api_availability_request_delete', methods: ['DELETE'])]
    public function delete(?AvailabilityRequest $availabilityRequest, EntityManagerInterface $em): JsonResponse
    {
        if (!$availabilityRequest) {
            return new JsonResponse(['error' => 'Petición de disponibilidad no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        // Debido a cascade: ['remove'] u onDelete="CASCADE", se borrarán también sus períodos en cadena
        $em->remove($availabilityRequest);
        $em->flush();

        return new JsonResponse(['message' => 'Petición de disponibilidad eliminada correctamente.'], Response::HTTP_OK);
    }
}