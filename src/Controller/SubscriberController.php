<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriberController extends AbstractController
{
    /**
     * @Route("/subscriber", name="subscriber_index")
     */
    public function index(): Response
    {
        return $this->render('subscriber/index.html.twig', [
            'controller_name' => 'SubscriberController',
        ]);
    }
}
