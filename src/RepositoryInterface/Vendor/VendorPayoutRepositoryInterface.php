<?php

declare(strict_types=1);

namespace App\Vendoring\RepositoryInterface\Vendor;

use App\Vendoring\Entity\Vendor\VendorPayoutEntity;
use App\Vendoring\Entity\Vendor\VendorPayoutItemEntity;
use Doctrine\DBAL\Exception;
use JsonException;

interface VendorPayoutRepositoryInterface
{
    /** @throws Exception|JsonException */
    public function insert(VendorPayoutEntity $payout): void;

    /** @throws Exception */
    public function insertItem(VendorPayoutItemEntity $item): void;

    /** @throws Exception */
    public function byId(string $id): ?VendorPayoutEntity;

    /**
     * @return list<VendorPayoutItemEntity>
     */
    /**
     * @return list<VendorPayoutItemEntity>
     * @throws Exception
     */
    public function items(string $payoutId): array;

    /**
     * @param array<string, mixed> $meta
     * @throws Exception
     */
    public function markProcessed(string $id, string $processedAt, array $meta = []): void;

    /**
     * @param array<string, mixed> $meta
     * @throws Exception
     */
    public function markFailed(string $id, string $processedAt, array $meta = []): void;
}
