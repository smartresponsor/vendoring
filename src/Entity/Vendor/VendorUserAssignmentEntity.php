<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorUserAssignmentRepository::class)]
#[ORM\Table(name: 'vendor_user_assignment')]
class VendorUserAssignmentEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'userAssignments')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'integer')] private int $userId;
    #[ORM\Column(type: 'string', length: 64)] private string $role;
    #[ORM\Column(type: 'string', length: 32)] private string $status;
    #[ORM\Column(type: 'boolean')] private bool $primaryAssignment = false;
    #[ORM\Column(type: 'datetime_immutable')] private \DateTimeImmutable $grantedAt;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $revokedAt = null;
    public function __construct(...$arguments)
    {
        $vendor = $arguments['vendorId'] ?? $arguments['vendor'] ?? $arguments[0] ?? null;
        $userId = (int) ($arguments['userId'] ?? $arguments[1] ?? 0);
        $role = (string) ($arguments['role'] ?? $arguments[2] ?? 'owner');
        $status = (string) ($arguments['status'] ?? 'active');
        $isPrimary = (bool) ($arguments['isPrimary'] ?? $arguments[3] ?? false);

        parent::__construct('active');
        $this->vendor = $vendor instanceof VendorEntity ? $vendor : new VendorEntity('unresolved-vendor-'.$vendor);
        $this->userId = $userId;
        $this->role = $role;
        $this->status = $status;
        $this->primaryAssignment = $isPrimary;
        $this->grantedAt = new \DateTimeImmutable();
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
        return $this->primaryAssignment;
    }

    public function getGrantedAt(): \DateTimeImmutable
    {
        return $this->grantedAt;
    }

    public function getRevokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function revoke(): self
    {
        $this->status = 'revoked';
        $this->revokedAt = new \DateTimeImmutable();

        return $this;
    }

    public function activate(): self
    {
        $this->status = 'active';
        $this->revokedAt = null;

        return $this;
    }

    public function changeRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function markPrimary(): self
    {
        $this->primaryAssignment = true;

        return $this;
    }

    public function clearPrimary(): self
    {
        $this->primaryAssignment = false;

        return $this;
    }
}
