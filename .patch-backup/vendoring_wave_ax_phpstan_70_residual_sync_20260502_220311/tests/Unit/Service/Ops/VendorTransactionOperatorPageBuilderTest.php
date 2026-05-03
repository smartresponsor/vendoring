<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service\Ops;

use App\Vendoring\Entity\Vendor\VendorTransactionEntity;
use App\Vendoring\Service\Ops\VendorTransactionOperatorPageBuilderService;
use App\Vendoring\ValueObject\VendorTransactionStatusValueObject;
use PHPUnit\Framework\TestCase;

final class VendorTransactionOperatorPageBuilderTest extends TestCase
{
    public function testRenderIndexUsesCanonicalTransactionStatusesInFallbackOperatorSurface(): void
    {
        $transaction = new VendorTransactionEntity('vendor-1', 'order-1', 'project-1', '10.00');
        $this->forceId($transaction, 42);
        $transaction->setStatus(VendorTransactionStatusValueObject::SETTLED);

        $html = (new VendorTransactionOperatorPageBuilderService())->renderIndex('vendor-1', [$transaction]);

        self::assertStringContainsString('>Settled</td>', $html);
        self::assertStringContainsString('<option value="settled" selected>Settled</option>', $html);
        self::assertStringContainsString('<option value="cancelled">Cancelled</option>', $html);
        self::assertStringNotContainsString('<option value="captured">Captured</option>', $html);
    }

    private function forceId(VendorTransactionEntity $transaction, int $id): void
    {
        $reflection = new \ReflectionObject($transaction);
        $property = $reflection->getProperty('id');
        $property->setValue($transaction, $id);
    }
}
