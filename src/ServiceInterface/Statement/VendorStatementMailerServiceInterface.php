<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Statement;

/**
 * Write-side contract for outbound vendor statement delivery.
 *
 * Implementations send one statement email and return a stable operational result
 * payload that callers can inspect for transport outcome and runtime protection
 * metadata such as retryability and circuit-breaker state.
 */
interface VendorStatementMailerServiceInterface
{
    /**
     * Send one statement email for the supplied tenant/vendor period.
     *
     * @return array{
     *   ok:bool,
     *   message:string,
     *   tenantId:string,
     *   vendorId:string,
     *   email:string,
     *   pdfPath:string,
     *   periodLabel:string,
     *   attached:bool,
     *   retryable:bool,
     *   timeoutSeconds:int,
     *   maxAttempts:int,
     *   attemptCount:int,
     *   failureMode:string,
     *   circuitState:string,
     *   errorClass?:string,
     *   errorMessage?:string
     * }
     */
    public function send(string $tenantId, string $vendorId, string $email, string $pdfPath, string $periodLabel): array;
}
