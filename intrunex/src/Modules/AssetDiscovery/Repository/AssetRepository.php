<?php

namespace App\Modules\AssetDiscovery\Repository;

use App\Modules\AssetDiscovery\Entity\Asset;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Asset>
 */
class AssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asset::class);
    }

    /**
     * Find all assets for a specific user
     */
    public function findByUser(User $user, array $orderBy = ['userAssetNumber' => 'ASC']): array
    {
        return $this->findBy(['user' => $user], $orderBy);
    }

    /**
     * Find all assets accessible by a user (user's own assets + admin can see all)
     */
    public function findAccessibleByUser(User $user, array $orderBy = ['userAssetNumber' => 'ASC']): array
    {
        if ($user->isAdmin()) {
            return $this->findAll($orderBy);
        }
        
        return $this->findBy(['user' => $user], $orderBy);
    }

    /**
     * Count assets for a specific user
     */
    public function countByUser(User $user): int
    {
        return $this->count(['user' => $user]);
    }


    /**
     * Find assets by user with pagination
     */
    public function findByUserPaginated(User $user, int $offset = 0, int $limit = 20): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.userAssetNumber', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find assets by status for a user
     */
    public function findByUserAndStatus(User $user, string $status): array
    {
        return $this->findBy([
            'user' => $user,
            'status' => $status
        ], ['userAssetNumber' => 'ASC']);
    }

    /**
     * Find assets that need profiling
     */
    public function findAssetsNeedingProfiling(User $user = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.lastProfiledAt IS NULL')
            ->orderBy('a.createdAt', 'ASC');

        if ($user && !$user->isAdmin()) {
            $qb->andWhere('a.user = :user')
               ->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find assets that need vulnerability scanning
     */
    public function findAssetsNeedingVulnScan(User $user = null, int $days = 30): array
    {
        $cutoffDate = new \DateTimeImmutable("-{$days} days");
        
        $qb = $this->createQueryBuilder('a')
            ->where('a.lastVulnerabilityScanAt IS NULL OR a.lastVulnerabilityScanAt < :cutoff')
            ->setParameter('cutoff', $cutoffDate)
            ->orderBy('a.lastVulnerabilityScanAt', 'ASC');

        if ($user && !$user->isAdmin()) {
            $qb->andWhere('a.user = :user')
               ->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Check if user can access this asset
     */
    public function canUserAccessAsset(Asset $asset, User $user): bool
    {
        return $asset->canBeAccessedBy($user);
    }

    /**
     * Get asset statistics for a user
     */
    public function getStatsByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.status, COUNT(a.id) as count')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->groupBy('a.status');

        $results = $qb->getQuery()->getResult();
        
        $stats = [
            'total' => $this->countByUser($user),
            'by_status' => []
        ];

        foreach ($results as $result) {
            $stats['by_status'][$result['status']] = (int) $result['count'];
        }

        return $stats;
    }
}
