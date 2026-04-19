<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\ValueObject;

use App\Vendoring\ValueObject\VendorTransactionStatus;
use PHPUnit\Framework\TestCase;

final class VendorTransactionStatusTest extends TestCase
{
    public function testAllReturnsCanonicalTransactionStatusesInOperatorSafeOrder(): void
    {
        self::assertSame([
            VendorTransactionStatus::PENDING,
            VendorTransactionStatus::AUTHORIZED,
            VendorTransactionStatus::SETTLED,
            VendorTransactionStatus::FAILED,
            VendorTransactionStatus::CANCELLED,
            VendorTransactionStatus::REFUNDED,
        ], VendorTransactionStatus::all());
    }

    public function testOperatorChoicesExposeCanonicalLabelsAndValues(): void
    {
        self::assertSame([
            'Pending' => VendorTransactionStatus::PENDING,
            'Authorized' => VendorTransactionStatus::AUTHORIZED,
            'Settled' => VendorTransactionStatus::SETTLED,
            'Failed' => VendorTransactionStatus::FAILED,
            'Cancelled' => VendorTransactionStatus::CANCELLED,
            'Refunded' => VendorTransactionStatus::REFUNDED,
        ], VendorTransactionStatus::operatorChoices());
    }

    public function testLabelReturnsHumanReadableCanonicalLabel(): void
    {
        self::assertSame('Cancelled', VendorTransactionStatus::label(VendorTransactionStatus::CANCELLED));
        self::assertSame('Settled', VendorTransactionStatus::label(VendorTransactionStatus::SETTLED));
    }
}
