<?php
declare(strict_types=1);

namespace App\Controller\Vendor;

use App\Command\Vendor\SyncVendorLedgerCommand;
use App\Command\Vendor\SyncVendorCRMCommand;
use App\Command\Vendor\SyncVendorProductsCommand;
use App\CommandBus\Vendor\VendorSyncCommandBus;
use App\Service\Vendor\VendorSyncStatsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class VendorSyncController
{
    public function __construct(
        private readonly VendorSyncCommandBus $bus,
        private readonly VendorSyncStatsService $stats
    ) {}

    #[Route('/api/vendor/sync/{vendorId}', name: 'api_vendor_sync_all', methods: ['POST'])]
    public function syncAll(int $vendorId): JsonResponse
    {
        $this->bus->dispatch(new SyncVendorLedgerCommand($vendorId));
        $this->bus->dispatch(new SyncVendorCRMCommand($vendorId));
        $this->bus->dispatch(new SyncVendorProductsCommand($vendorId));
        return new JsonResponse(['ok' => true, 'vendorId' => $vendorId]);
    }

    #[Route('/api/vendor/sync-stats', name: 'api_vendor_sync_stats', methods: ['GET'])]
    public function stats(Request $req): JsonResponse
    {
        return new JsonResponse($this->stats->snapshot());
    }
}
