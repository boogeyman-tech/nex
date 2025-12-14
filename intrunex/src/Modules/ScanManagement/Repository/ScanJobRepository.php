<?php

namespace App\Modules\ScanManagement\Repository;

use App\Modules\ScanManagement\Entity\ScanJob;
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
     * Find all scan jobs that are pending (status = 'pending')
     */
    public function findPendingJobs(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('s.startedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all jobs for a given asset
     */
    public function findByAsset(int $assetId): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.asset = :assetId')
            ->setParameter('assetId', $assetId)
            ->orderBy('s.startedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
