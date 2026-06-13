<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\ValueObject;

use App\Vendoring\ValueObject\VendorTransactionStatusValueObject;
use PHPUnit\Framework\TestCase;

final class VendorTransactionStatusTest extends TestCase
{
    public function testAllReturnsCanonicalTransactionStatusesInOperatorSafeOrder(): void
    {
        self::assertSame([
            VendorTransactionStatusValueObject::PENDING,
            VendorTransactionStatusValueObject::AUTHORIZED,
            VendorTransactionStatusValueObject::SETTLED,
            VendorTransactionStatusValueObject::FAILED,
            VendorTransactionStatusValueObject::CANCELLED,
            VendorTransactionStatusValueObject::REFUNDED,
        ], VendorTransactionStatusValueObject::all());
    }

    public function testOperatorChoicesExposeCanonicalLabelsAndValues(): void
    {
        self::assertSame([
            'Pending' => VendorTransactionStatusValueObject::PENDING,
            'Authorized' => VendorTransactionStatusValueObject::AUTHORIZED,
            'Settled' => VendorTransactionStatusValueObject::SETTLED,
            'Failed' => VendorTransactionStatusValueObject::FAILED,
            'Cancelled' => VendorTransactionStatusValueObject::CANCELLED,
            'Refunded' => VendorTransactionStatusValueObject::REFUNDED,
        ], VendorTransactionStatusValueObject::operatorChoices());
    }

    public function testLabelReturnsHumanReadableCanonicalLabel(): void
    {
        self::assertSame('Cancelled', VendorTransactionStatusValueObject::label(VendorTransactionStatusValueObject::CANCELLED));
        self::assertSame('Settled', VendorTransactionStatusValueObject::label(VendorTransactionStatusValueObject::SETTLED));
    }
}
