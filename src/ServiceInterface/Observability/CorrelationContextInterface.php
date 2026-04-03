<?php

declare(strict_types=1);

namespace App\ServiceInterface\Observability;

interface CorrelationContextInterface
{
    public function beginRequest(string $correlationId): void;

    public function currentCorrelationId(): ?string;
}
