<?php

namespace App\Security;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskVoter extends Voter
{
    const DELETE = 'delete';
    const EDIT = 'edit';
    public function __construct(
        private Security $security,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::DELETE, self::EDIT])) {
            return false;
        }

        // only vote on `Post` objects
        if (!$subject instanceof Task) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // you know $subject is a Post object, thanks to `supports()`
        /** @var Task $task */
        $task = $subject;

        return match($attribute) {
            self::DELETE => $this->canDelete($task, $user),
            self::EDIT => $this->canEdit($task, $user),
            default => throw new \LogicException('Vous n\'avez l\'autorisation de supprimer cette tâche')
        };
    }

    private function canDelete(Task $task, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN') || $user === $task->getOwner();
    }

    private function canEdit(Task $task, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN') || $user === $task->getOwner();
    }
}