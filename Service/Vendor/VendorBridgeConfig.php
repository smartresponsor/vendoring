<?php
declare(strict_types=1);

namespace App\Bridge\Vendor;

final class BridgeConfig
{
    public function __construct(
        public readonly int $outboxBatchLimit = 100,
        public readonly int $maxAttempts = 10,
        public readonly bool $logAudit = true
    ) {}
}
