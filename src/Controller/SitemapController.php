<?php

namespace App\Controller;

use App\Repository\ArtisticWorkRepository;
use App\Repository\GalleryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'sitemap', defaults: ['_format' => 'xml'])]
    public function index(
        ArtisticWorkRepository $workRepo,
        GalleryRepository $galleryRepo,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        $urls = [];
        $now  = new \DateTime();

        // Pages statiques
        foreach ([
            ['route' => 'home',              'priority' => '1.0',  'freq' => 'daily'],
            ['route' => 'marketplace_index', 'priority' => '0.9',  'freq' => 'hourly'],
            ['route' => 'share_index',       'priority' => '0.8',  'freq' => 'daily'],
            ['route' => 'explain',           'priority' => '0.6',  'freq' => 'monthly'],
            ['route' => 'security_login',    'priority' => '0.4',  'freq' => 'yearly'],
            ['route' => 'security_registration', 'priority' => '0.5', 'freq' => 'yearly'],
        ] as $page) {
            $urls[] = [
                'loc'        => $urlGenerator->generate($page['route'], [], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod'    => $now->format('Y-m-d'),
                'changefreq' => $page['freq'],
                'priority'   => $page['priority'],
            ];
        }

        // Fiches œuvres marketplace
        $works = $workRepo->findBy(['listingType' => ['sale', 'exchange', 'both'], 'status' => 'available']);
        foreach ($works as $work) {
            $urls[] = [
                'loc'        => $urlGenerator->generate('marketplace_show', ['id' => $work->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod'    => $work->getUpdatedAt()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
            ];
        }

        // Galeries publiques
        $galleries = $galleryRepo->findAll();
        foreach ($galleries as $gallery) {
            $urls[] = [
                'loc'        => $urlGenerator->generate('galery_showUser', ['id' => $gallery->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'lastmod'    => $now->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority'   => '0.6',
            ];
        }

        $response = new Response(
            $this->renderView('sitemap/index.xml.twig', ['urls' => $urls]),
            200,
            ['Content-Type' => 'application/xml']
        );

        return $response;
    }
}
