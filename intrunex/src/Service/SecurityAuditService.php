<?php

namespace App\Service;

use App\Entity\User;
use App\Modules\AuditLogging\Entity\ActivityLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;

class SecurityAuditService
{
    private EntityManagerInterface $em;
    private Security $security;
    private RequestStack $requestStack;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        RequestStack $requestStack
    ) {
        $this->em = $em;
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

    public function logIsolationViolation(string $action, string $entityType, ?int $entityId, string $reason): void
    {
        $user = $this->security->getUser();
        
        if (!$user instanceof User) {
            return; // Don't log for anonymous users
        }

        $request = $this->requestStack->getCurrentRequest();
        $ipAddress = $request ? $request->getClientIp() : 'unknown';
        $userAgent = $request ? $request->headers->get('User-Agent') : 'unknown';

        $activityLog = new ActivityLog();
        $activityLog->setUser($user);
        $activityLog->setMessage(sprintf(
            'SECURITY VIOLATION: %s on %s (ID: %s) - Reason: %s',
            $action,
            $entityType,
            $entityId ?: 'N/A',
            $reason
        ));
        $activityLog->setStatus('SECURITY_VIOLATION');
        $activityLog->setDetails(json_encode([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'reason' => $reason,
            'timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s')
        ]));
        $activityLog->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($activityLog);
        $this->em->flush();

        // Log to application logger as well
        error_log(sprintf(
            'SECURITY VIOLATION: User %s attempted %s on %s (ID: %s) from IP %s - %s',
            $user->getEmail(),
            $action,
            $entityType,
            $entityId ?: 'N/A',
            $ipAddress,
            $reason
        ));
    }

    public function logAccessDenied(string $action, string $entityType, ?int $entityId): void
    {
        $this->logIsolationViolation($action, $entityType, $entityId, 'Access denied');
    }

    public function logUnauthorizedAccess(string $action, string $entityType, ?int $entityId): void
    {
        $this->logIsolationViolation($action, $entityType, $entityId, 'Unauthorized access attempt');
    }

    public function logDataLeakageAttempt(string $action, string $entityType, ?int $entityId): void
    {
        $this->logIsolationViolation($action, $entityType, $entityId, 'Potential data leakage attempt');
    }

    public function logPrivilegeEscalation(string $action, string $entityType, ?int $entityId): void
    {
        $this->logIsolationViolation($action, $entityType, $entityId, 'Privilege escalation attempt');
    }

    public function getSecurityEvents(User $user, int $limit = 50): array
    {
        $activityLogRepository = $this->em->getRepository(ActivityLog::class);
        return $activityLogRepository->findBy(
            ['user' => $user, 'status' => 'SECURITY_VIOLATION'],
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    public function getAllSecurityEvents(int $limit = 100): array
    {
        $activityLogRepository = $this->em->getRepository(ActivityLog::class);
        return $activityLogRepository->findBy(
            ['status' => 'SECURITY_VIOLATION'],
            ['createdAt' => 'DESC'],
            $limit
        );
    }
}
