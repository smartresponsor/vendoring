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
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorCategoryEntity;
use App\Vendoring\Entity\Vendor\VendorCodeStorageEntity;
use App\Vendoring\Entity\Vendor\VendorCommissionEntity;
use App\Vendoring\Entity\Vendor\VendorConversationEntity;
use App\Vendoring\Entity\Vendor\VendorCustomerOrderEntity;
use App\Vendoring\Entity\Vendor\VendorFavouriteEntity;
use App\Vendoring\Entity\Vendor\VendorGroupEntity;
use App\Vendoring\Entity\Vendor\VendorPaymentEntity;
use App\Vendoring\Entity\Vendor\VendorRememberMeTokenEntity;
use App\Vendoring\Entity\Vendor\VendorShipmentEntity;
use App\Vendoring\Entity\Vendor\VendorWishlistEntity;

interface VendorOwnershipWriteServiceInterface
{
    public function upsertPayment(VendorEntity $vendor, VendorPaymentUpsertDTO $dto): VendorPaymentEntity;

    public function upsertCommission(VendorEntity $vendor, VendorCommissionUpsertDTO $dto): VendorCommissionEntity;

    public function createConversation(VendorEntity $vendor, VendorConversationUpsertDTO $dto): VendorConversationEntity;

    public function upsertShipment(VendorEntity $vendor, VendorShipmentUpsertDTO $dto): VendorShipmentEntity;

    public function upsertGroup(VendorEntity $vendor, VendorGroupUpsertDTO $dto): VendorGroupEntity;

    public function upsertCategory(VendorEntity $vendor, VendorCategoryUpsertDTO $dto): VendorCategoryEntity;

    public function upsertFavourite(VendorEntity $vendor, VendorFavouriteUpsertDTO $dto): VendorFavouriteEntity;

    public function upsertWishlist(VendorEntity $vendor, VendorWishlistUpsertDTO $dto): VendorWishlistEntity;

    public function upsertCodeStorage(VendorEntity $vendor, VendorCodeStorageUpsertDTO $dto): VendorCodeStorageEntity;

    public function upsertRememberMeToken(VendorEntity $vendor, VendorRememberMeTokenUpsertDTO $dto): VendorRememberMeTokenEntity;

    public function upsertCustomerOrder(VendorEntity $vendor, VendorCustomerOrderUpsertDTO $dto): VendorCustomerOrderEntity;
}
