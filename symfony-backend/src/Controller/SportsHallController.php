<?php

namespace App\Controller;

use App\Entity\SportsHall;
use App\Repository\SportsHallRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/sports-halls', name: 'api_sports_halls_')]
class SportsHallController extends AbstractController
{
    // 1. LISTAR TODOS LOS PABELLONES (GET /api/sports-halls)
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(SportsHallRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $sportsHalls = $repository->findAll();
        
        // Convertimos la lista de objetos en un JSON estructurado
        $json = $serializer->serialize($sportsHalls, 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    // 2. OBTENER UN PABELLÓN ESPECÍFICO (GET /api/sports-halls/{id})
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, SportsHallRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $sportsHall = $repository->find($id);

        if (!$sportsHall) {
            return new JsonResponse(['error' => 'Pabellón no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($sportsHall, 'json');
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    // 3. CREAR UN NUEVO PABELLÓN (POST /api/sports-halls)
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            // Deserializamos el JSON directamente a un objeto SportsHall
            /** @var SportsHall $sportsHall */
            $sportsHall = $serializer->deserialize($request->getContent(), SportsHall::class, 'json');
            
            // Validamos que cumpla las restricciones
            $errors = $validator->validate($sportsHall);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if (!$sportsHall->getName()) {
                return new JsonResponse(['error' => 'El nombre del pabellón es obligatorio.'], Response::HTTP_BAD_REQUEST);
            }

            $entityManager->persist($sportsHall);
            $entityManager->flush();

            $json = $serializer->serialize($sportsHall, 'json');
            return new JsonResponse($json, Response::HTTP_CREATED, [], true);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'JSON malformado o inválido.'], Response::HTTP_BAD_REQUEST);
        }
    }

    // 4. ACTUALIZAR UN PABELLÓN (PUT /api/sports-halls/{id})
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        SportsHallRepository $repository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $sportsHall = $repository->find($id);

        if (!$sportsHall) {
            return new JsonResponse(['error' => 'Pabellón no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Datos inválidos.'], Response::HTTP_BAD_REQUEST);
        }

        // Actualizamos solo los campos que vengan en la petición
        if (isset($data['name'])) {
            $sportsHall->setName($data['name']);
        }
        if (array_key_exists('shortName', $data)) {
            $sportsHall->setShortName($data['shortName']);
        }
        if (array_key_exists('address', $data)) {
            $sportsHall->setAddress($data['address']);
        }
        if (array_key_exists('location', $data)) {
            $sportsHall->setLocation($data['location']);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Pabellón actualizado correctamente.'], Response::HTTP_OK);
    }

    // 5. ELIMINAR UN PABELLÓN (DELETE /api/sports-halls/{id})
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, SportsHallRepository $repository, EntityManagerInterface $entityManager): JsonResponse
    {
        $sportsHall = $repository->find($id);

        if (!$sportsHall) {
            return new JsonResponse(['error' => 'Pabellón no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($sportsHall);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Pabellón eliminado correctamente.'], Response::HTTP_OK);
    }
}