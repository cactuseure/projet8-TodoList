<?php

namespace App\EventListener;

use App\Entity\Task;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class TaskListener
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function prePersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if ($entity instanceof Task) {
            $entity->setCreatedAt(new \DateTimeImmutable());
            $entity->setDone(false);

            $user = $this->security->getUser();

            // Ajouter l'utilisateur comme propriétaire
            if ($user) {
                $entity->setOwner($user);
            }
        }
    }
}