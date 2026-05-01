<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorApiKeyEntity;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorApiKeyEntity>
 */
interface VendorApiKeyRepositoryInterface extends ObjectRepository
{
    public function findOneByApiKey(string $apiKey): ?VendorApiKeyEntity;

    public function findOneByVendorId(string $vendorId): ?VendorApiKeyEntity;

    public function findActiveByToken(string $tokenHash): ?VendorApiKeyEntity;

    public function save(VendorApiKeyEntity $vendorApiKey, bool $flush = false): void;

    public function remove(VendorApiKeyEntity $vendorApiKey, bool $flush = false): void;
}
