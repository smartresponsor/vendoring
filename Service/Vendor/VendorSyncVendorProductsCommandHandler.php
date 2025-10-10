<?php
declare(strict_types=1);

namespace App\Handler\Vendor\Sync;

use App\Command\Vendor\SyncVendorProductsCommand;
use App\Repository\Vendor\VendorRepository;
use App\Service\Vendor\ProductCatalogService;

final class SyncVendorProductsCommandHandler
{
    public function __construct(
        private readonly VendorRepository $vendors,
        private readonly ProductCatalogService $catalog
    ) {}

    public function __invoke(SyncVendorProductsCommand $cmd): void
    {
        $vendor = $this->vendors->find($cmd->vendorId);
        if (!$vendor) { return; }
        $this->catalog->assignToVendor($vendor);
    }
}
