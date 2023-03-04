<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ExplainController extends AbstractController
{
    /**
     * @Route("/explain", name="explain")
     */
    public function index()
    {
        return $this->render('explain/index.html.twig', [
            'controller_name' => 'ExplainController',
        ]);
    }
}