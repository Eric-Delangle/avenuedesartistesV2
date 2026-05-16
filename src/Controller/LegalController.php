<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'mentions_legales')]
    public function mentions(): Response {
        return $this->render('legal/mentions.html.twig');
    }

    #[Route('/confidentialite', name: 'confidentialite')]
    public function confidentialite(): Response {
        return $this->render('legal/confidentialite.html.twig');
    }

    #[Route('/cgu', name: 'cgu')]
    public function cgu(): Response {
        return $this->render('legal/cgu.html.twig');
    }

    #[Route('/apropos', name: 'apropos')]
    public function apropos(): Response{
        return $this->render('legal/apropos.html.twig');
    }

    #[Route('/guide', name: 'documentation')]
    public function documentation(): Response {
        return $this->render('legal/documentation.html.twig');
    }
}