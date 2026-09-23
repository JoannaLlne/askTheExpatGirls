<?php

namespace App\Controller;

use App\Dto\ServiceFilterDto;
use App\Form\ServiceFilterType;
use App\Repository\ServiceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController extends AbstractController
{
    #[Route('/service', name: 'main_service')]
    public function index(Request $request, ServiceRepository $serviceRepository): Response
    {
        $filterDto = new ServiceFilterDto();
        $form= $this->createForm(ServiceFilterType::class, $filterDto);
        $form->handleRequest($request);

        $services = $serviceRepository->findByFilters($filterDto);

        return $this->render('service/services.html.twig', [
            'form' => $form->createView(),
            'services' => $services,
        ]);
    }

    #[Route('/service/works', name: 'how_it_works')]
    public function contact(): Response
    {
        return $this->render('service/how_it_works.html.twig');
    }
}
