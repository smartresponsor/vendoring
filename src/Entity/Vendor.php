<?php

declare(strict_types=1);

namespace App\Entity;

use App\EntityInterface\VendorEntityInterface;

final class Vendor implements VendorEntityInterface
{
    private ?int $id = null;
    private string $brandName;
    private ?int $ownerUserId;
    private string $status;
    private \DateTimeImmutable $createdAt;

    public function __construct(string $brandName, ?int $ownerUserId = null)
    {
        $this->brandName = $brandName;
        $this->ownerUserId = $ownerUserId;
        $this->status = 'inactive';
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrandName(): string
    {
        return $this->brandName;
    }

    public function getOwnerUserId(): ?int
    {
        return $this->ownerUserId;
    }

    public function getUserId(): ?int
    {
        return $this->ownerUserId;
    }

    public function hasOwnerUserId(): bool
    {
        return null !== $this->ownerUserId;
    }

    public function changeOwnerUserId(?int $ownerUserId): void
    {
        $this->ownerUserId = $ownerUserId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function rename(string $brandName): void
    {
        $this->brandName = $brandName;
    }

    public function activate(): void
    {
        $this->status = 'active';
    }

    public function deactivate(): void
    {
        $this->status = 'inactive';
    }
}
