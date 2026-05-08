<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Repository\UserRepository;
use App\Repository\GalleryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MessageRepository;
use App\Repository\ReponseRepository;

class MemberController extends AbstractController
{
    #[Route('/member', name: 'member_index')]
    public function index(UserRepository $userRepo, GalleryRepository $galleryrepo, MessageRepository $messageRepo, ReponseRepository $reponseRepo): Response {
        $user = $this->getUser();

        if ($user->getActivationToken() !== null) {
            $this->addFlash('danger', 'Veuillez activer votre compte via le lien reçu par email.');
            return $this->redirectToRoute('security_logout');
        }

        $perso = $userRepo->findBy(['id' => $user->getId()]);
        $gallery = $galleryrepo->findBy(['user' => $user]);
        $nbMessages = count($messageRepo->findBy(['destinataire' => $user]));
        $nbReponses = count($reponseRepo->findBy(['destinataire' => $user]));
        $total = $nbMessages + $nbReponses;

        return $this->render('member/index.html.twig', [
            'perso' => $perso,
            'gallery' => $gallery,
            'nbMessages' => $total
        ]);
    }
}
