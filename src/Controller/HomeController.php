<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Entity\User;
use App\Entity\Message;
use App\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;

class HomeController extends AbstractController
{
    
     public function __construct(private ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /* la je veux recuperer les lieux de mes membres en bases de données afin de les transformer en json
    et pouvoir les afficher sur la map */
    /**
     * @Route("/", name="home")
     */

    public function membersLocations(SerializerInterface $serializer)
    {

        //je récupere le repository des users et je vais checher ses infos
        $repositoryCat = $this->doctrine->getRepository(Category::class);
        $repositoryUser = $this->doctrine->getRepository(User::class);
        $repositoryMess = $this->doctrine->getRepository(Message::class);
        $user = $repositoryUser->findAll();

        // la je vais chercher ses catégories

        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);


        $data = $serializer->serialize(
            $user,
            'json',

            ['attributes' => 
                ['id', 'firstName', 'lastName', 'slug', 'location', 'categories' =>
                 ['name'], 'messages' =>
                  ['message']
                ]
            ]
        );
        json_encode($data);



        // Création du fichier json user

        // Nom du fichier à créer
        $members = 'members.json';

        // Ouverture du fichier
        $members = fopen($members, 'w+');

        // Ecriture dans le fichier
        fwrite($members, $data);


        // Fermeture du fichier
        fclose($members);

        return $this->render('home/index.html.twig');
    }
}