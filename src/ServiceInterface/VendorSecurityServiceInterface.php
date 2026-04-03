<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\Entity\Vendor;
use App\Entity\VendorApiKey;

/**
 * Backward-compatible bridge to the canonical VendorApiKeyServiceInterface.
 *
 * Keep this interface only during the transition away from misleading
 * "security" naming. Vendoring credentials and human identity remain outside
 * this boundary.
 */
interface VendorSecurityServiceInterface extends VendorApiKeyServiceInterface
{
    public function createKey(Vendor $vendor, string $permissions): string;

    public function rotateKey(VendorApiKey $existingKey): string;

    public function revokeKey(VendorApiKey $apiKey): void;

    public function validateToken(string $plainToken, ?string $permission = null): ?Vendor;

    public function validateAuthorizationHeader(string $authorizationHeader, ?string $permission = null): ?Vendor;

    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?Vendor;
}
