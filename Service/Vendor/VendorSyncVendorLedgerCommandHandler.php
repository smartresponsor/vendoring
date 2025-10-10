<?php
declare(strict_types=1);

namespace App\Handler\Vendor\Sync;

use App\Command\Vendor\SyncVendorLedgerCommand;
use App\Repository\Vendor\VendorRepository;
use App\Service\Vendor\LedgerService;

final class SyncVendorLedgerCommandHandler
{
    public function __construct(
        private readonly VendorRepository $vendors,
        private readonly LedgerService $ledger
    ) {}

    public function __invoke(SyncVendorLedgerCommand $cmd): void
    {
        $vendor = $this->vendors->find($cmd->vendorId);
        if (!$vendor) { return; }
        $this->ledger->createVendorAccount($vendor);
    }
}
