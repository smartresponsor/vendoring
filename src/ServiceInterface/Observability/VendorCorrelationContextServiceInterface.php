<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Observability;

/**
 * Request-scoped contract for storing and reading the active correlation identifier.
 */
interface VendorCorrelationContextServiceInterface
{
    /**
     * Begin or replace the current request correlation scope.
     *
     * Empty input is normalized by the implementation and may clear the active value.
     *
     * @param string $correlationId External or generated correlation identifier.
     */
    public function beginRequest(string $correlationId): void;

    /**
     * Read the currently active correlation identifier.
     *
     * @return string|null Active correlation identifier, or null when no request scope exists.
     */
    public function currentCorrelationId(): ?string;
}
