<?php

namespace App\Modules\ScanManagement\MessageHandler;

use App\Modules\ScanManagement\Message\ScanJobMessage;
use App\Modules\ScanManagement\Service\ScanJobService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Modules\ScanManagement\Entity\ScanJob;
use App\Modules\VulnerabilityDetection\Message\NmapScanMessage;
use App\Modules\VulnerabilityDetection\Message\NiktoScanMessage;

#[AsMessageHandler]
class ScanJobHandler
{
    private ScanJobService $scanJobService;
    private EntityManagerInterface $em;
    private LoggerInterface $logger;
    private MessageBusInterface $bus;

    public function __construct(
        ScanJobService $scanJobService,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        MessageBusInterface $bus
    ) {
        $this->scanJobService = $scanJobService;
        $this->em = $em;
        $this->logger = $logger;
        $this->bus = $bus;
    }

    public function __invoke(ScanJobMessage $message)
    {
        $asset = $this->scanJobService->fetchAsset($message->getAssetId());

        if (!$asset) {
            $this->logger->error('Asset not found for id ' . $message->getAssetId());
            return;
        }

        // 1. Create Scan Job
        $scanJob = new ScanJob();
        $scanJob->setAsset($asset);
        $scanJob->setStatus('pending');
        $scanJob->setDetails('Scan job created, dispatching scanner...');
        $scanJob->setStartedAt(new \DateTimeImmutable());

        $this->em->persist($scanJob);
        $this->em->flush();

        // 2. Dispatch to scanner worker (Nmap or Nikto)
        // ✨ You can choose which scanner here, or use both
        $this->bus->dispatch(new NmapScanMessage($asset->getId(), $scanJob->getId()));

        // OPTIONAL: trigger nikto too
        // $this->bus->dispatch(new NiktoScanMessage($asset->getId(), $scanJob->getId()));

        $this->logger->info("ScanJob {$scanJob->getId()} dispatched to scanner.");
    }
}



