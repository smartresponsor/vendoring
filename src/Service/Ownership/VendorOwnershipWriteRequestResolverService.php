<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Ownership;

use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipWriteRequestResolverServiceInterface;

final readonly class VendorOwnershipWriteRequestResolverService implements VendorOwnershipWriteRequestResolverServiceInterface
{
    public function resolvePayment(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return $this->withOptionalId($payload, [
            'providerCode' => $this->requiredString($payload, 'providerCode'),
            'methodCode' => $this->requiredString($payload, 'methodCode'),
            'externalPaymentId' => $this->optionalString($payload, 'externalPaymentId'),
            'label' => $this->optionalString($payload, 'label'),
            'status' => $this->optionalString($payload, 'status') ?? 'active',
            'isDefault' => $this->boolean($payload, 'isDefault', false),
            'meta' => $this->metadata($payload),
        ]);
    }

    public function resolveCommission(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return $this->withOptionalId($payload, [
            'code' => $this->requiredString($payload, 'code'),
            'direction' => $this->requiredString($payload, 'direction'),
            'ratePercent' => $this->requiredString($payload, 'ratePercent'),
            'status' => $this->optionalString($payload, 'status') ?? 'active',
            'effectiveFrom' => $this->optionalDate($payload, 'effectiveFrom'),
            'effectiveTo' => $this->optionalDate($payload, 'effectiveTo'),
            'meta' => $this->metadata($payload),
        ]);
    }

    public function resolveConversation(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return [
            'subject' => $this->optionalString($payload, 'subject'),
            'channel' => $this->requiredString($payload, 'channel'),
            'counterpartyType' => $this->optionalString($payload, 'counterpartyType'),
            'counterpartyId' => $this->optionalString($payload, 'counterpartyId'),
            'counterpartyName' => $this->optionalString($payload, 'counterpartyName'),
            'status' => $this->optionalString($payload, 'status') ?? 'open',
            'meta' => $this->metadata($payload),
        ];
    }

    public function resolveShipment(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return $this->withOptionalId($payload, [
            'externalShipmentId' => $this->optionalString($payload, 'externalShipmentId'),
            'carrierCode' => $this->optionalString($payload, 'carrierCode'),
            'methodCode' => $this->optionalString($payload, 'methodCode'),
            'trackingNumber' => $this->optionalString($payload, 'trackingNumber'),
            'status' => $this->optionalString($payload, 'status') ?? 'pending',
            'meta' => $this->metadata($payload),
        ]);
    }

    public function resolveGroup(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return $this->withOptionalId($payload, [
            'code' => $this->requiredString($payload, 'code'),
            'name' => $this->requiredString($payload, 'name'),
            'status' => $this->optionalString($payload, 'status') ?? 'active',
            'meta' => $this->metadata($payload),
        ]);
    }

    public function resolveCategory(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return $this->withOptionalId($payload, [
            'categoryCode' => $this->requiredString($payload, 'categoryCode'),
            'categoryName' => $this->optionalString($payload, 'categoryName'),
            'isPrimary' => $this->boolean($payload, 'isPrimary', false),
        ]);
    }

    public function resolveFavourite(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return $this->withOptionalId($payload, [
            'targetType' => $this->requiredString($payload, 'targetType'),
            'targetId' => $this->requiredString($payload, 'targetId'),
            'note' => $this->optionalString($payload, 'note'),
        ]);
    }

    public function resolveWishlist(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return $this->withOptionalId($payload, [
            'customerReference' => $this->requiredString($payload, 'customerReference'),
            'name' => $this->requiredString($payload, 'name'),
            'status' => $this->optionalString($payload, 'status') ?? 'active',
        ]);
    }

    public function resolveCodeStorage(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return $this->withOptionalId($payload, [
            'code' => $this->requiredString($payload, 'code'),
            'phone' => $this->optionalString($payload, 'phone'),
            'purpose' => $this->requiredString($payload, 'purpose'),
            'isLogin' => $this->boolean($payload, 'isLogin', false),
            'expiresAt' => $this->requiredDate($payload, 'expiresAt'),
            'consumedAt' => $this->optionalDate($payload, 'consumedAt'),
        ]);
    }

    public function resolveRememberMeToken(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return $this->withOptionalId($payload, [
            'series' => $this->requiredString($payload, 'series'),
            'tokenValue' => $this->requiredString($payload, 'tokenValue'),
            'providerClass' => $this->requiredString($payload, 'providerClass'),
            'username' => $this->requiredString($payload, 'username'),
        ]);
    }

    public function resolveCustomerOrder(int $vendorId, array $payload): array
    {
        $this->assertVendorId($vendorId, $payload);

        return $this->withOptionalId($payload, [
            'externalOrderId' => $this->requiredString($payload, 'externalOrderId'),
            'orderNumber' => $this->optionalString($payload, 'orderNumber'),
            'status' => $this->optionalString($payload, 'status') ?? 'placed',
            'currency' => $this->requiredString($payload, 'currency'),
            'grossCents' => $this->integer($payload, 'grossCents'),
            'netCents' => $this->integer($payload, 'netCents'),
            'meta' => $this->metadata($payload),
        ]);
    }

    /** @param array<string, mixed> $payload */
    private function assertVendorId(int $vendorId, array $payload): void
    {
        if (!array_key_exists('vendorId', $payload)) {
            return;
        }

        $payloadVendorId = filter_var($payload['vendorId'], FILTER_VALIDATE_INT);
        if (false === $payloadVendorId || $payloadVendorId !== $vendorId) {
            throw new \InvalidArgumentException('vendor_id_mismatch');
        }
    }

    /** @param array<string, mixed> $payload */
    private function requiredString(array $payload, string $key): string
    {
        $value = $this->optionalString($payload, $key);
        if (null === $value) {
            throw new \InvalidArgumentException($this->errorKey($key).'_required');
        }

        return $value;
    }

    /** @param array<string, mixed> $payload */
    private function optionalString(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;
        if (null === $value) {
            return null;
        }
        if (!is_scalar($value)) {
            throw new \InvalidArgumentException($this->errorKey($key).'_invalid');
        }

        $value = trim((string) $value);

        return '' === $value ? null : $value;
    }

    /** @param array<string, mixed> $payload */
    private function boolean(array $payload, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $payload)) {
            return $default;
        }

        $value = filter_var($payload[$key], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if (null === $value) {
            throw new \InvalidArgumentException($this->errorKey($key).'_invalid');
        }

        return $value;
    }

    /** @param array<string, mixed> $payload */
    private function integer(array $payload, string $key): int
    {
        $value = $payload[$key] ?? null;
        $integer = filter_var($value, FILTER_VALIDATE_INT);
        if (false === $integer) {
            throw new \InvalidArgumentException($this->errorKey($key).'_invalid');
        }

        return $integer;
    }

    /** @param array<string, mixed> $payload */
    private function requiredDate(array $payload, string $key): \DateTimeImmutable
    {
        $value = $this->optionalDate($payload, $key);
        if (!$value instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException($this->errorKey($key).'_required');
        }

        return $value;
    }

    /** @param array<string, mixed> $payload */
    private function optionalDate(array $payload, string $key): ?\DateTimeImmutable
    {
        $value = $this->optionalString($payload, $key);
        if (null === $value) {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            throw new \InvalidArgumentException($this->errorKey($key).'_invalid');
        }
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function metadata(array $payload): array
    {
        $meta = $payload['meta'] ?? [];
        if (!is_array($meta)) {
            throw new \InvalidArgumentException('meta_invalid');
        }

        /* @var array<string, mixed> $meta */
        return $meta;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $resolved
     *
     * @return array<string, mixed>
     */
    private function withOptionalId(array $payload, array $resolved): array
    {
        if (!array_key_exists('id', $payload)) {
            return $resolved;
        }

        $id = filter_var($payload['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (false === $id) {
            throw new \InvalidArgumentException('id_invalid');
        }

        return ['id' => $id] + $resolved;
    }

    private function errorKey(string $key): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
    }
}
