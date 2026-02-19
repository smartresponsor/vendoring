<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\RepositoryInterface\Vendor;

use App\Entity\Vendor\VendorApiKey;

interface VendorApiKeyRepositoryInterface
{
    public function save(VendorApiKey $apiKey, bool $flush = false): void;

    public function findActiveByToken(string $tokenHash): ?VendorApiKey;
}
