<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Command\Vendor\SyncVendorLedgerCommand;

final class VendorSyncSmokeTest extends TestCase
{
    public function testCommandHasVendorId(): void
    {
        $cmd = new SyncVendorLedgerCommand(42);
        $this->assertSame(42, $cmd->vendorId);
    }
}
