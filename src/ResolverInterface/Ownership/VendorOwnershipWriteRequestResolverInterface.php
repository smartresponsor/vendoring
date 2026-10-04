<?php

declare(strict_types=1);

namespace App\Vendoring\ResolverInterface\Ownership;

interface VendorOwnershipWriteRequestResolverInterface
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolvePayment(int $vendorId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolveCommission(int $vendorId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolveConversation(int $vendorId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolveShipment(int $vendorId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolveGroup(int $vendorId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolveCategory(int $vendorId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolveFavourite(int $vendorId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolveWishlist(int $vendorId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolveCodeStorage(int $vendorId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolveRememberMeToken(int $vendorId, array $payload): array;

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function resolveCustomerOrder(int $vendorId, array $payload): array;
}
