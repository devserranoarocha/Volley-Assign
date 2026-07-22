<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RefereeResponseController extends AbstractController
{
    #[Route('/referee/response', name: 'app_referee_response')]
    public function index(): Response
    {
        return $this->render('referee_response/index.html.twig', [
            'controller_name' => 'RefereeResponseController',
        ]);
    }
}
