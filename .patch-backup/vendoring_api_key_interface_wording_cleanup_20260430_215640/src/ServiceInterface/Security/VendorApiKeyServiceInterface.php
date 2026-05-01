<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface\Security;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorApiKeyEntity;
use Doctrine\ORM\Exception\ManagerException;
use Random\RandomException;

/**
 * VendorEntity API key service is the canonical machine-access seam for Vendoring.
 *
 * It manages vendor-owned API keys and bearer-token resolution without expanding
 * the Vendoring boundary into full human identity or credential ownership.
 */
interface VendorApiKeyServiceInterface
{
    /** @throws ManagerException|RandomException */
    public function createKey(VendorEntity $vendor, string $permissions): string;

    /** @throws ManagerException|RandomException */
    public function rotateKey(VendorApiKeyEntity $existingKey): string;

    /** @throws ManagerException */
    public function revokeKey(VendorApiKeyEntity $apiKey): void;

    /** @throws ManagerException */
    public function validateToken(string $plainToken, ?string $permission = null): ?VendorEntity;

    /** @throws ManagerException */
    public function validateAuthorizationHeader(string $authorizationHeader, ?string $permission = null): ?VendorEntity;

    /** @throws ManagerException */
    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?VendorEntity;
}
