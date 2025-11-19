<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Modules\AssetDiscovery\Entity\Asset;
use App\Modules\AssetVulnerability\Entity\Vulnerability;
use App\Modules\ScanManagement\Entity\ScanJob;

class AnalyticsController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/analytics', name: 'app_analytics')]
    public function index(): Response
    {
        $em = $this->doctrine->getManager();

        // Total Assets
        $totalAssets = $em->getRepository(Asset::class)->count([]);

        // Total Scans
        $totalScans = $em->getRepository(ScanJob::class)->count([]);

        // Total Vulnerabilities
        $vulnerabilitiesFound = $em->getRepository(Vulnerability::class)->count([]);

        // Critical Issues
        $criticalIssues = $em->getRepository(Vulnerability::class)->count(['severity' => 'Critical']);

        // Severity Data
        $severityData = [];
        $qb = $em->createQueryBuilder();
        $qb->select('v.severity, COUNT(v.id) as count')
           ->from(Vulnerability::class, 'v')
           ->groupBy('v.severity');
        $results = $qb->getQuery()->getResult();
        foreach ($results as $result) {
            $severityData[$result['severity']] = (int) $result['count'];
        }

        // Vulnerability Trend Data (monthly for current year)
        $currentYear = date('Y');
        $vulnerabilityTrendData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'data' => array_fill(0, 12, 0),
        ];
        $qb = $em->createQueryBuilder();
        $qb->select('v')
           ->from(Vulnerability::class, 'v')
           ->where('v.discoveredAt >= :start')
           ->andWhere('v.discoveredAt < :end')
           ->setParameter('start', new \DateTime("$currentYear-01-01"))
           ->setParameter('end', new \DateTime(($currentYear + 1) . "-01-01"));
        $vulnerabilities = $qb->getQuery()->getResult();
        foreach ($vulnerabilities as $vuln) {
            $month = (int) $vuln->getDiscoveredAt()->format('m') - 1; // 0-based index
            $vulnerabilityTrendData['data'][$month]++;
        }

        // Recent Scans (last 4 completed scans)
        $recentScans = [];
        $qb = $em->createQueryBuilder();
        $qb->select('sj')
           ->from(ScanJob::class, 'sj')
           ->where('sj.finishedAt IS NOT NULL')
           ->orderBy('sj.finishedAt', 'DESC')
           ->setMaxResults(4);
        $scanJobs = $qb->getQuery()->getResult();
        foreach ($scanJobs as $scanJob) {
            $assetName = $scanJob->getAsset()->getName();
            $date = $scanJob->getFinishedAt()->format('Y-m-d');
            // Count vulnerabilities for this scan job
            $vulnCount = $em->getRepository(Vulnerability::class)->count(['scanJob' => $scanJob]);
            // Max severity for this scan job
            $qbMax = $em->createQueryBuilder();
            $qbMax->select('v.severity')
                  ->from(Vulnerability::class, 'v')
                  ->where('v.scanJob = :scanJob')
                  ->setParameter('scanJob', $scanJob)
                  ->orderBy('CASE WHEN v.severity = \'Critical\' THEN 1 WHEN v.severity = \'High\' THEN 2 WHEN v.severity = \'Medium\' THEN 3 ELSE 4 END', 'ASC')
                  ->setMaxResults(1);
            $maxSeverityResult = $qbMax->getQuery()->getOneOrNullResult();
            $severity = $maxSeverityResult ? $maxSeverityResult['severity'] : 'None';
            $recentScans[] = [
                'asset' => $assetName,
                'date' => $date,
                'vulnerabilities' => $vulnCount,
                'severity' => $severity,
            ];
        }

        return $this->render('analytics/index.html.twig', [
            'totalAssets' => $totalAssets,
            'totalScans' => $totalScans,
            'vulnerabilitiesFound' => $vulnerabilitiesFound,
            'criticalIssues' => $criticalIssues,
            'severityData' => $severityData,
            'vulnerabilityTrendData' => $vulnerabilityTrendData,
            'recentScans' => $recentScans,
        ]);
    }
}
