<?php

namespace App\Controller\admin;

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
 * @Route("/admin/artistic/work/vente")
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

        $quelgalerie = $galleryVente->getSlug();
        $galerieid = $galleryVente->getId();


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

            return $this->redirectToRoute('admin_gallery_vente_show', [
                'id' => $galerieid,
                'slug' => $quelgalerie,
            ]);
        }

        return $this->render('admin/artistic_work_vente/new.html.twig', [
            'artistic_work_vente' => $artisticWorkVente,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="artistic_work_echange_show", requirements={"id": "\d+" })
     */
    public function show(ArtisticWorkVente $artisticWorkVente): Response
    {

        return $this->render('admin/artistic_work_vente/show.html.twig', [
            'artistic_work_vente' => $artisticWorkVente,
            "artisticWork" => $artisticWorkVente
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
            $this->addFlash('success', "Votre image a bien été modifiée !");
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('artistic_work_vente_index');
        }

        return $this->render('admin/artistic_work_vente/edit.html.twig', [
            'artistic_work_vente' => $artisticWorkVente,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="artistic_work_vente_delete")
     */
    public function delete(Request $request, ArtisticWorkVente $artisticWorkVente): Response
    {
        //dd($artisticWorkVente);
        //$quelgalerie = $galleryVente->getSlug();
        $galerieid = $artisticWorkVente->getGalleryVente()->getId();
        //dd($galerieid);

        if ($this->isCsrfTokenValid('delete' . $artisticWorkVente->getId(), $request->request->get('_token'))) {
            $this->addFlash('success', "Votre image a bien été supprimée !");
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($artisticWorkVente);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_gallery_vente_show', [
            'id' => $galerieid,
        ]);
    }
}
