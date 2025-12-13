<?php

namespace App\Modules\Reporting\Controller;

use App\Modules\AssetVulnerability\Entity\Vulnerability;
use App\Modules\ScanManagement\Entity\ScanJob;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReportController extends AbstractController
{

    #[Route('/report/{id}', name: 'report_view')]
    public function viewReport($id, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $scanJobRepository = $em->getRepository(ScanJob::class);
        $vulnerabilityRepository = $em->getRepository(Vulnerability::class);
        
        $scanJob = $scanJobRepository->find($id);

        if (!$scanJob || !$scanJobRepository->canUserAccessScanJob($scanJob, $user)) {
            throw $this->createNotFoundException();
        }

        $asset = $scanJob->getAsset();
        $vulnerabilities = $vulnerabilityRepository->findByAssetAndUser($asset, $user);

        return $this->render('reporting/report/view.html.twig', [
            'scanJob' => $scanJob,
            'asset' => $asset,
            'vulnerabilities' => $vulnerabilities,
        ]);
    }

    #[Route('/reports', name: 'reports_list')]
    public function listReports(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        $scanJobRepository = $em->getRepository(ScanJob::class);
        $scanJobs = $scanJobRepository->findAccessibleByUser($user, ['startedAt' => 'DESC']);

        return $this->render('reporting/report/list.html.twig', [
            'scanJobs' => $scanJobs,
            'isAdmin' => $user->isAdmin(),
        ]);
    }
}
