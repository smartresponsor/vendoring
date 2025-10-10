<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class VendorCLICommandsTest extends TestCase
{
    public function testCommandsExist(): void
    {
        $this->assertTrue(class_exists(App\Command\VendorSyncCommand::class));
        $this->assertTrue(class_exists(App\Command\VendorRepairOutboxCommand::class));
        $this->assertTrue(class_exists(App\Command\VendorStatsCommand::class));
    }
}
