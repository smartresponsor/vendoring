<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Runtime\Profile;

use App\Vendoring\ServiceInterface\Profile\VendorProfileProjectionBuilderServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final readonly class VendorProfileShowService
{
    public function __construct(
        private VendorProfileProjectionBuilderServiceInterface $profileProjectionBuilder,
    ) {
    }

    public function __invoke(int|string $vendorId): JsonResponse
    {
        $normalizedVendorId = $this->vendorId($vendorId);

        if (null === $normalizedVendorId) {
            return new JsonResponse(['error' => 'vendor_identifier_required'], 422);
        }

        $projection = $this->profileProjectionBuilder->buildForVendorId($normalizedVendorId);

        if (null === $projection) {
            return new JsonResponse(['error' => 'vendor_not_found'], 404);
        }

        return new JsonResponse(['data' => $projection->toArray()], 200);
    }

    private function vendorId(int|string $vendorId): ?int
    {
        if (is_int($vendorId)) {
            return $vendorId > 0 ? $vendorId : null;
        }

        $normalized = trim($vendorId);

        if ('' === $normalized || !ctype_digit($normalized)) {
            return null;
        }

        $normalizedVendorId = (int) $normalized;

        return $normalizedVendorId > 0 ? $normalizedVendorId : null;
    }
}
