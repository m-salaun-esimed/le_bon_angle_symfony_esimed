<?php
namespace App\EntityListener;

use App\Entity\Advert;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\WorkflowInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: 'workflow.advert.completed.publish', method: 'onPublish')]
class AdvertWorkflowSubscriber
{
    public function onPublish(CompletedEvent $event): void
    {
        $advert = $event->getSubject(); 

        if (!$advert instanceof Advert) {
            return;
        }

        $advert->setPublishAt(new \DateTimeImmutable()); 
       
    }
}
