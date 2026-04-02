<?php

declare(strict_types=1);

namespace App\Entity;

use App\EntityInterface\VendorUserAssignmentEntityInterface;

final class VendorUserAssignment implements VendorUserAssignmentEntityInterface
{
    private ?int $id = null;
    private string $role;
    private string $status;
    private bool $isPrimary;
    private \DateTimeImmutable $grantedAt;
    private ?\DateTimeImmutable $revokedAt;

    public function __construct(
        private readonly int $vendorId,
        private readonly int $userId,
        string $role = 'owner',
        string $status = 'active',
        bool $isPrimary = false,
        ?\DateTimeImmutable $grantedAt = null,
        ?\DateTimeImmutable $revokedAt = null,
    ) {
        $this->role = trim($role);
        $this->status = trim($status);
        $this->isPrimary = $isPrimary;
        $this->grantedAt = $grantedAt ?? new \DateTimeImmutable();
        $this->revokedAt = $revokedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendorId(): int
    {
        return $this->vendorId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function getGrantedAt(): \DateTimeImmutable
    {
        return $this->grantedAt;
    }

    public function getRevokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function markPrimary(): void
    {
        $this->isPrimary = true;
    }

    public function clearPrimary(): void
    {
        $this->isPrimary = false;
    }

    public function revoke(): void
    {
        $this->status = 'revoked';
        $this->isPrimary = false;
        $this->revokedAt = new \DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->revokedAt = null;
    }

    public function changeRole(string $role): void
    {
        $this->role = trim($role);
    }
}
