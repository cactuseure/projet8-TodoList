<?php

namespace App\EventListener;

use App\Entity\Task;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class TaskListener
{
    public function prePersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof Task) {
            $entity->setCreatedAt(new \DateTimeImmutable());
            $entity->setDone(false);
        }
    }
}