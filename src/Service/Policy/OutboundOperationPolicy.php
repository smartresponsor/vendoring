<?php

declare(strict_types=1);

namespace App\Service\Policy;

use App\ServiceInterface\Policy\OutboundOperationPolicyInterface;

/**
 * Canonical reliability policy registry for outbound operations.
 *
 * This service centralizes timeout, retry, and breaker thresholds for outbound
 * transport calls so that callers can keep runtime behavior deterministic and
 * machine-readable.
 */
final class OutboundOperationPolicy implements OutboundOperationPolicyInterface
{
    /**
     * @var array<string, array{
     *   timeoutSeconds:int,
     *   maxAttempts:int,
     *   retryable:bool,
     *   failureMode:string,
     *   breakerThreshold:int,
     *   cooldownSeconds:int
     * }>
     */
    private const POLICIES = [
        'statement_mail_send' => [
            'timeoutSeconds' => 10,
            'maxAttempts' => 1,
            'retryable' => false,
            'failureMode' => 'fail_fast',
            'breakerThreshold' => 2,
            'cooldownSeconds' => 60,
        ],
        'payout_transfer' => [
            'timeoutSeconds' => 15,
            'maxAttempts' => 2,
            'retryable' => true,
            'failureMode' => 'retry_then_fail',
            'breakerThreshold' => 3,
            'cooldownSeconds' => 120,
        ],
        'vendor_crm_register' => [
            'timeoutSeconds' => 12,
            'maxAttempts' => 2,
            'retryable' => true,
            'failureMode' => 'retry_then_fail',
            'breakerThreshold' => 3,
            'cooldownSeconds' => 120,
        ],
        'vendor_webhook_consume' => [
            'timeoutSeconds' => 8,
            'maxAttempts' => 1,
            'retryable' => false,
            'failureMode' => 'fail_fast',
            'breakerThreshold' => 5,
            'cooldownSeconds' => 30,
        ],
    ];

    public function forOperation(string $operation): array
    {
        $policy = self::POLICIES[$operation] ?? [
            'timeoutSeconds' => 10,
            'maxAttempts' => 1,
            'retryable' => false,
            'failureMode' => 'fail_fast',
            'breakerThreshold' => 2,
            'cooldownSeconds' => 60,
        ];

        return [
            'operation' => $operation,
            'timeoutSeconds' => $policy['timeoutSeconds'],
            'maxAttempts' => $policy['maxAttempts'],
            'retryable' => $policy['retryable'],
            'failureMode' => $policy['failureMode'],
            'breakerThreshold' => $policy['breakerThreshold'],
            'cooldownSeconds' => $policy['cooldownSeconds'],
        ];
    }
}
