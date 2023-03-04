<?php

namespace App\Controller;

use App\Entity\User;
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
use App\Form\PasswordType;
use App\Controller\ChangePassword;


class PasswordController extends AbstractController
{

 private $userPasswordHasherInterface;

    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        $this->passwordHasher = $userPasswordHasherInterface;
    }

    /**
     * @Route("/password", name="reset_pass", methods={"GET","POST"})
     */
    public function modificationPassword(Request $request, UserPasswordHasherInterface $encoder, ManagerRegistry $manager)
    {

        $user = $this->getUser();
        $passwordUpdate = new ChangePassword();

        $form = $this->createForm(PasswordType::class, $passwordUpdate);
    
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $newPassword = $passwordUpdate->getNewPassword();
            $hash = $encoder->hashPassword($user, $newPassword);
            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', "Votre mot de passe a bien été modifié !");
            return $this->redirectToRoute('member_index');
        }

        return $this->render('password/index.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }
}
