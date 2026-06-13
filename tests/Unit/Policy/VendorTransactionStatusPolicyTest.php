<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Policy;

use App\Vendoring\Service\Policy\VendorTransactionStatusPolicyService;
use PHPUnit\Framework\TestCase;

final class VendorTransactionStatusPolicyTest extends TestCase
{
    public function testNormalizeCanonicalizesCaseAndWhitespace(): void
    {
        $policy = new VendorTransactionStatusPolicyService();

        self::assertSame('authorized', $policy->normalize('  AUTHORIZED  '));
    }

    public function testCanonicalStatusCatalogIsUsedForPending(): void
    {
        $source = file_get_contents(__DIR__ . '/../../../src/Service/Policy/VendorTransactionStatusPolicyService.php');

        self::assertIsString($source);
        self::assertStringContainsString('VendorTransactionStatusValueObject::PENDING', $source);
        self::assertStringContainsString('VendorTransactionStatusValueObject::AUTHORIZED', $source);
        self::assertStringContainsString('VendorTransactionStatusValueObject::REFUNDED', $source);
    }

    public function testNormalizeRejectsUnknownStatusViaTransitionGuard(): void
    {
        $policy = new VendorTransactionStatusPolicyService();

        self::assertFalse($policy->canTransition('pending', 'unknown-status'));
        self::assertFalse($policy->canTransition('unknown-status', 'authorized'));
    }
}
