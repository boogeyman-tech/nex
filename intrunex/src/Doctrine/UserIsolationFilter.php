<?php

namespace App\Doctrine;

use App\Entity\User;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class UserIsolationFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        $user = $this->getCurrentUser();
        
        // If no user is available (anonymous), deny all access
        if (!$user) {
            return '1 = 0';
        }

        // Admin users can access everything
        if ($user instanceof User && $user->isAdmin()) {
            return '';
        }

        // Apply user isolation based on entity type
        $entityClass = $targetEntity->getName();
        
        switch ($entityClass) {
            case 'App\Modules\AssetDiscovery\Entity\Asset':
                return sprintf('%s.user_id = %d', $targetTableAlias, $user->getId());
            
            case 'App\Modules\AssetVulnerability\Entity\Vulnerability':
                // Join through asset to get user_id
                return sprintf(
                    '%s.asset_id IN (SELECT a.id FROM asset_discovery_asset a WHERE a.user_id = %d)',
                    $targetTableAlias,
                    $user->getId()
                );
            
            case 'App\Modules\ScanManagement\Entity\ScanJob':
                // Join through asset to get user_id
                return sprintf(
                    '%s.asset_id IN (SELECT a.id FROM asset_discovery_asset a WHERE a.user_id = %d)',
                    $targetTableAlias,
                    $user->getId()
                );
            
            case 'App\Modules\AuditLogging\Entity\ActivityLog':
                return sprintf('%s.user_id = %d', $targetTableAlias, $user->getId());
            
            case 'App\Entity\User':
                // Users can only see themselves unless admin
                return sprintf('%s.id = %d', $targetTableAlias, $user->getId());
        }

        // Default: deny access to unknown entities
        return '1 = 0';
    }

    private function getCurrentUser(): ?User
    {
        // Get the current user from the security context
        $tokenStorage = $this->getApplication()->getContainer()->get('security.token_storage');
        $token = $tokenStorage->getToken();
        
        if (!$token) {
            return null;
        }
        
        $user = $token->getUser();
        
        return $user instanceof User ? $user : null;
    }
}
