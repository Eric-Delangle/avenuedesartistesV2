<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\GalleryEchangeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ShareController extends AbstractController
{
    /**
     * @Route("/share", name="share_index")
     */
    public function index(UserRepository $userRepo, GalleryEchangeRepository $galerieechange): Response
    {
        $user = $this->getUser();

        $listeArtisteEchange = $userRepo->findAll();

        $slug = $userRepo->find('slug');
        $listeGalerieUser = $galerieechange->findAll();


        foreach ($listeArtisteEchange as $artiste) {
            foreach ($listeGalerieUser as $galerie) {
                $id = $galerie->getId();
                $artiste = $galerieechange->findBy(['id' => $id]);
            }
        }


        return $this->render('share/index.html.twig', [
            'users' => $listeArtisteEchange,
            'galeries' => $listeGalerieUser,
        ]);
    }

    /**
     * @Route("/share/{slug}", name="share_one")
     */
    public function indexUser($slug, UserRepository $userRepo, GalleryEchangeRepository $galerieEchange): Response
    {
        // je dois trouver toutes les galeries de vente d'un seul user

        $userGaleryEchange = $userRepo->findAll();

        foreach ($userGaleryEchange as $user) {
            $galerie = $user->getGalleryEchange();
        }

        return $this->render('share/artisteEchange.html.twig', [

            'galeries' => $galerie,
        ]);
    }
}
