<?php

namespace App\Modules\ScanManagement\MessageHandler;

use App\Modules\ScanManagement\Message\ScanJobMessage;
use App\Modules\ScanManagement\Service\ScanJobService;
use App\Modules\AssetVulnerability\Service\VulnerabilityImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Modules\ScanManagement\Entity\ScanJob;

#[AsMessageHandler]
class ScanJobHandler
{
    private ScanJobService $scanJobService;
    private VulnerabilityImportService $vulnerabilityImportService;
    private EntityManagerInterface $em;

    public function __construct(ScanJobService $scanJobService, VulnerabilityImportService $vulnerabilityImportService, EntityManagerInterface $em)
    {
        $this->scanJobService = $scanJobService;
        $this->vulnerabilityImportService = $vulnerabilityImportService;
        $this->em = $em;
    }

    public function __invoke(ScanJobMessage $message)
    {
        $scanJob = new ScanJob();
        $scanJob->setStartedAt(new \DateTimeImmutable());

        $asset = $this->scanJobService->fetchAsset($message->getAssetId());

        if (!$asset) {
            $scanJob->setStatus('failed');
            $scanJob->setErrorMessage('Asset not found for id ' . $message->getAssetId());
        } else {
            $scanJob->setAsset($asset);
            $scanJob->setStatus('pending'); // Set to pending, actual scan initiated by other handlers
            $scanJob->setDetails('Scan job created, awaiting scanner-specific message dispatch.');
        }

        $scanJob->setFinishedAt(new \DateTimeImmutable()); // This will be updated by the scanner-specific handler
        $this->em->persist($scanJob);
        $this->em->flush();
    }
}
