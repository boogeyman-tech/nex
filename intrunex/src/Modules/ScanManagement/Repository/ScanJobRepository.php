<?php

namespace App\Modules\ScanManagement\Repository;

use App\Modules\ScanManagement\Entity\ScanJob;
use App\Modules\AssetDiscovery\Entity\Asset;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScanJob>
 */
class ScanJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScanJob::class);
    }

    /**
     * Find scan jobs for a specific user's assets
     */
    public function findByUser(User $user, array $orderBy = ['startedAt' => 'DESC']): array
    {
        return $this->createQueryBuilder('sj')
            ->join('sj.asset', 'a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('sj.' . array_key_first($orderBy), array_values($orderBy)[0])
            ->getQuery()
            ->getResult();
    }

    /**
     * Find scan jobs accessible by a user (user's assets + admin can see all)
     */
    public function findAccessibleByUser(User $user, array $orderBy = ['startedAt' => 'DESC']): array
    {
        if ($user->isAdmin()) {
            return $this->findBy([], $orderBy);
        }
        
        return $this->findByUser($user, $orderBy);
    }

    /**
     * Count scan jobs for a specific user's assets
     */
    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('sj')
            ->select('COUNT(sj.id)')
            ->join('sj.asset', 'a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find all scan jobs that are pending for a user
     */
    public function findPendingJobs(User $user = null): array
    {
        $qb = $this->createQueryBuilder('sj')
            ->where('sj.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('sj.startedAt', 'ASC');

        if ($user && !$user->isAdmin()) {
            $qb->join('sj.asset', 'a')
               ->andWhere('a.user = :user')
               ->setParameter('user', $user);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find scan jobs by asset for a user
     */
    public function findByAssetAndUser(Asset $asset, User $user): array
    {
        if (!$asset->canBeAccessedBy($user)) {
            return [];
        }

        return $this->findBy(['asset' => $asset], ['startedAt' => 'DESC']);
    }

    /**
     * Find all jobs for a given asset
     */
    public function findByAssetEntity(Asset $asset): array
    {
        return $this->createQueryBuilder('sj')
            ->where('sj.asset = :asset')
            ->setParameter('asset', $asset)
            ->orderBy('sj.startedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find running scan jobs for a user
     */
    public function findRunningByUser(User $user): array
    {
        return $this->createQueryBuilder('sj')
            ->join('sj.asset', 'a')
            ->where('a.user = :user')
            ->andWhere('sj.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'running')
            ->orderBy('sj.startedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find failed scan jobs for a user
     */
    public function findFailedByUser(User $user): array
    {
        return $this->createQueryBuilder('sj')
            ->join('sj.asset', 'a')
            ->where('a.user = :user')
            ->andWhere('sj.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'failed')
            ->orderBy('sj.startedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if user can access this scan job
     */
    public function canUserAccessScanJob(ScanJob $scanJob, User $user): bool
    {
        return $scanJob->getAsset()->canBeAccessedBy($user);
    }

    /**
     * Get scan job statistics for a user
     */
    public function getStatsByUser(User $user): array
    {
        $total = $this->countByUser($user);
        
        // Get status breakdown
        $statusCounts = $this->createQueryBuilder('sj')
            ->select('sj.status, COUNT(sj.id) as count')
            ->join('sj.asset', 'a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->groupBy('sj.status')
            ->getQuery()
            ->getResult();

        $byStatus = [];
        foreach ($statusCounts as $result) {
            $byStatus[$result['status']] = (int) $result['count'];
        }

        // Get recent activity
        $recentJobs = $this->findByUser($user, ['startedAt' => 'DESC'], 10);

        return [
            'total' => $total,
            'by_status' => $byStatus,
            'running_count' => $byStatus['running'] ?? 0,
            'failed_count' => $byStatus['failed'] ?? 0,
            'completed_count' => $byStatus['completed'] ?? 0,
            'recent_jobs' => $recentJobs
        ];
    }

    /**
     * Find scan jobs by status for a user
     */
    public function findByUserAndStatus(User $user, string $status): array
    {
        return $this->createQueryBuilder('sj')
            ->join('sj.asset', 'a')
            ->where('a.user = :user')
            ->andWhere('sj.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->orderBy('sj.startedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
