<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Reliability;

use App\Vendoring\Service\Reliability\FileOutboundCircuitBreaker;
use PHPUnit\Framework\TestCase;

final class FileOutboundCircuitBreakerTest extends TestCase
{
    public function testBreakerOpensAfterThresholdAndShortCircuitsUntilCooldownExpires(): void
    {
        $dir = sys_get_temp_dir() . '/vendoring-breaker-' . bin2hex(random_bytes(4));
        $breaker = new FileOutboundCircuitBreaker($dir);

        $initial = $breaker->currentState('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        self::assertSame('closed', $initial['state']);
        self::assertTrue($initial['allowRequest']);

        $breaker->recordFailure('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        $afterOne = $breaker->currentState('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        self::assertSame('closed', $afterOne['state']);
        self::assertTrue($afterOne['allowRequest']);

        $afterTwo = $breaker->recordFailure('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        self::assertSame('open', $afterTwo['state']);
        self::assertFalse($afterTwo['allowRequest']);

        $current = $breaker->currentState('statement_mail_send', 'tenant-1:vendor-1', 2, 60);
        self::assertSame('open', $current['state']);
        self::assertFalse($current['allowRequest']);
    }

    public function testBreakerResetsAfterSuccess(): void
    {
        $dir = sys_get_temp_dir() . '/vendoring-breaker-' . bin2hex(random_bytes(4));
        $breaker = new FileOutboundCircuitBreaker($dir);

        $breaker->recordFailure('statement_mail_send', 'tenant-1:vendor-1', 1, 60);
        $open = $breaker->currentState('statement_mail_send', 'tenant-1:vendor-1', 1, 60);
        self::assertSame('open', $open['state']);

        $breaker->recordSuccess('statement_mail_send', 'tenant-1:vendor-1');
        $closed = $breaker->currentState('statement_mail_send', 'tenant-1:vendor-1', 1, 60);
        self::assertSame('closed', $closed['state']);
        self::assertTrue($closed['allowRequest']);
    }
}
