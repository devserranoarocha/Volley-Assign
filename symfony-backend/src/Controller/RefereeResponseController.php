<?php

namespace App\Controller;

use App\Entity\RefereeResponse;
use App\Repository\AvailabilityRequestPeriodRepository;
use App\Repository\RefereeRepository;
use App\Repository\RefereeResponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/referee-responses')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class RefereeResponseController extends AbstractController
{
    #[Route('', name: 'api_referee_response_save', methods: ['POST'])]
    public function saveResponses(
        Request $request,
        RefereeRepository $refereeRepository,
        AvailabilityRequestPeriodRepository $periodRepository,
        RefereeResponseRepository $responseRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $referee = $refereeRepository->findOneBy(['user' => $user]);

        if (!$referee) {
            return new JsonResponse(
                ['error' => 'No se encontró un perfil de árbitro asociado al usuario autenticado.'],
                Response::HTTP_NOT_FOUND
            );
        }

        // Valida que el body no esté vacío y que sea JSON válido
        $content = $request->getContent();
        if (empty($content)) {
            return new JsonResponse(
                ['error' => 'El cuerpo de la petición está vacío.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(
                ['error' => 'El formato JSON enviado no es válido: ' . json_last_error_msg()],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!isset($data['responses']) || !is_array($data['responses'])) {
            return new JsonResponse(
                ['error' => 'Payload inválido. Se espera un objeto con el array "responses".'],
                Response::HTTP_BAD_REQUEST
            );
        }

        foreach ($data['responses'] as $item) {
            if (!isset($item['period_id']) || !array_key_exists('is_available', $item)) {
                continue;
            }

            $period = $periodRepository->find($item['period_id']);
            if (!$period) {
                continue;
            }

            $responseEntity = $responseRepository->findOneBy([
                'referee' => $referee,
                'period'  => $period,
            ]);

            if (!$responseEntity) {
                $responseEntity = new RefereeResponse();
                $responseEntity->setReferee($referee);
                $responseEntity->setPeriod($period);
            }

            $responseEntity->setIsAvailable((bool) $item['is_available']);
            $responseEntity->setDisplacement($item['displacement'] ?? null);

            $em->persist($responseEntity);
        }

        $em->flush();

        return new JsonResponse([
            'message' => 'Respuestas registradas correctamente.'
        ], Response::HTTP_OK);
    }

    #[Route('/my-responses/request/{requestId}', name: 'api_referee_my_responses', methods: ['GET'])]
    public function getMyResponses(
        int $requestId,
        RefereeRepository $refereeRepository,
        RefereeResponseRepository $responseRepository
    ): JsonResponse {
        $user = $this->getUser();
        $referee = $refereeRepository->findOneBy(['user' => $user]);

        if (!$referee) {
            return new JsonResponse(
                ['error' => 'No se encontró un perfil de árbitro asociado.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $responses = $responseRepository->createQueryBuilder('rr')
            ->join('rr.period', 'p')
            ->where('rr.referee = :referee')
            ->andWhere('p.availabilityRequest = :requestId')
            ->setParameter('referee', $referee)
            ->setParameter('requestId', $requestId)
            ->getQuery()
            ->getResult();

        $data = array_map(function (RefereeResponse $response) {
            return [
                'id'           => $response->getId(),
                'period_id'    => $response->getPeriod()?->getId(),
                'is_available' => $response->isIsAvailable(),
                'displacement' => $response->getDisplacement(),
            ];
        }, $responses);

        return new JsonResponse($data, Response::HTTP_OK);
    }
}