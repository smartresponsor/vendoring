<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Ownership;

use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Read-side HTTP HTTP service for vendor ownership inspection.
 *
 * Exposes the ownership projection for a single vendor through a stable JSON contract. The
 * The HTTP service performs no writes and returns `vendor_not_found` when the ownership projection is
 * unavailable for the requested identifier.
 */
final class VendorOwnershipService
{
    public function __construct(private readonly VendorOwnershipProjectionBuilderServiceInterface $ownershipProjectionBuilder)
    {
    }

    /**
     * Show ownership information for one vendor.
     *
     * @param int $vendorId canonical numeric vendor identifier
     *
     * @return JsonResponse JSON payload containing either `vendor_not_found` or a `data` object with
     *                      the ownership projection built by the ownership projection builder
     */
    public function show(int $vendorId): JsonResponse
    {
        $projection = $this->ownershipProjectionBuilder->buildForVendorId($vendorId);

        if (null === $projection) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }
}
