<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\CorrelationContextInterface;

/**
 * In-memory request-scoped holder for the active correlation identifier.
 */
final class CorrelationContext implements CorrelationContextInterface
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
