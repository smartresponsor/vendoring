<?php
declare(strict_types=1);

namespace App\Handler\Vendor\Sync;

use App\Command\Vendor\SyncVendorCRMCommand;
use App\Repository\Vendor\VendorRepository;
use App\Service\Vendor\CrmService;

final class SyncVendorCRMCommandHandler
{
    public function __construct(
        private readonly VendorRepository $vendors,
        private readonly CrmService $crm
    ) {}

    public function __invoke(SyncVendorCRMCommand $cmd): void
    {
        $vendor = $this->vendors->find($cmd->vendorId);
        if (!$vendor) { return; }
        $this->crm->registerVendor($vendor);
    }
}
