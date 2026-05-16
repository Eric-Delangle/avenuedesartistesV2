<?php

namespace App\Event;

use App\Entity\Oeuvre;
use Symfony\Contracts\EventDispatcher\Event;

class OeuvrePublieeEvent extends Event {
    public const NAME = 'oeuvre.publiee';

    public function __construct(private Oeuvre $oeuvre) {}

    public function getOeuvre(): Oeuvre{
        return $this->oeuvre;
    }
}