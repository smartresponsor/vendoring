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
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorCategory;
use App\Vendoring\Entity\VendorCodeStorage;
use App\Vendoring\Entity\VendorCommission;
use App\Vendoring\Entity\VendorConversation;
use App\Vendoring\Entity\VendorCustomerOrder;
use App\Vendoring\Entity\VendorFavourite;
use App\Vendoring\Entity\VendorGroup;
use App\Vendoring\Entity\VendorPayment;
use App\Vendoring\Entity\VendorRememberMeToken;
use App\Vendoring\Entity\VendorShipment;
use App\Vendoring\Entity\VendorWishlist;

interface VendorOwnershipWriteServiceInterface
{
    public function upsertPayment(Vendor $vendor, VendorPaymentUpsertDTO $dto): VendorPayment;

    public function upsertCommission(Vendor $vendor, VendorCommissionUpsertDTO $dto): VendorCommission;

    public function createConversation(Vendor $vendor, VendorConversationUpsertDTO $dto): VendorConversation;

    public function upsertShipment(Vendor $vendor, VendorShipmentUpsertDTO $dto): VendorShipment;

    public function upsertGroup(Vendor $vendor, VendorGroupUpsertDTO $dto): VendorGroup;

    public function upsertCategory(Vendor $vendor, VendorCategoryUpsertDTO $dto): VendorCategory;

    public function upsertFavourite(Vendor $vendor, VendorFavouriteUpsertDTO $dto): VendorFavourite;

    public function upsertWishlist(Vendor $vendor, VendorWishlistUpsertDTO $dto): VendorWishlist;

    public function upsertCodeStorage(Vendor $vendor, VendorCodeStorageUpsertDTO $dto): VendorCodeStorage;

    public function upsertRememberMeToken(Vendor $vendor, VendorRememberMeTokenUpsertDTO $dto): VendorRememberMeToken;

    public function upsertCustomerOrder(Vendor $vendor, VendorCustomerOrderUpsertDTO $dto): VendorCustomerOrder;
}
