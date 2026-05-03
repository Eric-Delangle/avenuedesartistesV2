<?php

namespace App\Controller;

use App\Entity\ArtisticWork;
use App\Entity\Offer;
use App\Form\OfferType;
use App\Repository\ArtisticWorkRepository;
use App\Repository\CategoryRepository;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        $this->addFlash('info', 'Le marché arrive bientôt ! Cette fonctionnalité sera disponible prochainement.');
        return $this->redirectToRoute('home');

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

    #[Route('/{id}', name: 'marketplace_show', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function show(
        ArtisticWork $work,
        Request $request,
        ArtisticWorkRepository $artworkRepository,
        EntityManagerInterface $em
    ): Response {
        $this->addFlash('info', 'Le marché arrive bientôt ! Cette fonctionnalité sera disponible prochainement.');
        return $this->redirectToRoute('home');

        if ($work->getListingType() === 'none' || $work->getStatus() !== 'available') {
            throw $this->createNotFoundException('Cette œuvre n\'est pas disponible sur la marketplace.');
        }

        $offerForm = null;
        $user = $this->getUser();

        if ($user && $work->getGallery()?->getUser() !== $user) {
            $galleries = $user->getGalleryEchange();
            $userWorks = $galleries && count($galleries) > 0
                ? $artworkRepository->findBy(['gallery' => $galleries->toArray()])
                : [];

            $offer = new Offer();
            $offerForm = $this->createForm(OfferType::class, $offer, [
                'allowed_types' => [$work->getListingType()],
                'user_works'    => $userWorks,
            ]);
            $offerForm->handleRequest($request);

            if ($offerForm->isSubmitted() && $offerForm->isValid()) {
                $offer->setSender($user);
                $offer->setTargetWork($work);
                $em->persist($offer);
                $em->flush();

                $this->addFlash('success', 'Votre offre a bien été envoyée !');

                return $this->redirectToRoute('marketplace_show', ['id' => $work->getId()]);
            }
        }

        return $this->render('marketplace/show.html.twig', [
            'work'      => $work,
            'offerForm' => $offerForm?->createView(),
        ]);
    }
}
