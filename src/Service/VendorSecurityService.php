<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorApiKey;
use App\ServiceInterface\VendorApiKeyServiceInterface;
use App\ServiceInterface\VendorSecurityServiceInterface;

/**
 * Backward-compatible wrapper around VendorApiKeyService.
 *
 * The canonical machine-access seam is VendorApiKeyService. Keep this class only
 * as a transitional alias while older callers still request VendorSecurityService.
 */
final class VendorSecurityService implements VendorSecurityServiceInterface
{
    public function __construct(private readonly VendorApiKeyServiceInterface $apiKeyService)
    {
    }

    public function createKey(Vendor $vendor, string $permissions): string
    {
        return $this->apiKeyService->createKey($vendor, $permissions);
    }

    public function rotateKey(VendorApiKey $existingKey): string
    {
        return $this->apiKeyService->rotateKey($existingKey);
    }

    public function revokeKey(VendorApiKey $apiKey): void
    {
        $this->apiKeyService->revokeKey($apiKey);
    }

    public function validateToken(string $plainToken, ?string $permission = null): ?Vendor
    {
        return $this->apiKeyService->validateToken($plainToken, $permission);
    }

    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?Vendor
    {
        return $this->apiKeyService->resolveVendorFromAuthHeader($authorizationHeader);
    }
}
