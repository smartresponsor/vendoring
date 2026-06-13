<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorCommissionRepository::class)]
#[ORM\Table(name: 'vendor_commission')]
class VendorCommissionEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'commissions')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $code = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $direction = '';
    #[ORM\Column(type: 'decimal', precision: 6, scale: 2, nullable: false)] private string $ratePercent = '0.00';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $status = '';
    #[ORM\Column(type: 'datetime_immutable', nullable: false)] private ?\DateTimeImmutable $effectiveFrom = null;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $effectiveTo = null;
    #[ORM\Column(type: 'json')] private array $meta = [];
    public function __construct(VendorEntity $vendor, string $code, string $direction, string $ratePercent, array $meta = [])
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->code = $code;
        $this->direction = $direction;
        $this->ratePercent = $ratePercent;
        $this->meta = $meta;
        $this->effectiveFrom = new \DateTimeImmutable();
    }

    public function update(string $direction, string $ratePercent, string $status, ?\DateTimeImmutable $effectiveFrom = null, ?\DateTimeImmutable $effectiveTo = null, array $meta = []): self
    {
        $this->direction = $direction;
        $this->ratePercent = $ratePercent;
        $this->status = $status;
        $this->effectiveFrom = $effectiveFrom ?? $this->effectiveFrom;
        $this->effectiveTo = $effectiveTo;
        $this->meta = $meta;

        return $this->setStatus($status);
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function getRatePercent()
    {
        return $this->ratePercent;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getEffectiveFrom()
    {
        return $this->effectiveFrom;
    }

    public function getEffectiveTo()
    {
        return $this->effectiveTo;
    }

    public function getMeta()
    {
        return $this->meta;
    }
}
