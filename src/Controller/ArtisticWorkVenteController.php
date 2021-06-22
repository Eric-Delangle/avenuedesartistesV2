<?php

namespace App\Controller;

use Cocur\Slugify\Slugify;
use App\Entity\GalleryVente;
use App\Entity\ArtisticWorkVente;
use App\Form\ArtisticWorkVenteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArtisticWorkVenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/artistic/work/vente")
 */
class ArtisticWorkVenteController extends AbstractController
{
    /**
     * @Route("/", name="artistic_work_vente_index")
     */
    public function index(ArtisticWorkVenteRepository $artisticWorkRepository): Response
    {
        return $this->render('artistic_work_vente/index.html.twig', [
            'artistic_work_ventes' => $artisticWorkRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new/{id}", name="artistic_work_vente_new", requirements={"id": "\d+" })
     */
    public function new(Request $request, GalleryVente $galleryVente): Response
    {

        $artisticWorkVente = new ArtisticWorkVente();
        $artisticWorkVente->setGalleryVente($galleryVente);

        $form = $this->createForm(ArtisticWorkVenteType::class, $artisticWorkVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre image a bien été ajoutée!');
            $slugify = new Slugify();
            $slug = $slugify->slugify($artisticWorkVente->getName());
            $artisticWorkVente->setSlug($slug);
            $artisticWorkVente->setCreatedAt(new \DateTime());
            $artisticWorkVente->setUpdatedAt(new \DateTime());
            $artisticWorkVente->setGalleryVente($galleryVente);


            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($artisticWorkVente);
            $entityManager->flush();

            return $this->redirectToRoute('member_index');
        }

        return $this->render('artistic_work_vente/new.html.twig', [
            'artistic_work_vente' => $artisticWorkVente,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="artistic_work_echange_show", requirements={"id": "\d+" })
     */
    public function show(ArtisticWorkVente $artisticWorkVente): Response
    {
        return $this->render('artistic_work_vente/show.html.twig', [
            'artistic_work_vente' => $artisticWorkVente,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="artistic_work_vente_edit")
     */
    public function edit(Request $request, ArtisticWorkVente $artisticWorkVente): Response
    {
        $form = $this->createForm(ArtisticWorkVenteType::class, $artisticWorkVente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('artistic_work_vente_index');
        }

        return $this->render('artistic_work_vente/edit.html.twig', [
            'artistic_work_vente' => $artisticWorkVente,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="artistic_work_vente_delete")
     */
    public function delete(Request $request, ArtisticWorkVente $artisticWorkVente): Response
    {
        if ($this->isCsrfTokenValid('delete' . $artisticWorkVente->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($artisticWorkVente);
            $entityManager->flush();
        }

        return $this->redirectToRoute('artistic_work_vente_index');
    }
}
