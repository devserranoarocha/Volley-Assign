<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Referee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class RegisterController extends AbstractController
{
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email']) || !isset($data['password']) || !isset($data['roles'])) {
            return new JsonResponse(['error' => 'Datos insuficientes o JSON malformado.'], Response::HTTP_BAD_REQUEST);
        }

        // 1. Crear y configurar la entidad User
        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles($data['roles']);

        // Validar restricciones nativas del User (email válido, no vacío, etc.)
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Hashear la contraseña de forma segura antes de persistir
        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);

        // 2. Si el rol es de Árbitro, creamos simultáneamente su ficha técnica
        if (in_array('ROLE_REFEREE', $user->getRoles())) {
            if (!isset($data['referee_data'])) {
                return new JsonResponse(['error' => 'Faltan los datos del perfil de árbitro (referee_data).'], Response::HTTP_BAD_REQUEST);
            }

            $refereeData = $data['referee_data'];

            $referee = new Referee();
            $referee->setName($refereeData['name'] ?? null);
            $referee->setLastName($refereeData['last_name'] ?? null);
            $referee->setBaseVenueId($refereeData['base_venue_id'] ?? null);
            $referee->setReferreLevel($refereeData['referee_level'] ?? null);
            $referee->setSeasonCounter(0); // Empezamos liga a cero
            $referee->setWeekCounter(0);
            $referee->setOthers($refereeData['others'] ?? '');
            
            // Establecemos la relación OneToOne que acabamos de definir
            $referee->setUser($user);

            $entityManager->persist($referee);
        }

        // Guardamos todo de forma atómica en la base de datos
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Usuario registrado correctamente.',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles()
            ]
        ], Response::HTTP_CREATED);
    }
}