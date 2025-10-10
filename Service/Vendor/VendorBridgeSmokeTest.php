<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Bridge\Vendor\BridgeConfig;

final class BridgeSmokeTest extends TestCase
{
    public function testConfigDefaults(): void
    {
        $cfg = new BridgeConfig();
        $this->assertSame(100, $cfg->outboxBatchLimit);
        $this->assertSame(10, $cfg->maxAttempts);
        $this->assertTrue($cfg->logAudit);
    }
}
