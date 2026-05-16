<?php

namespace App\Service;

use App\Entity\Oeuvre;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacebookPageService {
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $pageId,
        private string $pageAccessToken,
        private string $siteBaseUrl,
    ) {}

    public function publierOeuvre(Oeuvre $oeuvre): void {
        $message = sprintf(
            "🎨 Nouvelle œuvre disponible : %s\n\n%s\n\n👉 Voir l'œuvre sur Avenue des Artistes",
            $oeuvre->getTitre(),
            $oeuvre->getDescription(),
        );

        $this->httpClient->request('POST', sprintf(
            'https://graph.facebook.com/%s/photos', $this->pageId
        ), [
            'body' => [
                'caption'      => $message,
                'url'          => $oeuvre->getImageUrl(), // URL publique de l'image
                'link'         => $this->siteBaseUrl . '/oeuvres/' . $oeuvre->getSlug(),
                'access_token' => $this->pageAccessToken,
            ],
        ]);
    }
}