<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Ownership;

use App\Vendoring\DTO\Ownership\VendorCategoryUpsertDTO;
use App\Vendoring\DTO\Ownership\VendorCodeStorageUpsertDTO;
use App\Vendoring\DTO\Ownership\VendorCommissionUpsertDTO;
use App\Vendoring\DTO\Ownership\VendorConversationUpsertDTO;
use App\Vendoring\DTO\Ownership\VendorCustomerOrderUpsertDTO;
use App\Vendoring\DTO\Ownership\VendorFavouriteUpsertDTO;
use App\Vendoring\DTO\Ownership\VendorGroupUpsertDTO;
use App\Vendoring\DTO\Ownership\VendorPaymentUpsertDTO;
use App\Vendoring\DTO\Ownership\VendorRememberMeTokenUpsertDTO;
use App\Vendoring\DTO\Ownership\VendorShipmentUpsertDTO;
use App\Vendoring\DTO\Ownership\VendorWishlistUpsertDTO;

interface VendorOwnershipWriteRequestResolverServiceInterface
{
    /** @param array<string, mixed> $payload */
    public function resolvePayment(int $vendorId, array $payload): VendorPaymentUpsertDTO;

    /** @param array<string, mixed> $payload */
    public function resolveCommission(int $vendorId, array $payload): VendorCommissionUpsertDTO;

    /** @param array<string, mixed> $payload */
    public function resolveConversation(int $vendorId, array $payload): VendorConversationUpsertDTO;

    /** @param array<string, mixed> $payload */
    public function resolveShipment(int $vendorId, array $payload): VendorShipmentUpsertDTO;

    /** @param array<string, mixed> $payload */
    public function resolveGroup(int $vendorId, array $payload): VendorGroupUpsertDTO;

    /** @param array<string, mixed> $payload */
    public function resolveCategory(int $vendorId, array $payload): VendorCategoryUpsertDTO;

    /** @param array<string, mixed> $payload */
    public function resolveFavourite(int $vendorId, array $payload): VendorFavouriteUpsertDTO;

    /** @param array<string, mixed> $payload */
    public function resolveWishlist(int $vendorId, array $payload): VendorWishlistUpsertDTO;

    /** @param array<string, mixed> $payload */
    public function resolveCodeStorage(int $vendorId, array $payload): VendorCodeStorageUpsertDTO;

    /** @param array<string, mixed> $payload */
    public function resolveRememberMeToken(int $vendorId, array $payload): VendorRememberMeTokenUpsertDTO;

    /** @param array<string, mixed> $payload */
    public function resolveCustomerOrder(int $vendorId, array $payload): VendorCustomerOrderUpsertDTO;
}
