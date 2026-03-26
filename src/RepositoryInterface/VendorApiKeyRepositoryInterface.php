<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\Vendor\VendorApiKey;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorApiKey>
 */
interface VendorApiKeyRepositoryInterface extends ObjectRepository
{
    public function findOneByApiKey(string $apiKey): ?VendorApiKey;

    public function findOneByVendorId(string $vendorId): ?VendorApiKey;

    public function findActiveByToken(string $tokenHash): ?VendorApiKey;

    public function save(VendorApiKey $vendorApiKey, bool $flush = false): void;

    public function remove(VendorApiKey $vendorApiKey, bool $flush = false): void;
}
