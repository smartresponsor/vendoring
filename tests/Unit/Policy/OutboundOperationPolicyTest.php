<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Policy;

use App\Vendoring\Service\Policy\OutboundOperationPolicy;
use PHPUnit\Framework\TestCase;

final class OutboundOperationPolicyTest extends TestCase
{
    public function testStatementMailSendPolicyIsFailFastAndNonRetryable(): void
    {
        $policy = (new OutboundOperationPolicy())->forOperation('statement_mail_send');

        self::assertSame('statement_mail_send', $policy['operation']);
        self::assertFalse($policy['retryable']);
        self::assertSame(1, $policy['maxAttempts']);
        self::assertSame('fail_fast', $policy['failureMode']);
        self::assertSame(2, $policy['breakerThreshold']);
    }

    public function testPayoutTransferPolicyAllowsRetry(): void
    {
        $policy = (new OutboundOperationPolicy())->forOperation('payout_transfer');

        self::assertTrue($policy['retryable']);
        self::assertSame(2, $policy['maxAttempts']);
        self::assertSame('retry_then_fail', $policy['failureMode']);
    }
}
