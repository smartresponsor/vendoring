<?php
declare(strict_types=1);

namespace App\Service\Vendor;

final class VendorSyncStatsService
{
    public function __construct() {}

    public function snapshot(): array
    {
        // placeholder metrics
        return [
            'events_processed' => 0,
            'success' => 0,
            'failed' => 0,
            'retry_ratio' => 0.0,
        ];
    }
}
