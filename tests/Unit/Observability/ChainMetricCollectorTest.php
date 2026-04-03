<?php

declare(strict_types=1);

namespace App\Tests\Unit\Observability;

use App\Observability\Service\ChainMetricCollector;
use App\Observability\Service\MetricEmitter;
use PHPUnit\Framework\TestCase;

final class ChainMetricCollectorTest extends TestCase
{
    public function testChainCollectorFansOneIncrementOutToAllCollectors(): void
    {
        $first = new MetricEmitter();
        $second = new MetricEmitter();

        $collector = new ChainMetricCollector([$first, $second]);
        $collector->increment('payout_created_total', ['currency' => 'USD']);

        self::assertCount(1, $first->snapshot());
        self::assertCount(1, $second->snapshot());
        self::assertSame('payout_created_total', $first->snapshot()[0]['name']);
        self::assertSame('USD', $first->snapshot()[0]['tags']['currency']);
    }
}
