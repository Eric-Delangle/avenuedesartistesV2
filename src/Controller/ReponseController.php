<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Message;
use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\MessageRepository;
use App\Repository\ReponseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @Route("/reponse")
 */
class ReponseController extends AbstractController
{

    public function __construct(private ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/", name="reponse_index", methods={"GET"})
     */
    public function index(ReponseRepository $reponseRepository): Response
    {
        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponseRepository->findAll(),
        ]);
    }

    /**
     * @Route("/show/{id}", name="reponse_new", methods={"GET"})
     */
    
    public function messageReçu(Message $message): Response 
    {
        return $this->render('reponse/new.html.twig', [
            'message' => $message,
            dump($message),
        ]); 
    }


    /**
     * @Route("/new/{id}/reponse/", name="reponse_newRep", methods={"GET","POST"})
     */
    public function newRep(Request $request, Reponse $reponse): Response
    {
     
            $expediteur = $reponse->getDestinataire();
            $destinataire = $reponse->getExpediteur();
            $rep = new Reponse();
            $form = $this->createForm(ReponseType::class, $rep);
            $form->handleRequest($request);
            dump($expediteur);
            dump($destinataire);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->addFlash('success', 'Votre reponse a bien été envoyée !');
                $rep->setExpediteur($expediteur);
                $rep->setDestinataire($destinataire);
                $rep->setPostedAt(new \DateTime());
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($rep);
                $entityManager->flush();

                return $this->redirectToRoute('member');
            }

            return $this->render('reponse/new.html.twig', [
                'reponse' => $reponse,
                'form' => $form->createView(),
            ]);
        
    }
 
    /**
     * @Route("/new/{id}", name="reponse_new", methods={"GET","POST"})
     */
    public function new(Request $request, Message $message): Response
    {
     
            $expediteur = $message->getDestinataire();
            $destinataire = $message->getExpediteur();
            $reponse = new Reponse();
            $form = $this->createForm(ReponseType::class, $reponse);
            $form->handleRequest($request);
            dump($expediteur);
            dump($destinataire);
            if ($form->isSubmitted() && $form->isValid()) {
                $this->addFlash('success', 'Votre reponse a bien été envoyée !');
                $reponse->setExpediteur($expediteur);
                $reponse->setDestinataire($destinataire);
                $reponse->setPostedAt(new \DateTime());
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($reponse);
                $entityManager->flush();

                return $this->redirectToRoute('member');
            }

            return $this->render('reponse/new.html.twig', [
                'reponse' => $reponse,
                'form' => $form->createView(),
            ]);
        
    }

    /**
     * @Route("/show/{id}", name="reponse_showRep", methods={"GET"})
     */
    
    public function showrep(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    
    /**
     * @Route("/show/{id}/message/{message}", name="reponse_show", methods={"GET"})
     * @Entity("message", expr="repository.find(message)")
     */
    public function show(Reponse $reponse, Message $message): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
            'message' => $message,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="reponse_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Reponse $reponse): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->doctrine->getManager()->flush();

            return $this->redirectToRoute('reponse_index');
        }

        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reponse_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Reponse $reponse): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponse->getId(), $request->request->get('_token'))) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('member');
    }
}
