<?php

namespace App\Controller;

use App\Entity\GalleryEchange;
use App\Form\GalleryEchangeType;
use App\Repository\GalleryEchangeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/gallery/echange")
 */
class GalleryEchangeController extends AbstractController
{
    /**
     * @Route("/", name="gallery_echange_index", methods={"GET"})
     */
    public function index(GalleryEchangeRepository $galleryEchangeRepository): Response
    {
        $id = $this->getUser()->getId();
        $galerieEchangePerso = $galleryEchangeRepository->findBy(['user' => $id]);

        return $this->render('gallery_echange/index.html.twig', [
            'gallery_echanges' => $galerieEchangePerso,
        ]);
    }

    /**
     * @Route("/new", name="gallery_echange_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $galleryEchange = new GalleryEchange();
        $form = $this->createForm(GalleryEchangeType::class, $galleryEchange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $galleryEchange->setSlug($galleryEchange->getName());
            $galleryEchange->setUser($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($galleryEchange);
            $entityManager->flush();

            return $this->redirectToRoute('gallery_echange_index');
        }

        return $this->render('gallery_echange/new.html.twig', [
            'gallery_echange' => $galleryEchange,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gallery_echange_show", methods={"GET"})
     */
    public function show(GalleryEchange $galleryEchange): Response
    {
        return $this->render('gallery_echange/show.html.twig', [
            'gallery_echange' => $galleryEchange,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="gallery_echange_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, GalleryEchange $galleryEchange): Response
    {
        $form = $this->createForm(GalleryEchangeType::class, $galleryEchange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('gallery_echange_index');
        }

        return $this->render('gallery_echange/edit.html.twig', [
            'gallery_echange' => $galleryEchange,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="gallery_echange_delete", methods={"POST"})
     */
    public function delete(Request $request, GalleryEchange $galleryEchange): Response
    {
        if ($this->isCsrfTokenValid('delete' . $galleryEchange->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($galleryEchange);
            $entityManager->flush();
        }

        return $this->redirectToRoute('gallery_echange_index');
    }
}
