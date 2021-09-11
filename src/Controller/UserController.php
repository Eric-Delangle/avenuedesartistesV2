<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserModifType;
use App\Repository\UserRepository;
use App\Repository\CategoryRepository;
use App\Repository\GalleryVenteRepository;
use App\Repository\GalleryEchangeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    // la au click je veux le profil du membre
    /**
     * @Route("/{slug}", name="user_show", methods={"GET"})
     */

    public function show(
        $slug,
        User $user,
        UserRepository $userRepo,
        CategoryRepository $categoryRepo,
        GalleryVenteRepository $galleryVenteRepo,
        GalleryEchangeRepository $galleryEchangeRepo
    ) {


        return $this->render('user/show.html.twig', [
            'user' => $user,
            'users' => $userRepo->findOneBySlug(['slug' => $slug]),
            'categories' => $categoryRepo,
            'galleryvente' => $galleryVenteRepo->findBy(['user' => $user]),
            'galleryechange' => $galleryEchangeRepo->findBy(['user' => $user]),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, ValidatorInterface $validator): Response
    {

        $form = $this->createForm(UserModifType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->addFlash('success', 'Votre profil a bien été mis à jour !');
            $this->getDoctrine()->getManager()->flush();


            return $this->redirectToRoute('member_index');
        }
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
