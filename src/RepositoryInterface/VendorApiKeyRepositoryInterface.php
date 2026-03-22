<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\Vendor\VendorApiKey;

/**
 * App-level repository contract for vendor API keys.
 *
 * Important: this interface intentionally does not redeclare Doctrine's generic
 * find() signature, because narrowing that method breaks compatibility with
 * Doctrine repository proxies and ServiceEntityRepository.
 */
interface VendorApiKeyRepositoryInterface
{
    public function findOneByApiKey(string $apiKey): ?VendorApiKey;

    public function findOneByVendorId(string $vendorId): ?VendorApiKey;

    public function save(VendorApiKey $vendorApiKey): void;

    public function remove(VendorApiKey $vendorApiKey): void;
}
