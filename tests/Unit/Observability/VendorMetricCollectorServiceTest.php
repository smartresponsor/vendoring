<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Observability;

use App\Vendoring\Service\Observability\VendorMetricCollectorService;
use App\Vendoring\Service\Observability\VendorMetricEmitterService;
use PHPUnit\Framework\TestCase;

final class VendorMetricCollectorServiceTest extends TestCase
{
    public function testMetricCollectorFansOneIncrementOutToAllCollectors(): void
    {
        $first = new VendorMetricEmitterService();
        $second = new VendorMetricEmitterService();

        $collector = new VendorMetricCollectorService([$first, $second]);
        $collector->increment('payout_created_total', ['currency' => 'USD']);

        self::assertCount(1, $first->snapshot());
        self::assertCount(1, $second->snapshot());
        self::assertSame('payout_created_total', $first->snapshot()[0]['name']);
        self::assertSame('USD', $first->snapshot()[0]['tags']['currency']);
    }
}
