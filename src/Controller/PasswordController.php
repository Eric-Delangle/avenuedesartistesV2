<?php

namespace App\Controller;

use App\Form\PasswordType;
use App\Controller\ChangePassword;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class PasswordController extends AbstractController
{

    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * @Route("/modifier-mot-de-passe", name="reset_password")
     */
    public function modificationPassword(Request $request, UserPasswordHasherInterface $encoder, EntityManagerInterface $manager)
    {

        $user = $this->getUser();
        $passwordUpdate = new ChangePassword();

        $form = $this->createForm(PasswordType::class, $passwordUpdate);
        dd($form);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dd($passwordUpdate);
            $newPassword = $passwordUpdate->getNewPassword();
            $hash = $encoder->encodePassword($user, $newPassword);
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
