<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends AbstractController
{
    /**
     * @Route("/member", name="member_index")
     */
    public function index(UserRepository $userRepo): Response
    {

        // verification du compte si il est activé
        $activToken = $this->getUser()->getActivationToken();

        if ($activToken != null) {

            return $this->render('emails/activationFailed.html.twig', [

                'token' => $activToken
            ]);
        }

        // si le compte est bien activé

        $id = $this->getUser()->getId();
        $perso = $userRepo->findBy(['id' => $id]);

        return $this->render('member/index.html.twig', [
            'perso' => $perso,
            'token' => $activToken
        ]);
    }
}
