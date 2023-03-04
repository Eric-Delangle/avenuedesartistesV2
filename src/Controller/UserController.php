<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Gallery;
use App\Form\UserType;
use App\Form\UserModifType;
use App\Form\AvatarType;
use App\Repository\UserRepository;
use App\Repository\CategoryRepository;
use App\Repository\GalleryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    
    private $userPasswordHasherInterface;

    public function __construct(private ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->passwordHasher = $userPasswordHasherInterface;
        $this->doctrine = $doctrine;
    }

    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository, PaginatorInterface $paginator): Response
    {
        return $this->render('user/index.html.twig', [
          'users' => $paginator->paginate(
          $userRepository->findBy(['category' => $category]),
          $request->query->getInt('page' , 1 ),
          4),
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
            $entityManager = $this->doctrine->getManager();
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
        GalleryRepository $galleryRepo
    ) {


        return $this->render('user/show.html.twig', [
            'user' => $user,
            'users' => $userRepo->findOneBySlug(['slug' => $slug]),
            'categories' => $categoryRepo,
            'galleryechange' => $galleryRepo->findBy(['user' => $user]),
        ]);
    }
    
    
     /**
     * @Route("/{id}/edit/avatar", name="edit_avatar", methods={"POST","GET"})
     */
    public function editAvatar(Request $request, User $user, ValidatorInterface $validator): Response
    {

        $form = $this->createForm(AvatarType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           //dd($user->getAvatarFile());
         
            $user->setAvatar($user->getAvatarFile());
            $this->addFlash('success', 'Votre avatar a bien été mis à jour !');
            $this->doctrine->getManager()->flush();


            return $this->redirectToRoute('member_index');
        }
        return $this->render('user/editAvatar.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
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
            $this->doctrine->getManager()->flush();


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
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
