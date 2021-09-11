<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MemberController extends AbstractController
{
    /**
     * @Route("/member", name="member_index")
     */
    public function index(UserRepository $userRepo): Response
    {
        $id = $this->getUser()->getId();
        $activToken = $this->getUser()->getActivationToken();

        if ($activToken != null) {

            return $this->render('emails/activationFailed.html.twig', [

                'token' => $activToken
            ]);
            /*
            echo '<p class="activation_text">Vous n\'avez pas encore activé votre compte.</p>';
            $url = "http://localhost:8000";
            $texte_du_lien = "Retourner à l'accueil";
            echo '<a class="activation_link" href="' . $url . '">' . $texte_du_lien . '</a>';
*/



            //exit();
        }
        $perso = $userRepo->findBy(['id' => $id]);

        return $this->render('member/index.html.twig', [
            'perso' => $perso,
            'token' => $activToken
        ]);
    }
}
