<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Ownership;

use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class VendorOwnershipHttpService
{
    public function __construct(
        private VendorOwnershipProjectionBuilderServiceInterface $projectionBuilder,
    ) {
    }

    public function show(int $vendorId): JsonResponse
    {
        $projection = $this->projectionBuilder->buildForVendorId($vendorId);

        if (null === $projection) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        return new JsonResponse([
            'data' => $projection->toArray(),
        ]);
    }
}
