<?php

namespace App\Modules\Reporting\Controller;

use App\Modules\ScanManagement\Repository\ScanJobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ScanReportController extends AbstractController
{
    #[Route('/reporting/scan-reports', name: 'scan_reports_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        $scanJobRepository = $em->getRepository(\App\Modules\ScanManagement\Entity\ScanJob::class);
        $scanJobs = $scanJobRepository->findAccessibleByUser($user, ['startedAt' => 'DESC']);

        return $this->render('reporting/scan_report/index.html.twig', [
            'controller_name' => 'ScanReportController',
            'scanJobs' => $scanJobs,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    #[Route('/reporting/scan-reports/my-reports', name: 'my_scan_reports')]
    public function myReports(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        $scanJobRepository = $em->getRepository(\App\Modules\ScanManagement\Entity\ScanJob::class);
        $scanJobs = $scanJobRepository->findByUser($user, ['startedAt' => 'DESC']);

        return $this->render('reporting/scan_report/index.html.twig', [
            'controller_name' => 'ScanReportController',
            'scanJobs' => $scanJobs,
            'isAdmin' => $user->isAdmin(),
            'title' => 'My Scan Reports',
        ]);
    }
}
