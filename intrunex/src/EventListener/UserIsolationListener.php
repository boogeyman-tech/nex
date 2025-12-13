<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\SecurityAuditService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class UserIsolationListener
{
    private Security $security;
    private SecurityAuditService $securityAuditService;

    public function __construct(Security $security, SecurityAuditService $securityAuditService)
    {
        $this->security = $security;
        $this->securityAuditService = $securityAuditService;
    }

    #[AsEventListener(event: KernelEvents::REQUEST, priority: 10)]
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $user = $this->security->getUser();

        // Only apply isolation to authenticated users
        if (!$user instanceof User) {
            return;
        }

        // Check for suspicious activity patterns
        $this->checkSuspiciousActivity($request, $user);
    }

    private function checkSuspiciousActivity($request, User $user): void
    {
        $path = $request->getPathInfo();
        $method = $request->getMethod();
        $ipAddress = $request->getClientIp();

        // Check for rapid-fire requests (potential brute force)
        $rateLimitKey = sprintf('rate_limit_%s_%s', $user->getId(), $ipAddress);
        // This would need a cache implementation like Redis
        // For now, we'll skip this check

        // Check for attempts to access admin routes without admin privileges
        if (str_contains($path, '/admin') && !$user->isAdmin()) {
            $this->securityAuditService->logPrivilegeEscalation(
                'ADMIN_ACCESS_ATTEMPT',
                'ROUTE',
                null,
                sprintf('Non-admin user attempted to access admin route: %s', $path)
            );
        }

        // Check for attempts to access other users' data
        $userId = $request->attributes->get('user_id') ?: $request->query->get('user_id');
        if ($userId && $userId != $user->getId() && !$user->isAdmin()) {
            $this->securityAuditService->logUnauthorizedAccess(
                'CROSS_USER_ACCESS_ATTEMPT',
                'USER_DATA',
                $userId,
                sprintf('User attempted to access data for user ID: %s', $userId)
            );
        }

        // Check for API abuse patterns
        if (str_contains($path, '/api/')) {
            $this->checkApiAbuse($request, $user);
        }
    }

    private function checkApiAbuse($request, User $user): void
    {
        $path = $request->getPathInfo();
        $method = $request->getMethod();

        // Log API access for audit trail
        $this->securityAuditService->logIsolationViolation(
            'API_ACCESS',
            'API_ENDPOINT',
            null,
            sprintf('%s %s', $method, $path)
        );

        // Check for bulk data access attempts
        if (in_array($method, ['GET', 'POST']) && str_contains($path, '/bulk')) {
            if (!$user->isAdmin()) {
                $this->securityAuditService->logDataLeakageAttempt(
                    'BULK_DATA_ACCESS',
                    'API',
                    null,
                    'Non-admin user attempted bulk data access'
                );
            }
        }
    }
}
