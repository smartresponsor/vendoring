<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorChannelRepository::class)]
#[ORM\Table(name: 'vendor_channel')]
class VendorChannelEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'channels')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 64)] private string $code;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $nameEntity = null;
    public function __construct(VendorEntity $vendor, string $code, ?string $nameEntity = null)
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->code = $code;
        $this->nameEntity = $nameEntity;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): ?string
    {
        return $this->nameEntity;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }
}
