<?php

namespace App\Controller;

use Cocur\Slugify\Slugify;
use App\Entity\GalleryVente;
use App\Form\GalleryVenteType;
use App\Repository\GalleryVenteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/galerie/vente")
 */
class GalleryVenteController extends AbstractController
{
    /**
     * @Route("/", name="gallery_vente_index", methods={"GET"})
     */
    public function index(GalleryVenteRepository $galleryVenteRepository): Response
    {
        $id = $this->getUser()->getId();
        $galerieVentePerso = $galleryVenteRepository->findBy(['user' => $id]);

        return $this->render('gallery_vente/index.html.twig', [
            'gallery_ventes' => $galerieVentePerso,
        ]);
    }


    /**
     * @Route("/{id}", name="gallery_vente_show", methods={"GET"})
     */
    public function show(GalleryVente $galleryVente): Response
    {

        return $this->render('gallery_vente/show.html.twig', [
            'gallery_vente' => $galleryVente,
        ]);
    }
}
