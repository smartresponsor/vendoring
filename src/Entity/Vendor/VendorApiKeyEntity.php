<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorApiKeyRepository::class)]
#[ORM\Table(name: 'vendor_api_key')]
class VendorApiKeyEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255)] private string $tokenHash;
    #[ORM\Column(type: 'string', length: 512)] private string $permissions;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $lastUsedAt = null;
    public function __construct(VendorEntity $vendor, string $tokenHash, string $permissions)
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->tokenHash = $tokenHash;
        $this->permissions = $permissions;
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, array_map('trim', explode(',', $this->permissions)), true);
    }

    public function touch(): self
    {
        $this->lastUsedAt = new \DateTimeImmutable();

        return $this;
    }

    public function deactivate(): self
    {
        return $this->setStatus('inactive');
    }

    public function getLastUsedAt(): ?\DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function getVendor(): VendorEntity
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
}
