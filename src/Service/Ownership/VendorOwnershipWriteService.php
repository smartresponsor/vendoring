<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Ownership;

use App\Vendoring\Entity\Vendor\VendorCategoryEntity;
use App\Vendoring\Entity\Vendor\VendorCodeStorageEntity;
use App\Vendoring\Entity\Vendor\VendorCommissionEntity;
use App\Vendoring\Entity\Vendor\VendorConversationEntity;
use App\Vendoring\Entity\Vendor\VendorCustomerOrderEntity;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorFavouriteEntity;
use App\Vendoring\Entity\Vendor\VendorGroupEntity;
use App\Vendoring\Entity\Vendor\VendorPaymentEntity;
use App\Vendoring\Entity\Vendor\VendorRememberMeTokenEntity;
use App\Vendoring\Entity\Vendor\VendorShipmentEntity;
use App\Vendoring\Entity\Vendor\VendorWishlistEntity;
use App\Vendoring\RepositoryInterface\VendorCategoryRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorCodeStorageRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorCommissionRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorConversationRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorCustomerOrderRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorFavouriteRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorGroupRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorPaymentRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorRememberMeTokenRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorShipmentRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorWishlistRepositoryInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipWriteServiceInterface;

final readonly class VendorOwnershipWriteService implements VendorOwnershipWriteServiceInterface
{
    public function __construct(
        private VendorPaymentRepositoryInterface $paymentRepository,
        private VendorCommissionRepositoryInterface $commissionRepository,
        private VendorConversationRepositoryInterface $conversationRepository,
        private VendorShipmentRepositoryInterface $shipmentRepository,
        private VendorGroupRepositoryInterface $groupRepository,
        private VendorCategoryRepositoryInterface $categoryRepository,
        private VendorFavouriteRepositoryInterface $favouriteRepository,
        private VendorWishlistRepositoryInterface $wishlistRepository,
        private VendorCodeStorageRepositoryInterface $codeStorageRepository,
        private VendorRememberMeTokenRepositoryInterface $rememberMeTokenRepository,
        private VendorCustomerOrderRepositoryInterface $customerOrderRepository,
    ) {
    }

    public function upsertPayment(VendorEntity $vendor, array $data): void
    {
        $payment = $this->findForVendor($this->paymentRepository, $vendor, $data, $this->criteria($data, ['externalPaymentId'], ['providerCode', 'methodCode']));
        if (!$payment instanceof VendorPaymentEntity) {
            $payment = new VendorPaymentEntity($vendor, $this->arrayValue($data, 'meta'));
        }

        $payment->update(
            $this->stringValue($data, 'providerCode'),
            $this->stringValue($data, 'methodCode'),
            $this->nullableStringValue($data, 'externalPaymentId'),
            $this->nullableStringValue($data, 'label'),
            $this->stringValue($data, 'status'),
            $this->boolValue($data, 'isDefault'),
            $this->arrayValue($data, 'meta'),
        );
        $this->paymentRepository->save($payment, true);
    }

    public function upsertCommission(VendorEntity $vendor, array $data): void
    {
        $commission = $this->findForVendor($this->commissionRepository, $vendor, $data, ['code' => $this->stringValue($data, 'code')]);
        if (!$commission instanceof VendorCommissionEntity) {
            $commission = new VendorCommissionEntity(
                $vendor,
                $this->stringValue($data, 'code'),
                $this->stringValue($data, 'direction'),
                $this->stringValue($data, 'ratePercent'),
                $this->arrayValue($data, 'meta'),
            );
        }

        $commission->update(
            $this->stringValue($data, 'direction'),
            $this->stringValue($data, 'ratePercent'),
            $this->stringValue($data, 'status'),
            $this->nullableDateValue($data, 'effectiveFrom'),
            $this->nullableDateValue($data, 'effectiveTo'),
            $this->arrayValue($data, 'meta'),
        );
        $this->commissionRepository->save($commission, true);
    }

    public function createConversation(VendorEntity $vendor, array $data): void
    {
        $conversation = new VendorConversationEntity($vendor, $this->arrayValue($data, 'meta'));
        $conversation->update(
            $this->nullableStringValue($data, 'subject'),
            $this->stringValue($data, 'channel'),
            $this->nullableStringValue($data, 'counterpartyType'),
            $this->nullableStringValue($data, 'counterpartyId'),
            $this->nullableStringValue($data, 'counterpartyName'),
            $this->stringValue($data, 'status'),
            $this->arrayValue($data, 'meta'),
        );
        $this->conversationRepository->save($conversation, true);
    }

    public function upsertShipment(VendorEntity $vendor, array $data): void
    {
        $shipment = $this->findForVendor($this->shipmentRepository, $vendor, $data, $this->criteria($data, ['externalShipmentId']));
        if (!$shipment instanceof VendorShipmentEntity) {
            $shipment = new VendorShipmentEntity($vendor, $this->arrayValue($data, 'meta'));
        }
        $shipment->update(
            $this->nullableStringValue($data, 'externalShipmentId'),
            $this->nullableStringValue($data, 'carrierCode'),
            $this->nullableStringValue($data, 'methodCode'),
            $this->nullableStringValue($data, 'trackingNumber'),
            $this->stringValue($data, 'status'),
            $this->arrayValue($data, 'meta'),
        );
        $this->shipmentRepository->save($shipment, true);
    }

    public function upsertGroup(VendorEntity $vendor, array $data): void
    {
        $group = $this->findForVendor($this->groupRepository, $vendor, $data, ['code' => $this->stringValue($data, 'code')]);
        if (!$group instanceof VendorGroupEntity) {
            $group = new VendorGroupEntity($vendor, $this->stringValue($data, 'code'), $this->stringValue($data, 'name'), $this->arrayValue($data, 'meta'));
        }
        $group->update($this->stringValue($data, 'name'), $this->stringValue($data, 'status'), $this->arrayValue($data, 'meta'));
        $this->groupRepository->save($group, true);
    }

    public function upsertCategory(VendorEntity $vendor, array $data): void
    {
        $category = $this->findForVendor($this->categoryRepository, $vendor, $data, ['categoryCode' => $this->stringValue($data, 'categoryCode')]);
        if (!$category instanceof VendorCategoryEntity) {
            $category = new VendorCategoryEntity($vendor, $this->stringValue($data, 'categoryCode'));
        }
        $category->update($this->nullableStringValue($data, 'categoryName'), $this->boolValue($data, 'isPrimary'));
        $this->categoryRepository->save($category, true);
    }

    public function upsertFavourite(VendorEntity $vendor, array $data): void
    {
        $criteria = [
            'targetType' => $this->stringValue($data, 'targetType'),
            'targetId' => $this->stringValue($data, 'targetId'),
        ];
        $favourite = $this->findForVendor($this->favouriteRepository, $vendor, $data, $criteria);
        if (!$favourite instanceof VendorFavouriteEntity) {
            $favourite = new VendorFavouriteEntity($vendor, $criteria['targetType'], $criteria['targetId']);
        }
        $favourite->update($this->nullableStringValue($data, 'note'));
        $this->favouriteRepository->save($favourite, true);
    }

    public function upsertWishlist(VendorEntity $vendor, array $data): void
    {
        $wishlist = $this->findForVendor($this->wishlistRepository, $vendor, $data, ['customerReference' => $this->stringValue($data, 'customerReference')]);
        if (!$wishlist instanceof VendorWishlistEntity) {
            $wishlist = new VendorWishlistEntity($vendor, $this->stringValue($data, 'customerReference'), $this->stringValue($data, 'name'));
        }
        $wishlist->update($this->stringValue($data, 'name'), $this->stringValue($data, 'status'));
        $this->wishlistRepository->save($wishlist, true);
    }

    public function upsertCodeStorage(VendorEntity $vendor, array $data): void
    {
        $criteria = ['code' => $this->stringValue($data, 'code'), 'purpose' => $this->stringValue($data, 'purpose')];
        $codeStorage = $this->findForVendor($this->codeStorageRepository, $vendor, $data, $criteria);
        if (!$codeStorage instanceof VendorCodeStorageEntity) {
            $codeStorage = new VendorCodeStorageEntity(
                $vendor,
                $criteria['code'],
                $this->nullableStringValue($data, 'phone'),
                $criteria['purpose'],
                $this->boolValue($data, 'isLogin'),
                $this->requiredDateValue($data, 'expiresAt'),
            );
        }
        $consumedAt = $this->nullableDateValue($data, 'consumedAt');
        if ($consumedAt instanceof \DateTimeImmutable) {
            $codeStorage->consume($consumedAt);
        }
        $this->codeStorageRepository->save($codeStorage, true);
    }

    public function upsertRememberMeToken(VendorEntity $vendor, array $data): void
    {
        $token = $this->findForVendor($this->rememberMeTokenRepository, $vendor, $data, ['series' => $this->stringValue($data, 'series')]);
        if (!$token instanceof VendorRememberMeTokenEntity) {
            $token = new VendorRememberMeTokenEntity(
                $vendor,
                $this->stringValue($data, 'series'),
                $this->stringValue($data, 'tokenValue'),
                $this->stringValue($data, 'providerClass'),
                $this->stringValue($data, 'username'),
            );
        } else {
            $token->updateToken($this->stringValue($data, 'tokenValue'));
        }
        $this->rememberMeTokenRepository->save($token, true);
    }

    public function upsertCustomerOrder(VendorEntity $vendor, array $data): void
    {
        $order = $this->findForVendor($this->customerOrderRepository, $vendor, $data, ['externalOrderId' => $this->stringValue($data, 'externalOrderId')]);
        if (!$order instanceof VendorCustomerOrderEntity) {
            $order = new VendorCustomerOrderEntity(
                $vendor,
                $this->stringValue($data, 'externalOrderId'),
                $this->stringValue($data, 'currency'),
                $this->intValue($data, 'grossCents'),
                $this->intValue($data, 'netCents'),
                $this->arrayValue($data, 'meta'),
            );
        }
        $order->update(
            $this->nullableStringValue($data, 'orderNumber'),
            $this->stringValue($data, 'status'),
            $this->stringValue($data, 'currency'),
            $this->intValue($data, 'grossCents'),
            $this->intValue($data, 'netCents'),
            $this->arrayValue($data, 'meta'),
        );
        $this->customerOrderRepository->save($order, true);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $fallbackCriteria
     */
    private function findForVendor(
        VendorPaymentRepositoryInterface|VendorCommissionRepositoryInterface|VendorShipmentRepositoryInterface|VendorGroupRepositoryInterface|VendorCategoryRepositoryInterface|VendorFavouriteRepositoryInterface|VendorWishlistRepositoryInterface|VendorCodeStorageRepositoryInterface|VendorRememberMeTokenRepositoryInterface|VendorCustomerOrderRepositoryInterface $repository,
        VendorEntity $vendor,
        array $data,
        array $fallbackCriteria,
    ): ?object {
        $criteria = ['vendor' => $vendor];
        if (isset($data['id']) && is_int($data['id'])) {
            $criteria['id'] = $data['id'];
        } elseif ([] !== $fallbackCriteria) {
            $criteria += $fallbackCriteria;
        } else {
            return null;
        }

        return $repository->findOneBy($criteria);
    }

    /**
     * @param array<string, mixed> $data
     * @param list<string>         $preferredKeys
     * @param list<string>         $fallbackKeys
     *
     * @return array<string, mixed>
     */
    private function criteria(array $data, array $preferredKeys, array $fallbackKeys = []): array
    {
        $criteria = [];
        foreach ($preferredKeys as $key) {
            $value = $data[$key] ?? null;
            if (null !== $value && '' !== $value) {
                $criteria[$key] = $value;
            }
        }
        if ([] !== $criteria) {
            return $criteria;
        }
        foreach ($fallbackKeys as $key) {
            $criteria[$key] = $data[$key] ?? null;
        }

        return array_filter($criteria, static fn (mixed $value): bool => null !== $value && '' !== $value);
    }

    /** @param array<string, mixed> $data */
    private function stringValue(array $data, string $key): string
    {
        $value = $data[$key] ?? null;
        if (!is_string($value) || '' === $value) {
            throw new \InvalidArgumentException($key.'_invalid');
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    private function nullableStringValue(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;
        if (null === $value) {
            return null;
        }
        if (!is_string($value)) {
            throw new \InvalidArgumentException($key.'_invalid');
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    private function boolValue(array $data, string $key): bool
    {
        $value = $data[$key] ?? null;
        if (!is_bool($value)) {
            throw new \InvalidArgumentException($key.'_invalid');
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    private function intValue(array $data, string $key): int
    {
        $value = $data[$key] ?? null;
        if (!is_int($value)) {
            throw new \InvalidArgumentException($key.'_invalid');
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function arrayValue(array $data, string $key): array
    {
        $value = $data[$key] ?? null;
        if (!is_array($value)) {
            throw new \InvalidArgumentException($key.'_invalid');
        }

        /* @var array<string, mixed> $value */
        return $value;
    }

    /** @param array<string, mixed> $data */
    private function requiredDateValue(array $data, string $key): \DateTimeImmutable
    {
        $value = $this->nullableDateValue($data, $key);
        if (!$value instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException($key.'_invalid');
        }

        return $value;
    }

    /** @param array<string, mixed> $data */
    private function nullableDateValue(array $data, string $key): ?\DateTimeImmutable
    {
        $value = $data[$key] ?? null;
        if (null === $value) {
            return null;
        }
        if (!$value instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException($key.'_invalid');
        }

        return $value;
    }
}
