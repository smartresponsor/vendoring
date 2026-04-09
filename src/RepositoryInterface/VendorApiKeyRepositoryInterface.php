<?php

declare(strict_types=1);

namespace App\RepositoryInterface;

use App\Entity\VendorApiKey;
use Doctrine\Persistence\ObjectRepository;

/**
 * @extends ObjectRepository<VendorApiKey>
 */
interface VendorApiKeyRepositoryInterface extends ObjectRepository
{
    /**
     * Returns the requested persisted state.
     */
    public function findOneByApiKey(string $apiKey): ?VendorApiKey;

    /**
     * Returns the requested persisted state.
     */
    public function findOneByVendorId(string $vendorId): ?VendorApiKey;

    /**
     * Returns the requested persisted state.
     */
    public function findActiveByToken(string $tokenHash): ?VendorApiKey;

    /**
     * Persists the requested record.
     */
    public function save(VendorApiKey $vendorApiKey, bool $flush = false): void;

    /**
     * Removes the requested persisted state.
     */
    public function remove(VendorApiKey $vendorApiKey, bool $flush = false): void;
}
