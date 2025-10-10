<?php
declare(strict_types=1);

namespace App\Command\Vendor;

final class SyncVendorLedgerCommand
{
    public function __construct(public readonly int $vendorId) {}
}
