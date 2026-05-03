<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Entity\Category;
use Doctrine\Persistence\ManagerRegistry;

class HomeController extends AbstractController
{

     public function __construct(private ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/members.json', name: 'members_json')]
    public function membersLocations(): JsonResponse
    {
        $users = $this->doctrine->getRepository(User::class)->findAll();

        $data = [];
        foreach ($users as $user) {
            if (!$user->getLocation()) {
                continue;
            }
            $data[] = [
                'id'         => $user->getId(),
                'firstName'  => $user->getFirstName(),
                'lastName'   => $user->getLastName(),
                'slug'       => $user->getSlug(),
                'location'   => $user->getLocation(),
                'categories' => array_map(
                    fn($c) => ['name' => $c->getName()],
                    $user->getCategories()->toArray()
                ),
            ];
        }

        return new JsonResponse($data);
    }
}
