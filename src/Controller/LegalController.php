<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route('/privacy-policy', name: 'app_privacy_policy')]
    public function privacyPolicy(): Response
    {
        return $this->render('legal/privacy-policy.html.twig');
    }

    #[Route('/cookie-policy', name: 'app_cookie')]
    public function cookiePolicy(): Response
    {
        return $this->render('legal/cookie-policy.html.twig');
    }

    #[Route('/terms', name: 'app_terms')]
    public function terms(): Response
    {
        return $this->render('legal/terms.html.twig');
    }

    #[Route('/legal-notice', name: 'app_legal_notice')]
    public function legalNotice(): Response
    {
        return $this->render('legal/legal-notice.html.twig');
    }
}
