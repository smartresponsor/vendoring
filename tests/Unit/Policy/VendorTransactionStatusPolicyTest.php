<?php

declare(strict_types=1);

namespace Tests\Unit\Policy;

use App\Vendoring\Service\Policy\VendorTransactionStatusPolicy;
use PHPUnit\Framework\TestCase;

final class VendorTransactionStatusPolicyTest extends TestCase
{
    public function testNormalizeCanonicalizesCaseAndWhitespace(): void
    {
        $policy = new VendorTransactionStatusPolicy();

        self::assertSame('authorized', $policy->normalize('  AUTHORIZED  '));
    }

    public function testCanonicalStatusCatalogIsUsedForPending(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../src/Service/Policy/VendorTransactionStatusPolicy.php');

        self::assertIsString($source);
        self::assertStringContainsString('VendorTransactionStatus::PENDING', $source);
        self::assertStringContainsString('VendorTransactionStatus::AUTHORIZED', $source);
        self::assertStringContainsString('VendorTransactionStatus::REFUNDED', $source);
    }

    public function testNormalizeRejectsUnknownStatusViaTransitionGuard(): void
    {
        $policy = new VendorTransactionStatusPolicy();

        self::assertFalse($policy->canTransition('pending', 'unknown-status'));
        self::assertFalse($policy->canTransition('unknown-status', 'authorized'));
    }
}
