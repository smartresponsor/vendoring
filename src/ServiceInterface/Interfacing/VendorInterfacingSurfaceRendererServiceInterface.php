<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Interfacing;

use Symfony\Component\HttpFoundation\Response;

/**
 * Renders prepared Vendoring data through an Interfacing template when available.
 *
 * Missing templates are not an application error. The renderer must return the same prepared data
 * as structured JSON so the component remains standalone and host-safe.
 */
interface VendorInterfacingSurfaceRendererServiceInterface
{
    /**
     * @param array<string, mixed> $payload
     * @param list<string>         $templateCandidates
     */
    public function renderOrJson(
        string $surfaceName,
        array $payload,
        array $templateCandidates,
        int $statusCode = Response::HTTP_OK,
    ): Response;
}
