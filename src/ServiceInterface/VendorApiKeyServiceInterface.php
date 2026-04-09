<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\Entity\Vendor;
use App\Entity\VendorApiKey;

/**
 * Vendor API key service is the canonical machine-access seam for Vendoring.
 *
 * It manages vendor-owned API keys and bearer-token resolution without expanding
 * the Vendoring boundary into full human identity or credential ownership.
 */
interface VendorApiKeyServiceInterface
{
    public function createKey(Vendor $vendor, string $permissions): string;

    public function rotateKey(VendorApiKey $existingKey): string;

    public function revokeKey(VendorApiKey $apiKey): void;

    public function validateToken(string $plainToken, ?string $permission = null): ?Vendor;

    public function validateAuthorizationHeader(string $authorizationHeader, ?string $permission = null): ?Vendor;

    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?Vendor;
}
