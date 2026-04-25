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
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipWriteRequestResolverInterface;
use DateTimeImmutable;
use InvalidArgumentException;

final class VendorOwnershipWriteRequestResolver implements VendorOwnershipWriteRequestResolverInterface
{
    public function resolvePayment(int $vendorId, array $payload): VendorPaymentUpsertDTO
    {
        return new VendorPaymentUpsertDTO(
            vendorId: $vendorId,
            providerCode: $this->requiredString($payload, 'providerCode'),
            methodCode: $this->requiredString($payload, 'methodCode'),
            externalPaymentId: $this->nullableString($payload, 'externalPaymentId'),
            label: $this->nullableString($payload, 'label'),
            status: $this->nullableString($payload, 'status') ?? 'active',
            isDefault: $this->nullableBool($payload, 'isDefault'),
            meta: $this->normalizeObject($payload['meta'] ?? []),
        );
    }

    public function resolveCommission(int $vendorId, array $payload): VendorCommissionUpsertDTO
    {
        return new VendorCommissionUpsertDTO(
            vendorId: $vendorId,
            code: $this->requiredString($payload, 'code'),
            direction: $this->requiredString($payload, 'direction'),
            ratePercent: $this->requiredString($payload, 'ratePercent'),
            status: $this->nullableString($payload, 'status') ?? 'active',
            changedByUserId: $this->nullableInt($payload, 'changedByUserId'),
            reason: $this->nullableString($payload, 'reason'),
            meta: $this->normalizeObject($payload['meta'] ?? []),
        );
    }

    public function resolveConversation(int $vendorId, array $payload): VendorConversationUpsertDTO
    {
        return new VendorConversationUpsertDTO(
            vendorId: $vendorId,
            subject: $this->nullableString($payload, 'subject'),
            channel: $this->nullableString($payload, 'channel') ?? 'internal',
            counterpartyType: $this->nullableString($payload, 'counterpartyType'),
            counterpartyId: $this->nullableString($payload, 'counterpartyId'),
            counterpartyName: $this->nullableString($payload, 'counterpartyName'),
            status: $this->nullableString($payload, 'status') ?? 'open',
            conversationMeta: $this->normalizeObject($payload['conversationMeta'] ?? []),
            firstMessageBody: $this->nullableString($payload, 'firstMessageBody'),
            firstMessageDirection: $this->nullableString($payload, 'firstMessageDirection') ?? 'outbound',
            externalMessageId: $this->nullableString($payload, 'externalMessageId'),
            messageMeta: $this->normalizeObject($payload['messageMeta'] ?? []),
        );
    }

    public function resolveShipment(int $vendorId, array $payload): VendorShipmentUpsertDTO
    {
        return new VendorShipmentUpsertDTO(
            vendorId: $vendorId,
            externalShipmentId: $this->nullableString($payload, 'externalShipmentId'),
            carrierCode: $this->nullableString($payload, 'carrierCode'),
            methodCode: $this->nullableString($payload, 'methodCode'),
            trackingNumber: $this->nullableString($payload, 'trackingNumber'),
            status: $this->nullableString($payload, 'status') ?? 'pending',
            meta: $this->normalizeObject($payload['meta'] ?? []),
        );
    }

    public function resolveGroup(int $vendorId, array $payload): VendorGroupUpsertDTO
    {
        return new VendorGroupUpsertDTO(
            vendorId: $vendorId,
            code: $this->requiredString($payload, 'code'),
            name: $this->requiredString($payload, 'name'),
            status: $this->nullableString($payload, 'status') ?? 'active',
            meta: $this->normalizeObject($payload['meta'] ?? []),
        );
    }

    public function resolveCategory(int $vendorId, array $payload): VendorCategoryUpsertDTO
    {
        return new VendorCategoryUpsertDTO(
            vendorId: $vendorId,
            categoryCode: $this->requiredString($payload, 'categoryCode'),
            categoryName: $this->nullableString($payload, 'categoryName'),
            isPrimary: $this->nullableBool($payload, 'isPrimary') ?? false,
        );
    }

    public function resolveFavourite(int $vendorId, array $payload): VendorFavouriteUpsertDTO
    {
        return new VendorFavouriteUpsertDTO(
            vendorId: $vendorId,
            targetType: $this->requiredString($payload, 'targetType'),
            targetId: $this->requiredString($payload, 'targetId'),
            note: $this->nullableString($payload, 'note'),
        );
    }

