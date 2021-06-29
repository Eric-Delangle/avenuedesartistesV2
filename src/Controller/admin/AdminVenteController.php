<?php

namespace App\Controller\admin;

use Cocur\Slugify\Slugify;
use App\Entity\GalleryVente;
use App\Form\GalleryVenteType;
use App\Repository\GalleryVenteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/admin/galerie/vente")
 */
class AdminVenteController extends AbstractController
{

    /**
     * @Route("/{slug}", name="admin_gallery_vente_index", methods={"GET"})
     */
    public function index(GalleryVenteRepository $galleryVenteRepository): Response
    {
        $id = $this->getUser()->getId();
        $galerieVentePerso = $galleryVenteRepository->findBy(['user' => $id]);

        return $this->render('admin/admin_vente/index.html.twig', [
            'gallery_ventes' => $galerieVentePerso,
        ]);
    }

    /**
     * @Route("/admin/{id}", name="admin_gallery_vente_show", methods={"GET"})
     */
    public function show(GalleryVente $galleryvente): Response
    {

        return $this->render('admin/admin_vente/show.html.twig', [
            'gallery_vente' => $galleryvente,
        ]);
    }

    /**
     * @Route("/new/{slug}", name="admin_gallery_vente_new", methods={"GET","POST"})
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

            return $this->redirectToRoute('admin_gallery_vente_index', ['slug' => $galleryVente->getSlug()]);
        }

        return $this->render('admin/admin_vente/new.html.twig', [
            'gallery_vente' => $galleryVente,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_gallery_vente_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, GalleryVente $galleryVente): Response
    {
        $form = $this->createForm(GalleryVenteType::class, $galleryVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', 'Votre galerie de vente a bien été modifiée!');
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_gallery_vente_index');
        }

        return $this->render('admin/admin_vente/edit.html.twig', [
            'gallery_vente' => $galleryVente,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="admin_gallery_vente_delete", methods={"POST"})
     */
    public function delete(Request $request, GalleryVente $galleryVente): Response
    {
        if ($this->isCsrfTokenValid('delete' . $galleryVente->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($galleryVente);
            $entityManager->flush();
            $this->addFlash('success', 'Votre galerie de vente a bien été supprimée!');
        }

        return $this->redirectToRoute('admin_gallery_vente_index', ['slug' => $galleryVente->getSlug()]);
    }
}
