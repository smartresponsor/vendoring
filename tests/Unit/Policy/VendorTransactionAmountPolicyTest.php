<?php

declare(strict_types=1);

namespace App\Tests\Unit\Policy;

use App\Service\Policy\VendorTransactionAmountPolicy;
use PHPUnit\Framework\TestCase;

final class VendorTransactionAmountPolicyTest extends TestCase
{
    public function testNormalizeFormatsPositiveNumericAmount(): void
    {
        $policy = new VendorTransactionAmountPolicy();

        self::assertSame('10.50', $policy->normalize(' 10.5 '));
        self::assertSame('12.35', $policy->normalize('12.345'));
    }

    public function testNormalizeRejectsNonNumericAmount(): void
    {
        $policy = new VendorTransactionAmountPolicy();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('amount_not_numeric');

        $policy->normalize('ten');
    }

    public function testNormalizeRejectsZeroOrNegativeAmount(): void
    {
        $policy = new VendorTransactionAmountPolicy();

        try {
            $policy->normalize('0');
            self::fail('Expected exception for zero amount.');
        } catch (\InvalidArgumentException $exception) {
            self::assertSame('amount_not_positive', $exception->getMessage());
        }

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('amount_not_positive');

        $policy->normalize('-5');
    }
}
