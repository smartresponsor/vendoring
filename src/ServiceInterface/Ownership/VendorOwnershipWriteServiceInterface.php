<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Ownership;

use App\Vendoring\Entity\Vendor\VendorEntity;

interface VendorOwnershipWriteServiceInterface
{
    /** @param array<string, mixed> $data */
    public function upsertPayment(VendorEntity $vendor, array $data): void;

    /** @param array<string, mixed> $data */
    public function upsertCommission(VendorEntity $vendor, array $data): void;

    /** @param array<string, mixed> $data */
    public function createConversation(VendorEntity $vendor, array $data): void;

    /** @param array<string, mixed> $data */
    public function upsertShipment(VendorEntity $vendor, array $data): void;

    /** @param array<string, mixed> $data */
    public function upsertGroup(VendorEntity $vendor, array $data): void;

    /** @param array<string, mixed> $data */
    public function upsertCategory(VendorEntity $vendor, array $data): void;

    /** @param array<string, mixed> $data */
    public function upsertFavourite(VendorEntity $vendor, array $data): void;

    /** @param array<string, mixed> $data */
    public function upsertWishlist(VendorEntity $vendor, array $data): void;

    /** @param array<string, mixed> $data */
    public function upsertCodeStorage(VendorEntity $vendor, array $data): void;

    /** @param array<string, mixed> $data */
    public function upsertRememberMeToken(VendorEntity $vendor, array $data): void;

    /** @param array<string, mixed> $data */
    public function upsertCustomerOrder(VendorEntity $vendor, array $data): void;
}
