<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorCommissionHistoryRepository::class)]
#[ORM\Table(name: 'vendor_commission_history')]
class VendorCommissionHistoryEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'commissionHistory')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\ManyToOne(targetEntity: VendorCommissionEntity::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?VendorCommissionEntity $commission = null;
    #[ORM\Column(type: 'integer', nullable: true)] private ?int $changedByUserId = null;
    #[ORM\Column(type: 'decimal', precision: 6, scale: 2, nullable: true)] private ?string $previousRatePercent = null;
    #[ORM\Column(type: 'decimal', precision: 6, scale: 2, nullable: false)] private string $newRatePercent = '0.00';
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $reason = null;
    #[ORM\Column(type: 'datetime_immutable', nullable: false)] private ?\DateTimeImmutable $changedAt = null;
    public function __construct(VendorEntity $vendor, ?VendorCommissionEntity $commission, ?int $changedByUserId, ?string $previousRatePercent, string $newRatePercent, ?string $reason = null)
    {
        parent::__construct();
        $this->vendor = $vendor;
        $this->commission = $commission;
        $this->changedByUserId = $changedByUserId;
        $this->previousRatePercent = $previousRatePercent;
        $this->newRatePercent = $newRatePercent;
        $this->reason = $reason;
        $this->changedAt = new \DateTimeImmutable();
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getChangedByUserId()
    {
        return $this->changedByUserId;
    }

    public function getPreviousRatePercent()
    {
        return $this->previousRatePercent;
    }

    public function getNewRatePercent()
    {
        return $this->newRatePercent;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function getChangedAt()
    {
        return $this->changedAt;
    }
}
