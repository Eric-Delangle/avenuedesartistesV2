<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Gallery;
use App\Entity\Message;
use App\Entity\Reponse;
use App\Form\MessageType;
use App\Form\ReponseMessType;
use App\Form\MessageDirectType;
use App\Repository\GalleryRepository;
use App\Repository\MessageRepository;
use App\Repository\ReponseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @Route("/message")
 */
class MessageController extends AbstractController
{
    
    public function __construct(private ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    /**
     * @Route("/user/{id}", name="message_index", methods={"GET"})
     */
    public function index(MessageRepository $repo, User $user, ReponseRepository $reponseRepo): Response
    {
        return $this->render('message/index.html.twig', [
      
            'messages' => $repo->findBy(['destinataire' => $user]),
            'message' => $repo->findBy(['expediteur' => $user]),       
            'reponse' => $reponseRepo->findBy(['destinataire' => $user]),       
        ]);
    }

    /**
     * @Route("/new/{id}", name="message_new", methods={"GET","POST"})
     */
    public function new(Request $request, User $user): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre message a bien été envoyé !');
            $message->setExpediteur($user);
            $message->setPostedAt(new \DateTime());
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('message_new');
        }

        return $this->render('message/new.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    // pour envoyer un message a un membre depuis son profil public
    /**
     * @Route("/new/user/{id}", name="message_newUserMess", methods={"GET","POST"})
     */
    public function newUserMess(Request $request, User $user, GalleryRepository $galleryRepo): Response
    {
        $exp = $this->getUser();
        $message = new Message();
        $form = $this->createForm(MessageDirectType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'Votre message a bien été envoyé !');
            $message->setExpediteur($exp);
            $message->setDestinataire($user);
            $message->setPostedAt(new \DateTime());
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('message_newUserMess', ["id" =>$user->getId()]);
        }

        return $this->render('message/new.html.twig', [
            'message' => $message,
            'gallery' => $galleryRepo,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/show/{id}", name="message_show", methods={"GET"})
     */
    public function show(Message $message): Response
    {
        return $this->render('message/show.html.twig', [
            'message' => $message,
            dump($message),
        ]);
    }


    /**
     * @Route("/{id}", name="message_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Message $message): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->request->get('_token'))) {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($message);
            $this->addFlash('success', 'Ce message a bien été supprimé !');
            $entityManager->flush();
        }

        return $this->redirectToRoute('message_delete');
    }
}