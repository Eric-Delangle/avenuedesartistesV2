<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SwiftmailerBundle\Command\SendEmailCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use ReCaptcha\ReCaptcha;

class ContactController extends AbstractController
{


    private $mailer;


    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }


    /**
     * @Route("/contact", name="contact_us")
     */
    public function contact(Request $request)
    {

        $contact = new Contact();


        $form = $this->createForm(ContactType::class, $contact);


        $form->handleRequest($request);
        /* captcha */
/*
        $recaptcha = new ReCaptcha('6LeGor4aAAAAAEROt0YUsp0L77m4KlNxtLgPPSTi');
        $resp = $recaptcha->verify($request->request->get('g-recaptcha-response'), $request->getClientIp());
*/
        if ($form->isSubmitted() && $form->isValid()) {
            /*
            if (!$resp->isSuccess()) {
                $this->addFlash('warning', 'N\'oubliez pas de cocher la case "Je ne suis pas un robot"');
            } else {
*/
                $email = new Email();
                $email->from(new Address("info@ericdelangle-deco.fr", "Eric Delangle"))

                    ->to("info@ericdelangle-deco.fr")
                    ->html("<h1>Le mail est envoyé par: " . $contact->getEmail() . "</h1><p>" . $contact->getMessage() . "</p>")
                    ->subject($contact->getSubject());

                $this->mailer->send($email);




                $this->addFlash('success', 'Votre message a bien été envoyé !');



                return $this->redirectToRoute('home');
           // }
        }
        return $this->render('contact/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}