<?php

namespace App\Controller;

use App\Entity\Teams;
use App\Repository\TeamsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/teams', name: 'api_teams_')]
class TeamsController extends AbstractController
{
    // 1. LISTAR TODOS LOS EQUIPOS (GET /api/teams)
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TeamsRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $teams = $repository->findAll();
        
        // El serializer de Symfony convertirá automáticamente las propiedades camelCase a JSON
        $json = $serializer->serialize($teams, 'json');

        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    // 2. OBTENER UN EQUIPO ESPECÍFICO (GET /api/teams/{id})
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id, TeamsRepository $repository, SerializerInterface $serializer): JsonResponse
    {
        $team = $repository->find($id);

        if (!$team) {
            return new JsonResponse(['error' => 'Equipo no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $json = $serializer->serialize($team, 'json');
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    // 3. CREAR UN NUEVO EQUIPO (POST /api/teams)
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            // Deserializamos directamente el JSON de entrada al objeto Teams
            /** @var Teams $team */
            $team = $serializer->deserialize($request->getContent(), Teams::class, 'json');
            
            // Validaciones básicas de negocio manuales o por asserts
            if (!$team->getName() || !$team->getTeamLevel()) {
                return new JsonResponse(['error' => 'El nombre del equipo y el nivel (teamLevel) son obligatorios.'], Response::HTTP_BAD_REQUEST);
            }

            $errors = $validator->validate($team);
            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }
                return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $entityManager->persist($team);
            $entityManager->flush();

            $json = $serializer->serialize($team, 'json');
            return new JsonResponse($json, Response::HTTP_CREATED, [], true);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'JSON malformado o inválido.'], Response::HTTP_BAD_REQUEST);
        }
    }

    // 4. ACTUALIZAR UN EQUIPO (PUT /api/teams/{id})
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        TeamsRepository $repository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $team = $repository->find($id);

        if (!$team) {
            return new JsonResponse(['error' => 'Equipo no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return new JsonResponse(['error' => 'Datos inválidos.'], Response::HTTP_BAD_REQUEST);
        }

        // Actualizamos de forma segura controlando los campos que vienen en el JSON
        if (isset($data['name'])) {
            $team->setName($data['name']);
        }
        if (isset($data['teamLevel'])) {
            $team->setTeamLevel($data['teamLevel']);
        }
        if (array_key_exists('city', $data)) {
            $team->setCity($data['city']);
        }
        if (array_key_exists('firstCoach', $data)) {
            $team->setFirstCoach($data['firstCoach']);
        }
        if (array_key_exists('secondCoach', $data)) {
            $team->setSecondCoach($data['secondCoach']);
        }
        if (array_key_exists('delegate', $data)) {
            $team->setDelegate($data['delegate']);
        }
        if (array_key_exists('doctor', $data)) {
            $team->setDoctor($data['doctor']);
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Equipo actualizado correctamente.'], Response::HTTP_OK);
    }

    // 5. ELIMINAR UN EQUIPO (DELETE /api/teams/{id})
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, TeamsRepository $repository, EntityManagerInterface $entityManager): JsonResponse
    {
        $team = $repository->find($id);

        if (!$team) {
            return new JsonResponse(['error' => 'Equipo no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($team);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Equipo eliminado correctamente.'], Response::HTTP_OK);
    }
}