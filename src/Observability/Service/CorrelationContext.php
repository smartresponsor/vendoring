<?php

declare(strict_types=1);

namespace App\Observability\Service;

use App\ServiceInterface\Observability\CorrelationContextInterface;

final class CorrelationContext implements CorrelationContextInterface
{
    private ?string $correlationId = null;

    public function beginRequest(string $correlationId): void
    {
        $normalized = trim($correlationId);
        $this->correlationId = '' === $normalized ? null : $normalized;
    }

    public function currentCorrelationId(): ?string
    {
        return $this->correlationId;
    }
}
