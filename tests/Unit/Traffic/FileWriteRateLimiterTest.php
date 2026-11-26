<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Traffic;

use App\Vendoring\Service\Traffic\VendorWriteRateLimiterService;
use App\Vendoring\ValueObject\Traffic\VendorWriteRateLimitDecisionValueObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for VendorWriteRateLimiterService.
 *
 * The service uses a pluggable backend callable strategy — no hard dependency
 * on symfony/rate-limiter or any specific cache package is required in Vendoring.
 * The runtime configures a private local store; a host may inject a shared backend.
 */
final class FileWriteRateLimiterTest extends TestCase
{
    public function testConsumeDeniesRequestWhenNoBackendOrStoreConfigured(): void
    {
        $service = new VendorWriteRateLimiterService();
        $decision = $service->consume('transaction.create', 'vendor-1', 10, 60);

        self::assertInstanceOf(VendorWriteRateLimitDecisionValueObject::class, $decision);
        self::assertFalse($decision->allowed());
    }

    public function testLocalStoreEnforcesWindowAcrossInstancesAndIsolatesActors(): void
    {
        $dir = sys_get_temp_dir().'/vendoring-rate-test-'.bin2hex(random_bytes(12));
        try {
            $first = new VendorWriteRateLimiterService(stateDir: $dir);
            $second = new VendorWriteRateLimiterService(stateDir: $dir);
            self::assertTrue($first->consume('create', 'actor-a', 2, 60)->allowed());
            self::assertTrue($second->consume('create', 'actor-a', 2, 60)->allowed());
            $denied = $first->consume('create', 'actor-a', 2, 60);
            self::assertFalse($denied->allowed());
            self::assertGreaterThan(0, $denied->retryAfterSeconds());
            self::assertTrue($second->consume('create', 'actor-b', 2, 60)->allowed());
            self::assertTrue($second->consume('update', 'actor-a', 2, 60)->allowed());
            $files = glob($dir.'/*.json') ?: [];
            self::assertCount(3, $files);
            foreach ($files as $file) {
                file_put_contents($file, 'corrupt');
            }
            self::assertFalse($first->consume('create', 'actor-a', 2, 60)->allowed());
        } finally {
            foreach (glob($dir.'/*') ?: [] as $file) {
                unlink($file);
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    public function testConsumeAllowsRequestWhenScopeIsBlank(): void
    {
        $service = new VendorWriteRateLimiterService();

        $decision = $service->consume('', 'vendor-1', 10, 60);
        self::assertTrue($decision->allowed());
    }

    public function testConsumeAllowsRequestWhenActorIsBlank(): void
    {
        $service = new VendorWriteRateLimiterService();

        $decision = $service->consume('transaction.create', '', 10, 60);
        self::assertTrue($decision->allowed());
    }

    public function testConsumeAllowsRequestWhenLimitIsZero(): void
    {
        $service = new VendorWriteRateLimiterService();

        $decision = $service->consume('transaction.create', 'vendor-1', 0, 60);
        self::assertTrue($decision->allowed());
    }

    public function testConsumeUsesInjectedBackendFactory(): void
    {
        $capturedBucketId = null;

        $backend = static function (string $bucketId, int $limit) use (&$capturedBucketId): array {
            $capturedBucketId = $bucketId;

            return ['allowed' => true, 'remaining' => $limit - 1, 'retryAfterSeconds' => 0];
        };

        $service = new VendorWriteRateLimiterService($backend);
        $decision = $service->consume('payout.transfer', 'vendor-42', 10, 60);

        self::assertTrue($decision->allowed());
        self::assertSame(9, $decision->remaining());
        self::assertSame('payout.transfer.vendor-42', $capturedBucketId);
    }

    public function testConsumeReturnsRejectedDecisionFromBackend(): void
    {
        $backend = static fn (string $bucketId, int $limit): array => [
            'allowed' => false,
            'remaining' => 0,
            'retryAfterSeconds' => 30,
        ];

        $service = new VendorWriteRateLimiterService($backend);
        $decision = $service->consume('transaction.create', 'vendor-1', 10, 60);

        self::assertFalse($decision->allowed());
        self::assertSame(0, $decision->remaining());
        self::assertSame(30, $decision->retryAfterSeconds());
    }

    public function testConsumePassesScopedKeyToBackend(): void
    {
        $receivedKey = null;

        $backend = static function (string $bucketId, int $limit) use (&$receivedKey): array {
            $receivedKey = $bucketId;

            return ['allowed' => true, 'remaining' => 5, 'retryAfterSeconds' => 0];
        };

        $service = new VendorWriteRateLimiterService($backend);
        $service->consume('statement.send', 'tenant-1', 5, 60);

        self::assertSame('statement.send.tenant-1', $receivedKey);
    }
}