    public function resolveWishlist(int $vendorId, array $payload): VendorWishlistUpsertDTO
    {
        return new VendorWishlistUpsertDTO(
            vendorId: $vendorId,
            customerReference: $this->requiredString($payload, 'customerReference'),
            name: $this->requiredString($payload, 'name'),
            status: $this->nullableString($payload, 'status') ?? 'active',
            targetType: $this->nullableString($payload, 'targetType'),
            targetId: $this->nullableString($payload, 'targetId'),
            quantity: $this->nullableInt($payload, 'quantity') ?? 1,
            note: $this->nullableString($payload, 'note'),
        );
    }

    public function resolveCodeStorage(int $vendorId, array $payload): VendorCodeStorageUpsertDTO
    {
        return new VendorCodeStorageUpsertDTO(
            vendorId: $vendorId,
            code: $this->requiredString($payload, 'code'),
            purpose: $this->requiredString($payload, 'purpose'),
            expiresAt: $this->requiredDateTime($payload, 'expiresAt')->format(DATE_ATOM),
            phone: $this->nullableString($payload, 'phone'),
            isLogin: $this->nullableBool($payload, 'isLogin') ?? false,
        );
    }

    public function resolveRememberMeToken(int $vendorId, array $payload): VendorRememberMeTokenUpsertDTO
    {
        return new VendorRememberMeTokenUpsertDTO(
            vendorId: $vendorId,
            series: $this->requiredString($payload, 'series'),
            tokenValue: $this->requiredString($payload, 'tokenValue'),
            providerClass: $this->requiredString($payload, 'providerClass'),
            username: $this->requiredString($payload, 'username'),
        );
    }

    public function resolveCustomerOrder(int $vendorId, array $payload): VendorCustomerOrderUpsertDTO
    {
        return new VendorCustomerOrderUpsertDTO(
            vendorId: $vendorId,
            externalOrderId: $this->requiredString($payload, 'externalOrderId'),
            status: $this->nullableString($payload, 'status') ?? 'placed',
            currency: strtoupper($this->requiredString($payload, 'currency')),
            grossCents: $this->requiredInt($payload, 'grossCents'),
            netCents: $this->requiredInt($payload, 'netCents'),
            orderNumber: $this->nullableString($payload, 'orderNumber'),
            meta: $this->normalizeObject($payload['meta'] ?? []),
        );
    }

    /** @param array<string, mixed> $payload */
    private function requiredString(array $payload, string $field): string
    {
        $value = $this->nullableString($payload, $field);

        if (null === $value || '' === trim($value)) {
            throw new InvalidArgumentException(sprintf('%s_required', $field));
        }

        return $value;
    }

    /** @param array<string, mixed> $payload */
    private function nullableString(array $payload, string $field): ?string
    {
        $value = $payload[$field] ?? null;

        if (null === $value) {
            return null;
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException(sprintf('%s_must_be_string', $field));
        }

        $normalized = trim((string) $value);

        return '' === $normalized ? null : $normalized;
    }

    /** @param array<string, mixed> $payload */
    private function nullableInt(array $payload, string $field): ?int
    {
        $value = $payload[$field] ?? null;

        if (null === $value || '' === $value) {
            return null;
        }

        if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
            throw new InvalidArgumentException(sprintf('%s_must_be_int', $field));
        }

        return (int) $value;
    }

    /** @param array<string, mixed> $payload */
    private function requiredInt(array $payload, string $field): int
    {
        $value = $this->nullableInt($payload, $field);

        if (null === $value) {
            throw new InvalidArgumentException(sprintf('%s_required', $field));
        }

        return $value;
    }

    /** @param array<string, mixed> $payload */
    private function nullableBool(array $payload, string $field): ?bool
    {
        $value = $payload[$field] ?? null;

        if (null === $value) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return match (strtolower($value)) {
                '1', 'true', 'yes' => true,
                '0', 'false', 'no' => false,
                default => throw new InvalidArgumentException(sprintf('%s_must_be_bool', $field)),
            };
        }

        if (is_int($value)) {
            return match ($value) {
                1 => true,
                0 => false,
                default => throw new InvalidArgumentException(sprintf('%s_must_be_bool', $field)),
            };
        }

        throw new InvalidArgumentException(sprintf('%s_must_be_bool', $field));
    }

    /** @param array<string, mixed> $payload */
    private function requiredDateTime(array $payload, string $field): DateTimeImmutable
    {
        $value = $this->requiredString($payload, $field);

        try {
            return new DateTimeImmutable($value);
        } catch (\Exception) {
            throw new InvalidArgumentException(sprintf('%s_must_be_iso_datetime', $field));
        }
    }

    /** @param mixed $value
     *  @return array<string, mixed>
     */
    private function normalizeObject(mixed $value): array
    {
        if (null === $value) {
            return [];
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('meta_must_be_object');
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            $normalized[(string) $key] = $item;
        }

        return $normalized;
    }
}
