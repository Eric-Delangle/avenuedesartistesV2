<?php

namespace App\Controller;

use App\Entity\ArtisticWork;
use App\Repository\ArtisticWorkRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/marketplace')]
class MarketplaceController extends AbstractController
{
    #[Route('', name: 'marketplace_index', methods: ['GET'])]
    public function index(
        Request $request,
        ArtisticWorkRepository $artworkRepository,
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator
    ): Response {
        $filters = [
            'type'     => $request->query->get('type', ''),
            'category' => $request->query->get('category', ''),
            'priceMin' => $request->query->get('priceMin', ''),
            'priceMax' => $request->query->get('priceMax', ''),
        ];

        $query = $artworkRepository->findForMarketplace($filters);

        $works = $paginator->paginate(
            $query,
            max(1, $request->query->getInt('page', 1)),
            9
        );

        return $this->render('marketplace/index.html.twig', [
            'works'      => $works,
            'filters'    => $filters,
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'marketplace_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(ArtisticWork $work): Response
    {
        if ($work->getListingType() === 'none' || $work->getStatus() !== 'available') {
            throw $this->createNotFoundException('Cette œuvre n\'est pas disponible sur la marketplace.');
        }

        return $this->render('marketplace/show.html.twig', [
            'work' => $work,
        ]);
    }
}
