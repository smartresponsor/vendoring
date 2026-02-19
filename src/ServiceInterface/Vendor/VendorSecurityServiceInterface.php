<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface\Vendor;

use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorApiKey;

interface VendorSecurityServiceInterface
{
    public function createKey(Vendor $vendor, string $permissions): string;

    public function rotateKey(VendorApiKey $existingKey): string;

    public function revokeKey(VendorApiKey $apiKey): void;

    public function validateToken(string $plainToken, ?string $permission = null): ?Vendor;

    public function resolveVendorFromAuthHeader(string $authorizationHeader): ?Vendor;
}
