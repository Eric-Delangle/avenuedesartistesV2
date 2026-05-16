<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Category;
use ReCaptcha\ReCaptcha;
use Cocur\Slugify\Slugify;
use App\Form\RegistrationType;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\ResetPassType;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends AbstractController
{

    private $authenticationUtils;
    private $mailer;

    public function __construct(private ManagerRegistry $doctrine ,AuthenticationUtils $authenticationUtils, MailerInterface $mailer)
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->mailer = $mailer;
        $this->doctrine = $doctrine;
    }

    #[Route('/inscription', name: 'security_registration')]
public function registration(UserPasswordHasherInterface $passwordHasher,
    Request $request, EntityManagerInterface $manager)
{
    $user = new User();
    $form = $this->createForm(RegistrationType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // Vérification reCAPTCHA v3
        $recaptcha = new ReCaptcha($_ENV['GOOGLE_RECAPTCHA_SECRET']);
        $resp = $recaptcha->setExpectedAction('register')
                          ->setScoreThreshold(0.5)
                          ->verify($request->request->get('recaptcha_token'), $request->getClientIp());
           

        if (!$resp->isSuccess()) {
            $this->addFlash('danger', 'Vérification anti-bot échouée. Veuillez réessayer.');
            return $this->redirectToRoute('security_registration');
        }

        $hash = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hash);
        $user->setActivationToken(bin2hex(random_bytes(32)));

        $slugify = new AsciiSlugger();
        $slug = $slugify->slug($user->getFirstName() . ' ' . $user->getLastName());
        $user->setSlug($slug);
        $user->setRegisteredAt(new \DateTime());
        $user->setNiveau(1);

        $manager->persist($user);
        $manager->flush();

        $this->addFlash('success', 'Votre compte a bien été créé, vérifiez vos emails pour pouvoir l\'activer.');

        // Email d'activation
        $email = (new Email())
            ->from('info@artetpartage.com')
            ->to($user->getEmail())
            ->subject('Activation de votre compte')
            ->html($this->renderView('emails/activation.html.twig', ['token' => $user->getActivationToken()]));

        $this->mailer->send($email);

        // Mail de notification admin
        $notif = (new Email())
            ->from('info@artetpartage.com')
            ->to('info@artetpartage.com')
            ->subject('Nouvelle inscription sur Art et Partage')
            ->text('Un nouvel utilisateur vient de s\'inscrire : ' . $user->getFirstName() . ' ' . $user->getLastName() . ' (' . $user->getEmail() . ')');

        $this->mailer->send($notif);

        return $this->redirectToRoute('security_login');
    }

    return $this->render('security/registration.html.twig', [
        'form' => $form->createView()
    ]);
}


    #[Route('/activation/{token}', name: 'security_activation')]
public function activation($token, UserRepository $userRepo)
{
    $user = $userRepo->findOneBy(['activation_token' => $token]);

    if (!$user) {
        $this->addFlash('danger', 'Token invalide ou déjà utilisé.');
        return $this->redirectToRoute('security_login');
    }

    $user->setActivationToken(null);
    $em = $this->doctrine->getManager();
    $em->persist($user);
    $em->flush();

    $this->addFlash('success', 'Votre compte est activé, vous pouvez vous connecter.');
    return $this->redirectToRoute('security_login');
}

#[Route('/connexion', name: 'security_login')]
public function login()
{
    return $this->render('security/login.html.twig', [
        'last_username' => $this->authenticationUtils->getLastUsername(),
        'error'         => $this->authenticationUtils->getLastAuthenticationError(),
    ]);
}

    #[Route('/deconnexion', name: 'security_logout')]
    public function logout()
    {
        $this->addFlash('success', 'Vous êtes bien déconnecté !');
    }

    #[Route('/oubli-pass', name: 'forgotten_password')]
    public function oubliPass(Request $request, UserRepository $users): Response
    {
        $form = $this->createForm(ResetPassType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $donnees = $form->getData();
            $user = $users->findOneByEmail($donnees['email']);

            if ($user === null) {
                $this->addFlash('danger', 'Cette adresse e-mail est inconnue');
                return $this->redirectToRoute('security_login');
            }

            $token = bin2hex(random_bytes(32));

            try {
                $user->setResetToken($token);
                $em = $this->doctrine->getManager();
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('security_login');
            }

            $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new Email())
                ->from('info@artetpartage.com')
                ->to($user->getEmail())
                ->subject('Réinitialisation de votre mot de passe')
                ->text("Bonjour,\n\nUne demande de réinitialisation de mot de passe a été effectuée.\nCliquez sur ce lien pour choisir un nouveau mot de passe :\n\n" . $url . "\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez cet e-mail.");

            $this->mailer->send($email);
            $this->addFlash('success', 'Un e-mail de réinitialisation vous a été envoyé.');

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/forgotten_password.html.twig', ['emailForm' => $form->createView()]);
    }

    #[Route('/reset_pass/{token}', name: 'reset_password')]
    public function resetPassword(Request $request, string $token, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['reset_token' => $token]);

        if ($user === null) {
            $this->addFlash('danger', 'Token inconnu ou expiré.');
            return $this->redirectToRoute('security_login');
        }

        if ($request->isMethod('POST')) {
               if (!$this->isCsrfTokenValid('reset_password', $request->request->get('_token'))) {
                    $this->addFlash('danger', 'Token de sécurité invalide.');
                    return $this->redirectToRoute('security_login');
                }
            $user->setResetToken(null);
            $user->setPassword($passwordHasher->hashPassword($user, $request->request->get('password')));
            $this->doctrine->getManager()->flush();
            $this->addFlash('success', 'Mot de passe mis à jour. Vous pouvez vous connecter.');
            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/reset_password.html.twig', ['token' => $token]);
    }
}
