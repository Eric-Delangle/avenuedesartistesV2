<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\GalleryVenteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BuyController extends AbstractController
{
    /**
     * @Route("/buy", name="buy_index")
     */
    public function index(UserRepository $userRepo, GalleryVenteRepository $galerieVente): Response
    {
        $user = $this->getUser();

        $listeArtisteVente = $userRepo->findAll();

        $slug = $userRepo->find('slug');
        $listeGalerieUser = $galerieVente->findAll();


        foreach ($listeArtisteVente as $artiste) {
            foreach ($listeGalerieUser as $galerie) {
                $id = $galerie->getId();
                $artiste = $galerieVente->findBy(['id' => $id]);
            }
        }


        return $this->render('buy/index.html.twig', [
            'users' => $listeArtisteVente,
            'galeries' => $listeGalerieUser,
        ]);
    }

    /**
     * @Route("/buy/{slug}", name="buy_one")
     */
    public function indexUser($slug, UserRepository $userRepo, GalleryVenteRepository $galerieVente): Response
    {
        // je dois trouver toutes les galeries de vente d'un seul user

        $userGaleryVente = $userRepo->findAll();

        foreach ($userGaleryVente as $user) {
            $galerie = $user->getGalleryVente();
        }

        return $this->render('buy/artisteVente.html.twig', [

            'galeries' => $galerie,
        ]);
    }
}
