<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Ownership;

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
use App\Vendoring\Entity\Vendor\VendorCommissionHistoryEntity;
use App\Vendoring\Entity\Vendor\VendorConversationEntity;
use App\Vendoring\Entity\Vendor\VendorConversationMessageEntity;
use App\Vendoring\Entity\Vendor\VendorCustomerOrderEntity;
use App\Vendoring\Entity\Vendor\VendorFavouriteEntity;
use App\Vendoring\Entity\Vendor\VendorGroupEntity;
use App\Vendoring\Entity\Vendor\VendorLogEntity;
use App\Vendoring\Entity\Vendor\VendorPaymentEntity;
use App\Vendoring\Entity\Vendor\VendorRememberMeTokenEntity;
use App\Vendoring\Entity\Vendor\VendorShipmentEntity;
use App\Vendoring\Entity\Vendor\VendorWishlistEntity;
use App\Vendoring\Entity\Vendor\VendorWishlistItemEntity;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipWriteServiceInterface;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

final readonly class VendorOwnershipWriteService implements VendorOwnershipWriteServiceInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function upsertPayment(VendorEntity $vendor, VendorPaymentUpsertDTO $dto): VendorPaymentEntity
    {
        $repository = $this->entityManager->getRepository(VendorPaymentEntity::class);
        $payment = $repository->findOneBy([
            'vendor' => $vendor,
            'providerCode' => $dto->providerCode,
            'methodCode' => $dto->methodCode,
        ]);

        if (!$payment instanceof VendorPaymentEntity) {
            $payment = new VendorPaymentEntity($vendor, $dto->providerCode, $dto->methodCode, $dto->meta);
            $this->entityManager->persist($payment);
        }

        if (true === $dto->isDefault) {
            foreach ($repository->findBy(['vendor' => $vendor]) as $existingPayment) {
                if ($existingPayment instanceof VendorPaymentEntity) {
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
            'status' => $status,
            'isDefault' => $dto->isDefault,
        ]);
        $this->entityManager->flush();

        return $payment;
    }

    public function upsertCommission(VendorEntity $vendor, VendorCommissionUpsertDTO $dto): VendorCommissionEntity
    {
        $status = $dto->status ?? 'draft';
        $repository = $this->entityManager->getRepository(VendorCommissionEntity::class);
        $commission = $repository->findOneBy([
            'vendor' => $vendor,
            'code' => $dto->code,
        ]);

        if (!$commission instanceof VendorCommissionEntity) {
            $commission = new VendorCommissionEntity($vendor, $dto->code, $dto->direction, $dto->ratePercent, $dto->meta);
            $commission->updateConfiguration($dto->direction, $dto->ratePercent, $status, $dto->meta);
            $this->entityManager->persist($commission);
            $this->entityManager->persist(new VendorCommissionHistoryEntity($vendor, $commission, null, $dto->ratePercent, $dto->changedByUserId, $dto->reason));
        } else {
            $previousRate = $commission->getRatePercent();
            $commission->updateConfiguration($dto->direction, $dto->ratePercent, $status, $dto->meta);

            if ($previousRate !== $dto->ratePercent) {
                $this->entityManager->persist(new VendorCommissionHistoryEntity($vendor, $commission, $previousRate, $dto->ratePercent, $dto->changedByUserId, $dto->reason));
            }
        }

        $this->appendLog($vendor, 'vendor_commission_upserted', [
            'code' => $dto->code,
            'direction' => $dto->direction,
            'ratePercent' => $dto->ratePercent,
            'status' => $status,
        ]);
        $this->entityManager->flush();

        return $commission;
    }

    public function createConversation(VendorEntity $vendor, VendorConversationUpsertDTO $dto): VendorConversationEntity
    {
        $conversation = new VendorConversationEntity($vendor, $dto->conversationMeta);
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
            $message = new VendorConversationMessageEntity(
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
            'status' => $status,
            'counterpartyType' => $dto->counterpartyType,
            'counterpartyId' => $dto->counterpartyId,
        ]);
        $this->entityManager->flush();

        return $conversation;
    }

    public function upsertShipment(VendorEntity $vendor, VendorShipmentUpsertDTO $dto): VendorShipmentEntity
    {
        $repository = $this->entityManager->getRepository(VendorShipmentEntity::class);
        $shipment = null;

        if (null !== $dto->externalShipmentId) {
            $shipment = $repository->findOneBy([
                'vendor' => $vendor,
                'externalShipmentId' => $dto->externalShipmentId,
            ]);
        }

        if (!$shipment instanceof VendorShipmentEntity) {
            $shipment = new VendorShipmentEntity($vendor, $dto->meta);
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
            'status' => $status,
        ]);
        $this->entityManager->flush();

        return $shipment;
    }

    public function upsertGroup(VendorEntity $vendor, VendorGroupUpsertDTO $dto): VendorGroupEntity
    {
        $repository = $this->entityManager->getRepository(VendorGroupEntity::class);
        $group = $repository->findOneBy(['vendor' => $vendor, 'code' => $dto->code]);

        if (!$group instanceof VendorGroupEntity) {
            $group = new VendorGroupEntity($vendor, $dto->code, $dto->name, $dto->meta);
            $this->entityManager->persist($group);
        }

        $group->update($dto->name, $dto->status, $dto->meta);
        $this->appendLog($vendor, 'vendor_group_upserted', ['code' => $dto->code, 'status' => $dto->status]);
        $this->entityManager->flush();

        return $group;
    }

    public function upsertCategory(VendorEntity $vendor, VendorCategoryUpsertDTO $dto): VendorCategoryEntity
    {
        $repository = $this->entityManager->getRepository(VendorCategoryEntity::class);
        $category = $repository->findOneBy(['vendor' => $vendor, 'categoryCode' => $dto->categoryCode]);

        if (!$category instanceof VendorCategoryEntity) {
            $category = new VendorCategoryEntity($vendor, $dto->categoryCode, $dto->categoryName, $dto->isPrimary);
            $this->entityManager->persist($category);
        }

        if (true === $dto->isPrimary) {
            foreach ($repository->findBy(['vendor' => $vendor]) as $existingCategory) {
                $existingCategory->update(isPrimary: false);
            }
        }

        $category->update($dto->categoryName, $dto->isPrimary);
        $this->appendLog($vendor, 'vendor_category_upserted', ['categoryCode' => $dto->categoryCode, 'isPrimary' => $dto->isPrimary]);
        $this->entityManager->flush();

        return $category;
    }

    public function upsertFavourite(VendorEntity $vendor, VendorFavouriteUpsertDTO $dto): VendorFavouriteEntity
    {
        $repository = $this->entityManager->getRepository(VendorFavouriteEntity::class);
        $favourite = $repository->findOneBy([
            'vendor' => $vendor,
            'targetType' => $dto->targetType,
            'targetId' => $dto->targetId,
        ]);

        if (!$favourite instanceof VendorFavouriteEntity) {
            $favourite = new VendorFavouriteEntity($vendor, $dto->targetType, $dto->targetId, $dto->note);
            $this->entityManager->persist($favourite);
        }

        $favourite->update($dto->note);
        $this->appendLog($vendor, 'vendor_favourite_upserted', ['targetType' => $dto->targetType, 'targetId' => $dto->targetId]);
        $this->entityManager->flush();

        return $favourite;
    }

    public function upsertWishlist(VendorEntity $vendor, VendorWishlistUpsertDTO $dto): VendorWishlistEntity
    {
        $wishlistRepository = $this->entityManager->getRepository(VendorWishlistEntity::class);
        $wishlistItemRepository = $this->entityManager->getRepository(VendorWishlistItemEntity::class);
        $wishlist = $wishlistRepository->findOneBy([
            'vendor' => $vendor,
            'customerReference' => $dto->customerReference,
            'name' => $dto->name,
        ]);

        if (!$wishlist instanceof VendorWishlistEntity) {
            $wishlist = new VendorWishlistEntity($vendor, $dto->customerReference, $dto->name);
            $this->entityManager->persist($wishlist);
        }

        $wishlist->update($dto->name, $dto->status);

        if (null !== $dto->targetType && null !== $dto->targetId) {
            $item = $wishlistItemRepository->findOneBy([
                'wishlist' => $wishlist,
                'targetType' => $dto->targetType,
                'targetId' => $dto->targetId,
            ]);

            if (!$item instanceof VendorWishlistItemEntity) {
                $item = new VendorWishlistItemEntity($wishlist, $dto->targetType, $dto->targetId, $dto->quantity, $dto->note);
                $this->entityManager->persist($item);
            }

            $item->update($dto->quantity, $dto->note);
        }

        $this->appendLog($vendor, 'vendor_wishlist_upserted', [
            'customerReference' => $dto->customerReference,
            'name' => $dto->name,
            'status' => $status,
            'hasItem' => null !== $dto->targetType && null !== $dto->targetId,
        ]);
        $this->entityManager->flush();

        return $wishlist;
    }

    public function upsertCodeStorage(VendorEntity $vendor, VendorCodeStorageUpsertDTO $dto): VendorCodeStorageEntity
    {
        $repository = $this->entityManager->getRepository(VendorCodeStorageEntity::class);
        $codeStorage = $repository->findOneBy(['code' => $dto->code]);
        $expiresAt = new DateTimeImmutable($dto->expiresAt);

        if (!$codeStorage instanceof VendorCodeStorageEntity) {
            $codeStorage = new VendorCodeStorageEntity($vendor, $dto->code, $dto->purpose, $expiresAt);
            $this->entityManager->persist($codeStorage);
        }

        $codeStorage->update($dto->purpose, $expiresAt, $dto->phone, $dto->isLogin);
        $this->appendLog($vendor, 'vendor_code_storage_upserted', ['code' => $dto->code, 'purpose' => $dto->purpose, 'isLogin' => $dto->isLogin]);
        $this->entityManager->flush();

        return $codeStorage;
    }

    public function upsertRememberMeToken(VendorEntity $vendor, VendorRememberMeTokenUpsertDTO $dto): VendorRememberMeTokenEntity
    {
        $repository = $this->entityManager->getRepository(VendorRememberMeTokenEntity::class);
        $token = $repository->findOneBy(['series' => $dto->series]);

        if (!$token instanceof VendorRememberMeTokenEntity) {
            $token = new VendorRememberMeTokenEntity($vendor, $dto->series, $dto->tokenValue, $dto->providerClass, $dto->username);
            $this->entityManager->persist($token);
        }

        $token->rotate($dto->tokenValue, $dto->providerClass, $dto->username);
        $this->appendLog($vendor, 'vendor_remember_me_token_upserted', ['series' => $dto->series, 'username' => $dto->username]);
        $this->entityManager->flush();

        return $token;
    }

    public function upsertCustomerOrder(VendorEntity $vendor, VendorCustomerOrderUpsertDTO $dto): VendorCustomerOrderEntity
    {
        $repository = $this->entityManager->getRepository(VendorCustomerOrderEntity::class);
        $order = $repository->findOneBy(['vendor' => $vendor, 'externalOrderId' => $dto->externalOrderId]);

        if (!$order instanceof VendorCustomerOrderEntity) {
            $order = new VendorCustomerOrderEntity($vendor, $dto->externalOrderId, $dto->status, $dto->currency, $dto->grossCents, $dto->netCents, $dto->meta);
            $this->entityManager->persist($order);
        }

        $order->update($dto->orderNumber, $dto->status, $dto->currency, $dto->grossCents, $dto->netCents, $dto->meta);
        $this->appendLog($vendor, 'vendor_customer_order_upserted', ['externalOrderId' => $dto->externalOrderId, 'status' => $status, 'currency' => $dto->currency]);
        $this->entityManager->flush();

        return $order;
    }

    /** @param array<string, mixed> $payload */
    private function appendLog(VendorEntity $vendor, string $actionName, array $payload): void
    {
        $this->entityManager->persist(new VendorLogEntity($vendor, $actionName, $payload));
    }
}
