<?php

namespace App\Modules\AuditLogging\Controller;

use App\Modules\AuditLogging\Entity\ActivityLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class ActivityLogController extends AbstractController
{
    #[Route('/activity-log', name: 'activity_log')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        $activityLogRepository = $em->getRepository(ActivityLog::class);
        $activityLogs = $activityLogRepository->findAccessibleByUser($user, ['createdAt' => 'DESC']);

        return $this->render('audit_logging/activity_log/index.html.twig', [
            'activity_logs' => $activityLogs,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    #[Route('/activity-log/my-logs', name: 'my_activity_log')]
    public function myLogs(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();
        
        $activityLogRepository = $em->getRepository(ActivityLog::class);
        $activityLogs = $activityLogRepository->findByUser($user, ['createdAt' => 'DESC']);

        return $this->render('audit_logging/activity_log/index.html.twig', [
            'activity_logs' => $activityLogs,
            'isAdmin' => $user->isAdmin(),
            'title' => 'My Activity Logs',
        ]);
    }

    #[Route('/activity-log/admin', name: 'admin_activity_log')]
    #[IsGranted('ROLE_ADMIN')]
    public function adminLogs(EntityManagerInterface $em): Response
    {
        $activityLogRepository = $em->getRepository(ActivityLog::class);
        $activityLogs = $activityLogRepository->findAll();

        return $this->render('audit_logging/activity_log/index.html.twig', [
            'activity_logs' => $activityLogs,
            'isAdmin' => true,
            'title' => 'All Activity Logs (Admin)',
        ]);
    }
}
