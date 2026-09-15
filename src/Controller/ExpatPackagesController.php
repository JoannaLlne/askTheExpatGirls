<?php

namespace App\Controller;

use App\Repository\ExpatPackagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExpatPackagesController extends AbstractController
{
    #[Route('/expat/packages', name: 'expat_packages')]
    public function index(ExpatPackagesRepository $expatPackagesRepository): Response
    {
        return $this->render('expat_packages/packages.html.twig', [
            'packages' => $expatPackagesRepository->findAll(),
        ]);
    }
}
