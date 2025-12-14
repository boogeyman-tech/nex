<?php

namespace App\Modules\ScanManagement\Controller;

use App\Modules\ScanManagement\Entity\ScanJob;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ScanJobController extends AbstractController
{
    #[Route('/scan-jobs', name: 'scan_job_list')]
    public function list(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Query scan jobs via asset owned by user
        $qb = $em->createQueryBuilder()
            ->select('sj')
            ->from(ScanJob::class, 'sj')
            ->join('sj.asset', 'a')
            ->where('a.user = :user')
            ->setParameter('user', $user)
            ->orderBy('sj.startedAt', 'DESC')
            ->setMaxResults(50);

        $scanJobs = $qb->getQuery()->getResult();

        return $this->render('scan_management/scan_jobs.html.twig', [
            'scanJobs' => $scanJobs,
        ]);
    }

    #[Route('/scan-job/{id}/progress', name: 'scan_progress')]
    public function progress(ScanJob $scanJob): Response
    {
        // Ownership check
        if ($scanJob->getAsset()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not own this scan job.');
        }

        return $this->render('scan_management/scan_progress.html.twig', [
            'scanJob' => $scanJob,
        ]);
    }

    #[Route('/scan-job/{id}/status', name: 'scan_progress_api')]
    public function scanStatus(ScanJob $scanJob): JsonResponse
    {
        // Ownership check
        if ($scanJob->getAsset()->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        return new JsonResponse([
            'status' => $scanJob->getStatus(),
        ]);
    }

    #[Route('/scan-job/{id}/cancel', name: 'scan_cancel', methods: ['POST'])]
    public function cancelScan(Request $request, ScanJob $scanJob, EntityManagerInterface $em): Response
    {
        // Ownership check
        if ($scanJob->getAsset()->getUser() !== $this->getUser()) {
            return new Response('Access denied', 403);
        }

        if (!$this->isCsrfTokenValid('cancel_scan' . $scanJob->getId(), $request->headers->get('X-CSRF-TOKEN'))) {
            return new Response('Invalid CSRF token', 400);
        }

        if ($scanJob->getStatus() === 'running') {
            $scanJob->setStatus('cancelled');
            $em->flush();

            return new Response('Cancelled', 200);
        }

        return new Response('Scan is not running', 400);
    }

    #[Route('/scan-job/{id}/delete', name: 'scan_delete', methods: ['POST'])]
    public function deleteScan(Request $request, ScanJob $scanJob, EntityManagerInterface $em): Response
    {
        // Ownership check
        if ($scanJob->getAsset()->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'You do not own this scan job.');
            return $this->redirectToRoute('scan_progress', ['id' => $scanJob->getId()]);
        }

        if (!$this->isCsrfTokenValid('delete_scan' . $scanJob->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('scan_progress', ['id' => $scanJob->getId()]);
        }

        $em->remove($scanJob);
        $em->flush();

        $this->addFlash('success', 'Scan job deleted.');

        return $this->redirectToRoute('scan_job_list');
    }

    #[Route('/scan-jobs/delete-multiple', name: 'scan_job_delete_multiple', methods: ['POST'])]
    public function deleteMultiple(Request $request, EntityManagerInterface $em): Response
    {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_multiple_scans', $submittedToken)) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('scan_job_list');
        }

        $scanJobIds = $request->request->all('scan_job_ids');

        if (empty($scanJobIds)) {
            $this->addFlash('error', 'No scan jobs selected.');
            return $this->redirectToRoute('scan_job_list');
        }

        $scanJobs = $em->getRepository(ScanJob::class)->findBy(['id' => $scanJobIds]);

        $deletedCount = 0;
        foreach ($scanJobs as $scanJob) {
            // Ownership check
            if ($scanJob->getAsset()->getUser() === $this->getUser()) {
                $em->remove($scanJob);
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            $em->flush();
            $this->addFlash('success', $deletedCount . ' scan job(s) deleted.');
        } else {
            $this->addFlash('error', 'No scan jobs were deleted. You may not have permission to delete the selected jobs.');
        }

        return $this->redirectToRoute('scan_job_list');
    }
}
