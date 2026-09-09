<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController extends AbstractController
{
    #[Route('/service', name: 'main_service')]
    public function index(): Response
    {
        return $this->render('service/service.html.twig', [
            'controller_name' => 'ServiceController',
        ]);
    }
}
