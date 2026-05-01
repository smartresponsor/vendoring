<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Policy;

/**
 * Read-side contract for outbound reliability policy lookup.
 *
 * Implementations return stable policy metadata for one named outbound operation
 * so that callers can apply timeout, retry, and circuit-breaker behavior without
 * embedding those rules inline in transport code.
 */
interface VendorOutboundOperationPolicyServiceInterface
{
    /**
     * Resolve the canonical runtime policy for one outbound operation.
     *
     * @param string $operation Stable operation name such as `statement_mail_send`
     *                          or `payout_transfer`.
     *
     * @return array{
     *   operation:string,
     *   timeoutSeconds:int,
     *   maxAttempts:int,
     *   retryable:bool,
     *   failureMode:string,
     *   breakerThreshold:int,
     *   cooldownSeconds:int
     * }
     */
    public function forOperation(string $operation): array;
}
