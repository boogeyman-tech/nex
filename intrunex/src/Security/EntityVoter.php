<?php

namespace App\Security;

use App\Entity\User;
use App\Modules\AssetDiscovery\Entity\Asset;
use App\Modules\AssetVulnerability\Entity\Vulnerability;
use App\Modules\ScanManagement\Entity\ScanJob;
use App\Modules\AuditLogging\Entity\ActivityLog;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class EntityVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';
    const DELETE = 'delete';
    const CREATE = 'create';
    const ADMIN = 'admin';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::CREATE, self::ADMIN])) {
            return false;
        }

        if (!is_object($subject)) {
            return false;
        }

        return $subject instanceof Asset 
            || $subject instanceof Vulnerability 
            || $subject instanceof ScanJob
            || $subject instanceof ActivityLog
            || $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Admin users can do everything
        if ($user->isAdmin()) {
            return true;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
            case self::CREATE:
                return $this->canCreate($subject, $user);
            case self::ADMIN:
                return $user->isAdmin();
        }

        return false;
    }

    private function canView($entity, User $user): bool
    {
        return $this->isOwner($entity, $user);
    }

    private function canEdit($entity, User $user): bool
    {
        return $this->isOwner($entity, $user);
    }

    private function canDelete($entity, User $user): bool
    {
        return $this->isOwner($entity, $user);
    }

    private function canCreate($entity, User $user): bool
    {
        // Users can create entities for themselves
        return $user->getId() !== null;
    }

    private function isOwner($entity, User $user): bool
    {
        switch (true) {
            case $entity instanceof Asset:
                return $entity->getUser() === $user;
            
            case $entity instanceof Vulnerability:
                return $entity->getAsset()->getUser() === $user;
            
            case $entity instanceof ScanJob:
                return $entity->getAsset()->getUser() === $user;
            
            case $entity instanceof ActivityLog:
                return $entity->getUser() === $user;
            
            case $entity instanceof User:
                return $entity === $user;
        }

        return false;
    }
}
