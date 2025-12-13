<?php

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait UserAwareTrait
{
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Check if the current user has access to this entity
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->user === $user;
    }

    /**
     * Check if the current user can access this entity (owner or admin)
     */
    public function canBeAccessedBy(User $user): bool
    {
        if ($this->isOwnedBy($user)) {
            return true;
        }

        // Check if user has admin role
        return in_array('ROLE_ADMIN', $user->getRoles());
    }
}
