<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Traffic;

use App\Vendoring\Service\Traffic\VendorWriteRateLimiterService;
use PHPUnit\Framework\TestCase;

final class FileWriteRateLimiterTest extends TestCase
{
    public function testLimiterRejectsRequestAfterConfiguredLimit(): void
    {
        $limiter = new VendorWriteRateLimiterService();
        $scope = 'test_write_scope_' . bin2hex(random_bytes(6));
        $actor = 'actor-1';

        for ($index = 0; $index < 3; ++$index) {
            $decision = $limiter->consume($scope, $actor, 3, 60);
            self::assertTrue($decision->allowed());
        }

        $rejected = $limiter->consume($scope, $actor, 3, 60);

        self::assertFalse($rejected->allowed());
        self::assertSame(0, $rejected->remaining());
        self::assertGreaterThanOrEqual(1, $rejected->retryAfterSeconds());
    }
}
