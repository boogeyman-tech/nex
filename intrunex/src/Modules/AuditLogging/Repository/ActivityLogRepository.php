<?php

namespace App\Modules\AuditLogging\Repository;

use App\Modules\AuditLogging\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActivityLog>
 */
class ActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityLog::class);
    }

    /**
     * Find activity logs for a specific user
     */
    public function findByUser(User $user, array $orderBy = ['createdAt' => 'DESC'], int $limit = null): array
    {
        return $this->createQueryBuilder('al')
            ->where('al.user = :user')
            ->setParameter('user', $user)
            ->orderBy('al.' . array_key_first($orderBy), array_values($orderBy)[0])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find activity logs accessible by a user (user's own logs + admin can see all)
     */
    public function findAccessibleByUser(User $user, array $orderBy = ['createdAt' => 'DESC'], int $limit = null): array
    {
        if ($user->isAdmin()) {
            return $this->findBy([], $orderBy, $limit);
        }
        
        return $this->findByUser($user, $orderBy, $limit);
    }

    /**
     * Count activity logs for a specific user
     */
    public function countByUser(User $user): int
    {
        return $this->createQueryBuilder('al')
            ->select('COUNT(al.id)')
            ->where('al.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find recent activity logs for a user
     */
    public function findRecentByUser(User $user, int $days = 7, int $limit = 50): array
    {
        $cutoffDate = new \DateTimeImmutable("-{$days} days");
        
        return $this->createQueryBuilder('al')
            ->where('al.user = :user')
            ->andWhere('al.createdAt >= :cutoff')
            ->setParameter('user', $user)
            ->setParameter('cutoff', $cutoffDate)
            ->orderBy('al.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find activity logs by status for a user
     */
    public function findByUserAndStatus(User $user, string $status): array
    {
        return $this->createQueryBuilder('al')
            ->where('al.user = :user')
            ->andWhere('al.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->orderBy('al.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if user can access this activity log
     */
    public function canUserAccessActivityLog(ActivityLog $activityLog, User $user): bool
    {
        return $activityLog->canBeAccessedBy($user);
    }

    /**
     * Get activity statistics for a user
     */
    public function getStatsByUser(User $user): array
    {
        $total = $this->countByUser($user);
        
        // Get recent activity (last 7 days)
        $recentLogs = $this->findRecentByUser($user, 7);
        
        // Get status breakdown
        $statusCounts = $this->createQueryBuilder('al')
            ->select('al.status, COUNT(al.id) as count')
            ->where('al.user = :user')
            ->setParameter('user', $user)
            ->groupBy('al.status')
            ->getQuery()
            ->getResult();

        $byStatus = [];
        foreach ($statusCounts as $result) {
            $byStatus[$result['status']] = (int) $result['count'];
        }

        return [
            'total' => $total,
            'recent_count' => count($recentLogs),
            'by_status' => $byStatus,
            'last_activity' => !empty($recentLogs) ? $recentLogs[0]->getCreatedAt() : null
        ];
    }

    /**
     * Log user activity with automatic user assignment
     */
    public function logActivity(User $user, string $message, string $status = 'INFO', bool $flush = true): ActivityLog
    {
        $activityLog = new ActivityLog();
        $activityLog->setUser($user);
        $activityLog->setMessage($message);
        $activityLog->setStatus($status);
        $activityLog->setCreatedAt(new \DateTimeImmutable());

        $this->save($activityLog, $flush);

        return $activityLog;
    }

    public function save(ActivityLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ActivityLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
