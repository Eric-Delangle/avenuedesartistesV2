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
        $id = $this->getUser()->getId();
        $perso = $userRepo->findBy(['id' => $id]);

        return $this->render('member/index.html.twig', [
            'perso' => $perso,
        ]);
    }
}
