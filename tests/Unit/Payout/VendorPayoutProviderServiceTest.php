<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Payout;

use App\Vendoring\DTO\Payout\VendorPayoutTransferDTO;
use App\Vendoring\Exception\Payout\VendorPayoutProviderNotConfiguredException;
use App\Vendoring\Service\Payout\VendorNullPayoutProviderService;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for VendorNullPayoutProviderService.
 *
 * The null-object replaces the previous silent stub that returned ok=true
 * without executing any real transfer. The null-object throws explicitly
 * so that unconfigured payout attempts surface immediately at call time
 * rather than producing ghost transactions.
 */
final class VendorPayoutProviderServiceTest extends TestCase
{
    public function testTransferThrowsWhenNoProviderIsConfigured(): void
    {
        $this->expectException(VendorPayoutProviderNotConfiguredException::class);

        (new VendorNullPayoutProviderService())->transfer(new VendorPayoutTransferDTO(
            tenantId: 'tenant-1',
            vendorId: 'vendor-1',
            provider: 'bank',
            accountRef: 'iban-123',
            amount: 95.5,
            currency: 'USD',
        ));
    }

    public function testTransferExceptionMessageIncludesProviderName(): void
    {
        try {
            (new VendorNullPayoutProviderService())->transfer(new VendorPayoutTransferDTO(
                tenantId: 'tenant-1',
                vendorId: 'vendor-1',
                provider: 'stripe',
                accountRef: 'acct_123',
                amount: 10.0,
                currency: 'EUR',
            ));
            self::fail('Expected VendorPayoutProviderNotConfiguredException was not thrown.');
        } catch (VendorPayoutProviderNotConfiguredException $exception) {
            self::assertStringContainsString('stripe', $exception->getMessage());
        }
    }

    public function testNullProviderImplementsPayoutProviderInterface(): void
    {
        self::assertInstanceOf(
            \App\Vendoring\ServiceInterface\Payout\VendorPayoutProviderServiceInterface::class,
            new VendorNullPayoutProviderService(),
        );
    }
}
