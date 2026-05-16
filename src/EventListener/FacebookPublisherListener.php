<?php

namespace App\EventListener;

use App\Event\OeuvrePublieeEvent;
use App\Service\FacebookPageService;

class FacebookPublisherListener {
    public function __construct(
        private FacebookPageService $facebookPageService
    ) {}

    public function onOeuvrePubliee(OeuvrePublieeEvent $event): void {
        $this->facebookPageService->publierOeuvre($event->getOeuvre());
    }
}