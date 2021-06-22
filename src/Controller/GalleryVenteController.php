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
     * @Route("/new", name="gallery_vente_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $galleryVente = new GalleryVente();
        $form = $this->createForm(GalleryVenteType::class, $galleryVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre galerie de vente a bien été crée !');
            $slugify = new Slugify();
            $slug = $slugify->slugify($galleryVente->getName());
            $galleryVente->setSlug($slug);
            $galleryVente->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($galleryVente);
            $entityManager->flush();

            return $this->redirectToRoute('member_index');
        }

        return $this->render('gallery_vente/new.html.twig', [
            'gallery_vente' => $galleryVente,
            'form' => $form->createView(),
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

    /**
     * @Route("/{id}/edit", name="gallery_vente_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, GalleryVente $galleryVente): Response
    {
        $form = $this->createForm(GalleryVenteType::class, $galleryVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', 'Votre galerie de vente a bien été modifiée!');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gallery_vente_index');
        }

        return $this->render('gallery_vente/edit.html.twig', [
            'gallery_vente' => $galleryVente,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gallery_vente_delete", methods={"POST"})
     */
    public function delete(Request $request, GalleryVente $galleryVente): Response
    {
        if ($this->isCsrfTokenValid('delete' . $galleryVente->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($galleryVente);
            $entityManager->flush();
            $this->addFlash('success', 'Votre galerie de vente a bien été supprimée!');
        }

        return $this->redirectToRoute('gallery_vente_index');
    }
}
