<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

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
use App\Vendoring\Entity\VendorCommissionHistory;
use App\Vendoring\Entity\VendorConversation;
use App\Vendoring\Entity\VendorConversationMessage;
use App\Vendoring\Entity\VendorCustomerOrder;
use App\Vendoring\Entity\VendorFavourite;
use App\Vendoring\Entity\VendorGroup;
use App\Vendoring\Entity\VendorLog;
use App\Vendoring\Entity\VendorPayment;
use App\Vendoring\Entity\VendorRememberMeToken;
use App\Vendoring\Entity\VendorShipment;
use App\Vendoring\Entity\VendorWishlist;
use App\Vendoring\Entity\VendorWishlistItem;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipWriteServiceInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class VendorOwnershipWriteService implements VendorOwnershipWriteServiceInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function upsertPayment(Vendor $vendor, VendorPaymentUpsertDTO $dto): VendorPayment
    {
        $repository = $this->entityManager->getRepository(VendorPayment::class);
        $payment = $repository->findOneBy([
            'vendor' => $vendor,
            'providerCode' => $dto->providerCode,
            'methodCode' => $dto->methodCode,
        ]);

        if (!$payment instanceof VendorPayment) {
            $payment = new VendorPayment($vendor, $dto->providerCode, $dto->methodCode, $dto->meta);
            $this->entityManager->persist($payment);
        }

        if (true === $dto->isDefault) {
            foreach ($repository->findBy(['vendor' => $vendor]) as $existingPayment) {
                if ($existingPayment instanceof VendorPayment) {
                    $existingPayment->update(isDefault: false);
                }
            }
        }

        $payment->update(
            externalPaymentId: $dto->externalPaymentId,
            label: $dto->label,
            status: $dto->status,
            isDefault: $dto->isDefault,
            meta: $dto->meta,
        );

        $this->appendLog($vendor, 'vendor_payment_upserted', [
            'providerCode' => $dto->providerCode,
            'methodCode' => $dto->methodCode,
            'status' => $dto->status,
            'isDefault' => $dto->isDefault,
        ]);
        $this->entityManager->flush();

        return $payment;
    }

    public function upsertCommission(Vendor $vendor, VendorCommissionUpsertDTO $dto): VendorCommission
    {
        $repository = $this->entityManager->getRepository(VendorCommission::class);
        $commission = $repository->findOneBy([
            'vendor' => $vendor,
            'code' => $dto->code,
        ]);

        if (!$commission instanceof VendorCommission) {
            $commission = new VendorCommission($vendor, $dto->code, $dto->direction, $dto->ratePercent, $dto->meta);
            $commission->updateConfiguration($dto->direction, $dto->ratePercent, $dto->status, $dto->meta);
            $this->entityManager->persist($commission);
            $this->entityManager->persist(new VendorCommissionHistory($vendor, $commission, null, $dto->ratePercent, $dto->changedByUserId, $dto->reason));
        } else {
            $previousRate = $commission->getRatePercent();
            $commission->updateConfiguration($dto->direction, $dto->ratePercent, $dto->status, $dto->meta);

            if ($previousRate !== $dto->ratePercent) {
                $this->entityManager->persist(new VendorCommissionHistory($vendor, $commission, $previousRate, $dto->ratePercent, $dto->changedByUserId, $dto->reason));
            }
        }

        $this->appendLog($vendor, 'vendor_commission_upserted', [
            'code' => $dto->code,
            'direction' => $dto->direction,
            'ratePercent' => $dto->ratePercent,
            'status' => $dto->status,
        ]);
        $this->entityManager->flush();

        return $commission;
    }

    public function createConversation(Vendor $vendor, VendorConversationUpsertDTO $dto): VendorConversation
    {
        $conversation = new VendorConversation($vendor, $dto->conversationMeta);
        $conversation->update(
            subject: $dto->subject,
            channel: $dto->channel,
            counterpartyType: $dto->counterpartyType,
            counterpartyId: $dto->counterpartyId,
            counterpartyName: $dto->counterpartyName,
            status: $dto->status,
            meta: $dto->conversationMeta,
        );

        $this->entityManager->persist($conversation);

        if (null !== $dto->firstMessageBody) {
            $message = new VendorConversationMessage(
                $conversation,
                $dto->firstMessageDirection ?? 'outbound',
                $dto->firstMessageBody,
                $vendor,
                $dto->externalMessageId,
                $dto->messageMeta,
            );
            $this->entityManager->persist($message);
        }

        $this->appendLog($vendor, 'vendor_conversation_created', [
            'channel' => $dto->channel,
            'status' => $dto->status,
            'counterpartyType' => $dto->counterpartyType,
            'counterpartyId' => $dto->counterpartyId,
        ]);
        $this->entityManager->flush();

        return $conversation;
    }

    public function upsertShipment(Vendor $vendor, VendorShipmentUpsertDTO $dto): VendorShipment
    {
        $repository = $this->entityManager->getRepository(VendorShipment::class);
        $shipment = null;

        if (null !== $dto->externalShipmentId) {
            $shipment = $repository->findOneBy([
                'vendor' => $vendor,
                'externalShipmentId' => $dto->externalShipmentId,
            ]);
        }

        if (!$shipment instanceof VendorShipment) {
            $shipment = new VendorShipment($vendor, $dto->meta);
            $this->entityManager->persist($shipment);
        }

        $shipment->update(
            externalShipmentId: $dto->externalShipmentId,
            carrierCode: $dto->carrierCode,
            methodCode: $dto->methodCode,
            trackingNumber: $dto->trackingNumber,
            status: $dto->status,
            meta: $dto->meta,
        );

        $this->appendLog($vendor, 'vendor_shipment_upserted', [
            'externalShipmentId' => $dto->externalShipmentId,
            'carrierCode' => $dto->carrierCode,
            'methodCode' => $dto->methodCode,
            'status' => $dto->status,
        ]);
        $this->entityManager->flush();

        return $shipment;
    }

    public function upsertGroup(Vendor $vendor, VendorGroupUpsertDTO $dto): VendorGroup
    {
        $repository = $this->entityManager->getRepository(VendorGroup::class);
        $group = $repository->findOneBy(['vendor' => $vendor, 'code' => $dto->code]);

        if (!$group instanceof VendorGroup) {
            $group = new VendorGroup($vendor, $dto->code, $dto->name, $dto->meta);
            $this->entityManager->persist($group);
        }

        $group->update($dto->name, $dto->status, $dto->meta);
        $this->appendLog($vendor, 'vendor_group_upserted', ['code' => $dto->code, 'status' => $dto->status]);
        $this->entityManager->flush();

        return $group;
    }

    public function upsertCategory(Vendor $vendor, VendorCategoryUpsertDTO $dto): VendorCategory
    {
        $repository = $this->entityManager->getRepository(VendorCategory::class);
        $category = $repository->findOneBy(['vendor' => $vendor, 'categoryCode' => $dto->categoryCode]);

        if (!$category instanceof VendorCategory) {
            $category = new VendorCategory($vendor, $dto->categoryCode, $dto->categoryName, $dto->isPrimary);
            $this->entityManager->persist($category);
        }

        if (true === $dto->isPrimary) {
            foreach ($repository->findBy(['vendor' => $vendor]) as $existingCategory) {
                if ($existingCategory instanceof VendorCategory) {
                    $existingCategory->update(isPrimary: false);
                }
            }
        }

        $category->update($dto->categoryName, $dto->isPrimary);
        $this->appendLog($vendor, 'vendor_category_upserted', ['categoryCode' => $dto->categoryCode, 'isPrimary' => $dto->isPrimary]);
        $this->entityManager->flush();

        return $category;
    }

    public function upsertFavourite(Vendor $vendor, VendorFavouriteUpsertDTO $dto): VendorFavourite
    {
        $repository = $this->entityManager->getRepository(VendorFavourite::class);
        $favourite = $repository->findOneBy([
            'vendor' => $vendor,
            'targetType' => $dto->targetType,
            'targetId' => $dto->targetId,
        ]);

        if (!$favourite instanceof VendorFavourite) {
            $favourite = new VendorFavourite($vendor, $dto->targetType, $dto->targetId, $dto->note);
            $this->entityManager->persist($favourite);
        }

        $favourite->update($dto->note);
        $this->appendLog($vendor, 'vendor_favourite_upserted', ['targetType' => $dto->targetType, 'targetId' => $dto->targetId]);
        $this->entityManager->flush();

        return $favourite;
    }

    public function upsertWishlist(Vendor $vendor, VendorWishlistUpsertDTO $dto): VendorWishlist
    {
        $wishlistRepository = $this->entityManager->getRepository(VendorWishlist::class);
        $wishlistItemRepository = $this->entityManager->getRepository(VendorWishlistItem::class);
        $wishlist = $wishlistRepository->findOneBy([
            'vendor' => $vendor,
            'customerReference' => $dto->customerReference,
            'name' => $dto->name,
        ]);

        if (!$wishlist instanceof VendorWishlist) {
            $wishlist = new VendorWishlist($vendor, $dto->customerReference, $dto->name);
            $this->entityManager->persist($wishlist);
        }

        $wishlist->update($dto->name, $dto->status);

        if (null !== $dto->targetType && null !== $dto->targetId) {
            $item = $wishlistItemRepository->findOneBy([
                'wishlist' => $wishlist,
                'targetType' => $dto->targetType,
                'targetId' => $dto->targetId,
            ]);

            if (!$item instanceof VendorWishlistItem) {
                $item = new VendorWishlistItem($wishlist, $dto->targetType, $dto->targetId, $dto->quantity, $dto->note);
                $this->entityManager->persist($item);
            }

            $item->update($dto->quantity, $dto->note);
        }

        $this->appendLog($vendor, 'vendor_wishlist_upserted', [
            'customerReference' => $dto->customerReference,
            'name' => $dto->name,
            'status' => $dto->status,
            'hasItem' => null !== $dto->targetType && null !== $dto->targetId,
        ]);
        $this->entityManager->flush();

        return $wishlist;
    }

    public function upsertCodeStorage(Vendor $vendor, VendorCodeStorageUpsertDTO $dto): VendorCodeStorage
    {
        $repository = $this->entityManager->getRepository(VendorCodeStorage::class);
        $codeStorage = $repository->findOneBy(['code' => $dto->code]);
        $expiresAt = new DateTimeImmutable($dto->expiresAt);

        if (!$codeStorage instanceof VendorCodeStorage) {
            $codeStorage = new VendorCodeStorage($vendor, $dto->code, $dto->purpose, $expiresAt);
            $this->entityManager->persist($codeStorage);
        }

        $codeStorage->update($dto->purpose, $expiresAt, $dto->phone, $dto->isLogin);
        $this->appendLog($vendor, 'vendor_code_storage_upserted', ['code' => $dto->code, 'purpose' => $dto->purpose, 'isLogin' => $dto->isLogin]);
        $this->entityManager->flush();

        return $codeStorage;
    }

    public function upsertRememberMeToken(Vendor $vendor, VendorRememberMeTokenUpsertDTO $dto): VendorRememberMeToken
    {
        $repository = $this->entityManager->getRepository(VendorRememberMeToken::class);
        $token = $repository->findOneBy(['series' => $dto->series]);

        if (!$token instanceof VendorRememberMeToken) {
            $token = new VendorRememberMeToken($vendor, $dto->series, $dto->tokenValue, $dto->providerClass, $dto->username);
            $this->entityManager->persist($token);
        }

        $token->rotate($dto->tokenValue, $dto->providerClass, $dto->username);
        $this->appendLog($vendor, 'vendor_remember_me_token_upserted', ['series' => $dto->series, 'username' => $dto->username]);
        $this->entityManager->flush();

        return $token;
    }

    public function upsertCustomerOrder(Vendor $vendor, VendorCustomerOrderUpsertDTO $dto): VendorCustomerOrder
    {
        $repository = $this->entityManager->getRepository(VendorCustomerOrder::class);
        $order = $repository->findOneBy(['vendor' => $vendor, 'externalOrderId' => $dto->externalOrderId]);

        if (!$order instanceof VendorCustomerOrder) {
            $order = new VendorCustomerOrder($vendor, $dto->externalOrderId, $dto->status, $dto->currency, $dto->grossCents, $dto->netCents, $dto->meta);
            $this->entityManager->persist($order);
        }

        $order->update($dto->orderNumber, $dto->status, $dto->currency, $dto->grossCents, $dto->netCents, $dto->meta);
        $this->appendLog($vendor, 'vendor_customer_order_upserted', ['externalOrderId' => $dto->externalOrderId, 'status' => $dto->status, 'currency' => $dto->currency]);
        $this->entityManager->flush();

        return $order;
    }

    /** @param array<string, mixed> $payload */
    private function appendLog(Vendor $vendor, string $actionName, array $payload): void
    {
        $this->entityManager->persist(new VendorLog($vendor, $actionName, $payload));
    }
}
