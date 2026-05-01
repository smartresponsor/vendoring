<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Observability;

use App\Vendoring\ServiceInterface\Observability\VendorCorrelationContextServiceInterface;

/**
 * In-memory request-scoped holder for the active correlation identifier.
 */
final class VendorCorrelationContextService implements VendorCorrelationContextServiceInterface
{
    private ?string $correlationId = null;

    /**
     * {@inheritdoc}
     */
    public function beginRequest(string $correlationId): void
    {
        $normalized = trim($correlationId);
        $this->correlationId = '' === $normalized ? null : $normalized;
    }

    /**
     * {@inheritdoc}
     */
    public function currentCorrelationId(): ?string
    {
        return $this->correlationId;
    }
}
