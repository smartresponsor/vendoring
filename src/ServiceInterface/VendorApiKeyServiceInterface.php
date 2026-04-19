<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorApiKey;
use Doctrine\ORM\Exception\ManagerException;
use Random\RandomException;

/**
 * Vendor API key service is the canonical machine-access seam for Vendoring.
 *
 * It manages vendor-owned API keys and bearer-token resolution without expanding
 * the Vendoring boundary into full human identity or credential ownership.
 */
interface VendorApiKeyServiceInterface
{
    /** @throws ManagerException|RandomException */
    public function createKey(Vendor $vendor, string $permissions): string;

    /** @throws ManagerException|RandomException */
    public function rotateKey(VendorApiKey $existingKey): string;

    /** @throws ManagerException */
    public function revokeKey(VendorApiKey $apiKey): void;

    /** @throws ManagerException */
    public function validateToken(string $plainToken, ?string $permission = null): ?Vendor;

    /** @throws ManagerException */
    public function validateAuthorizationHeader(string $authorizationHeader, ?string $permission = null): ?Vendor;

    /** @throws ManagerException */
    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?Vendor;
}
