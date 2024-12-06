<?php

namespace App\EntityListener;

use App\Entity\Advert;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

#[AsEntityListener(event: Events::prePersist, entity: Advert::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Advert::class)]
class AdvertListener
{
    public function prePersist(Advert $advert, PrePersistEventArgs $event): void
    {
        if ($advert->getCreatadAt() === null) {
            $advert->setCreatadAt(new \DateTimeImmutable());
        }
    }

    public function preUpdate(Advert $advert, PreUpdateEventArgs $event): void
    {
        if ($advert->getCreatadAt() === null) {
            $advert->setCreatadAt(new \DateTimeImmutable());
        }
    }
}
