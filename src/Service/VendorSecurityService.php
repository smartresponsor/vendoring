<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorApiKey;
use App\Vendoring\ServiceInterface\VendorApiKeyServiceInterface;
use App\Vendoring\ServiceInterface\VendorSecurityServiceInterface;

/**
 * Backward-compatible wrapper around VendorApiKeyService.
 *
 * The canonical machine-access seam is VendorApiKeyService. Keep this class only
 * as a transitional alias while older callers still request VendorSecurityService.
 */
final readonly class VendorSecurityService implements VendorSecurityServiceInterface
{
    public function __construct(private VendorApiKeyServiceInterface $apiKeyService) {}

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

    public function validateAuthorizationHeader(string $authorizationHeader, ?string $permission = null): ?Vendor
    {
        return $this->apiKeyService->validateAuthorizationHeader($authorizationHeader, $permission);
    }

    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?Vendor
    {
        return $this->apiKeyService->resolveVendorFromAuthHeader($authorizationHeader);
    }
}
