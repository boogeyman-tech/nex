<?php

namespace App\Modules\ScanManagement\MessageHandler;

use App\Modules\ScanManagement\Message\ScanJobMessage;
use App\Modules\ScanManagement\Service\ScanJobService;
use App\Modules\VulnerabilityDetection\Service\NiktoScanService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ScanJobHandler
{
    private ScanJobService $scanJobService;
    private NiktoScanService $niktoScanService;
    private EntityManagerInterface $em;

    public function __construct(ScanJobService $scanJobService, NiktoScanService $niktoScanService, EntityManagerInterface $em)
    {
        $this->scanJobService = $scanJobService;
        $this->niktoScanService = $niktoScanService;
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
            $scanJob->setStatus('running'); // Set to running before attempting scan

            try {
                $scanJob = $this->niktoScanService->scan($asset, $scanJob);
                $scanJob->setStatus('completed');
            } catch (\Exception $e) {
                $scanJob->setStatus('failed');
                $scanJob->setErrorMessage($e->getMessage());
            }
        }

        $scanJob->setFinishedAt(new \DateTimeImmutable());
        $this->em->persist($scanJob);
        $this->em->flush();
    }
}
