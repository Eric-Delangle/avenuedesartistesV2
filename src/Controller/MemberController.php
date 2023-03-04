<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Repository\UserRepository;
use App\Repository\GalleryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends AbstractController
{
    /**
     * @Route("/member", name="member_index")
     */
    public function index(UserRepository $userRepo, GalleryRepository $galleryrepo): Response
    {

        // verification du compte si il est activé
       // $activToken = $this->getUser()->getActivationToken();
/*
        if ($activToken != null) {

            return $this->render('emails/activationFailed.html.twig', [

                'token' => $activToken
            ]);
        }
*/
        // si le compte est bien activé

        $id = $this->getUser()->getUserIdentifier();
        $user = $this->getUser();
        $perso = $userRepo->findBy(['id' => $id]);
        $gallery = $galleryrepo->findBy(['user' => $user]);

        return $this->render('member/index.html.twig', [
            'perso' => $perso,
           // 'token' => $activToken,
            'gallery' => $gallery
        ]);
    }
}