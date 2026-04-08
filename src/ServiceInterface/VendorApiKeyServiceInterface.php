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
    /**
     * Creates the requested resource from the supplied input.
     */
    public function createKey(Vendor $vendor, string $permissions): string;

    /**
     * Rotates the requested credential material.
     */
    public function rotateKey(VendorApiKey $existingKey): string;

    /**
     * Executes the revoke key operation for this runtime surface.
     */
    public function revokeKey(VendorApiKey $apiKey): void;

    /**
     * Executes the validate token operation for this runtime surface.
     */
    public function validateToken(string $plainToken, ?string $permission = null): ?Vendor;

    /**
     * Executes the validate authorization header operation for this runtime surface.
     */
    public function validateAuthorizationHeader(string $authorizationHeader, ?string $permission = null): ?Vendor;

    /**
     * Resolves the requested runtime subject.
     */
    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?Vendor;
}
