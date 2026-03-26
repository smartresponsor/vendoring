<?php

declare(strict_types=1);

namespace App\Entity;

final class VendorApiKey
{
    private ?int $id = null;
    private string $status;
    private ?\DateTimeImmutable $lastUsedAt = null;
    private \DateTimeImmutable $createdAt;

    public function __construct(
        private readonly Vendor $vendor,
        private readonly string $tokenHash,
        private readonly string $permissions,
    ) {
        $this->status = 'active';
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getPermissions(): string
    {
        return $this->permissions;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function deactivate(): void
    {
        $this->status = 'inactive';
    }

    public function hasPermission(string $permission): bool
    {
        $tokens = array_filter(array_map('trim', explode(',', $this->permissions)), static fn (string $value): bool => '' !== $value);

        return in_array($permission, $tokens, true);
    }

    public function touch(): void
    {
        $this->lastUsedAt = new \DateTimeImmutable();
    }
}
