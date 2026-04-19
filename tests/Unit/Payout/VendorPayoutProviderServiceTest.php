<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Payout;

use App\Vendoring\DTO\Payout\VendorPayoutTransferDTO;
use App\Vendoring\Service\Payout\VendorPayoutProviderService;
use PHPUnit\Framework\TestCase;

final class VendorPayoutProviderServiceTest extends TestCase
{
    public function testTransferReturnsSuccessfulLocalBridgePayload(): void
    {
        $payload = (new VendorPayoutProviderService())->transfer(new VendorPayoutTransferDTO(
            'tenant-1',
            'vendor-1',
            'bank',
            'iban-123',
            95.5,
            'USD',
        ));

        self::assertTrue($payload['ok']);
        self::assertSame('tenant-1', $payload['tenantId']);
        self::assertSame('vendor-1', $payload['vendorId']);
        self::assertSame('bank', $payload['provider']);
        self::assertSame('iban-123', $payload['accountRef']);
        self::assertSame(95.5, $payload['amount']);
        self::assertSame('USD', $payload['currency']);
        self::assertNull($payload['error']);
        self::assertIsString($payload['ref']);
        self::assertMatchesRegularExpression('/^bank_payout_[a-f0-9]{8}$/', $payload['ref']);
    }

    public function testTransferEmbedsProviderIntoGeneratedReference(): void
    {
        $payload = (new VendorPayoutProviderService())->transfer(new VendorPayoutTransferDTO(
            'tenant-1',
            'vendor-1',
            'stripe',
            'acct_123',
            10.0,
            'EUR',
        ));

        self::assertIsString($payload['ref']);
        self::assertStringStartsWith('stripe_payout_', $payload['ref']);
    }
}
