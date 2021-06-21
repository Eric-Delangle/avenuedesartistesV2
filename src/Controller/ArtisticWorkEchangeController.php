<?php

namespace App\Controller;

use App\Entity\GalleryEchange;
use App\Entity\ArtisticWorkEchange;
use App\Form\ArtisticWorkEchangeType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArtisticWorkEchangeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/artistic/work/echange")
 */
class ArtisticWorkEchangeController extends AbstractController
{
    /**
     * @Route("/", name="artistic_work_echange_index", methods={"GET"})
     */
    public function index(ArtisticWorkEchangeRepository $artisticWorkRepository): Response
    {
        return $this->render('artistic_work_echange/index.html.twig', [
            'artistic_work_echanges' => $artisticWorkRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="artistic_work_echange_new", methods={"POST"}, requirements={"id": "\d+" })
     */
    public function new(Request $request, GalleryEchange $galleryEchange): Response
    {
        $artisticWorkEchange = new ArtisticWorkEchange();
        $artisticWorkEchange->setGalleryEchange($galleryEchange);

        $form = $this->createForm(ArtisticWorkEchangeType::class, $artisticWorkEchange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre image a bien été ajoutée!');
            $artisticWorkEchange->setCreatedAt(new \DateTime());
            $artisticWorkEchange->setGalleryEchange($galleryEchange);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($artisticWorkEchange);
            $entityManager->flush();

            return $this->redirectToRoute('artistic_work_echange_index');
        }

        return $this->render('artistic_work_echange/new.html.twig', [
            'artistic_work_echange' => $artisticWorkEchange,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="artistic_work_echange_show", methods={"GET"}, requirements={"id": "\d+" })
     */
    public function show(ArtisticWorkEchange $artisticWorkEchange): Response
    {
        return $this->render('artistic_work_echange/show.html.twig', [
            'artistic_work_echange' => $artisticWorkEchange,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="artistic_work_echange_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ArtisticWorkEchange $artisticWorkEchange): Response
    {
        $form = $this->createForm(ArtisticWorkEchangeType::class, $artisticWorkEchange);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('artistic_work_echange_index');
        }

        return $this->render('artistic_work_echange/edit.html.twig', [
            'artistic_work_echange' => $artisticWorkEchange,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="artistic_work_echange_delete", methods={"POST"})
     */
    public function delete(Request $request, ArtisticWorkEchange $artisticWorkEchange): Response
    {
        if ($this->isCsrfTokenValid('delete' . $artisticWorkEchange->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($artisticWorkEchange);
            $entityManager->flush();
        }

        return $this->redirectToRoute('artistic_work_echange_index');
    }
}