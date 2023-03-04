<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Category;
use ReCaptcha\ReCaptcha;
use Cocur\Slugify\Slugify;
use App\Form\RegistrationType;
use App\Entity\AdminController;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Doctrine\Persistence\ManagerRegistry;

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

    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(UserPasswordHasherInterface $passwordHasher,
        Request $request, EntityManagerInterface $manager)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        /* captcha */
        /*
        $recaptcha = new ReCaptcha('');
        $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
  
        if (!$resp->isSuccess()) {
         // $this->addFlash('N\'oubliez pas de cocher la case "Je ne suis pas un robot"');
        } else {
       */
        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hash);

            // on génère le token d'activation

            $user->setActivationToken(md5(uniqid()));


            $slugify = new AsciiSlugger();
            $slug = $slugify->slug($user->getFirstName() . '' . $user->getLastName());
            $user->setSlug($slug);
            $user->getCategories(new Category());
            $user->setRegisteredAt(new \DateTime());
            $user->setNiveau(1);
            $user->setUserIdentifier($user->getEmail());
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Votre compte a bien été créé, vérifiez vos emails pour pouvoir l\'activer.');


            // email d'activation

            $email = (new Email())
                ->from('infos@avenuedesartistes.com')
                ->to($user->getEmail())

                ->subject('Activation de votre compte')
                ->text($this->renderView('emails/activation.html.twig', ['token' => $user->getActivationToken()]));


            $this->mailer->send($email);


            return $this->redirectToRoute('security_login');
        }
        // }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/activation/{token}", name="security_activation")
     */
    public function activation($token, UserRepository $userRepo)
    {
        // on verifie si un utilsateur a ce token
        $user = $userRepo->findoneBy(['activation_token' => $token]);
        // si aucun utilisateur n'existe avec ce token
        if (!$user) {
            throw $this->createNotFoundException('Cet utilsateur n\'existe pas');
        }
        // on supprime le token
        $user->setActivationToken(null);
        $em = $this->doctrine->getManager();

        $em->persist($user);
        $em->flush();

        // on envoie un message flash
        $this->addFlash('message', 'Vous avez bien activé votre compte.');

        // on va sur l'espace membre
        return $this->redirectToRoute('member_index');
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login()
    {

        // Si le visiteur est déjà identifié, on le redirige vers l'accueil
        /*
        if ($this->getSubscribedServices(['security.authorization_checker'])->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('member_index');
       }
*/
        // Le service authentication_utils permet de récupérer le nom d'utilisateur
        // et l'erreur dans le cas où le formulaire a déjà été soumis mais était invalide
        // (mauvais mot de passe par exemple)
       

        return $this->render('security/login.html.twig', array(
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error'         => $this->authenticationUtils->getLastAuthenticationError(),
        ));

        $this->addFlash('success', 'Vous êtes bien connecté !');
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
        $this->addFlash('success', 'Vous êtes bien déconnecté !');
    }
    
     /**
     * @Route("/oubli-pass", name="forgotten_password")
     */
    public function oubliPass(
        Request $request,
        UserRepository $users,
        MailerInterface $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): Response {
        // On initialise le formulaire
        $form = $this->createForm(ResetPassType::class);

        // On traite le formulaire
        $form->handleRequest($request);

        // Si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les données
            $donnees = $form->getData();

            // On cherche un utilisateur ayant cet e-mail
            $user = $users->findOneByEmail($donnees['email']);

            // Si l'utilisateur n'existe pas
            if ($user === null) {
                // On envoie une alerte disant que l'adresse e-mail est inconnue
                $this->addFlash('danger', 'Cette adresse e-mail est inconnue');

                // On retourne sur la page de connexion
                return $this->redirectToRoute('security_login');
            }

            // On génère un token
            $token = $tokenGenerator->generateToken();

            // On essaie d'écrire le token en base de données
            try {
                $user->setResetToken($token);
                $entityManager = $this->doctrine->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('security_login');
            }

            // On génère l'URL de réinitialisation de mot de passe
            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            // On génère l'e-mail
            $email = (new Email())
                ->from('hello@example.com')
                ->to($user->getEmail())
                ->text(
                    "Bonjour, une demande de réinitialisation de mot de passe a été effectuée pour le site avenuedesartistes.com Veuillez cliquer sur le lien suivant : " . $url,
                    'text/html'
                );

            // On envoie l'e-mail
            $mailer->send($email);

            // On crée le message flash de confirmation
            $this->addFlash('message', 'E-mail de réinitialisation du mot de passe envoyé !');

            // On redirige vers la page de login
            return $this->redirectToRoute('security_login');
        }

        // On envoie le formulaire à la vue
        return $this->render('bundles/SyliusShopBundle/security/forgotten_password.html.twig', ['emailForm' => $form->createView()]);
    }

    /**
     * @Route("/reset_pass/{token}", name="reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        // On cherche un utilisateur avec le token donné
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['reset_token' => $token]);

        // Si l'utilisateur n'existe pas
        if ($user === null) {
            // On affiche une erreur
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('security_login');
        }

        // Si le formulaire est envoyé en méthode post
        if ($request->isMethod('POST')) {
            // On supprime le token
            $user->setResetToken(null);

            // On chiffre le mot de passe
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));

            // On stocke
            $entityManager = $this->doctrine->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // On crée le message flash
            $this->addFlash('message', 'Mot de passe mis à jour');

            // On redirige vers la page de connexion
            return $this->redirectToRoute('security_login');
        } else {
            // Si on n'a pas reçu les données, on affiche le formulaire
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
    }
}
