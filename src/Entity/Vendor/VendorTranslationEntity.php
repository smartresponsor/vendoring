<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorTranslationRepository::class)]
#[ORM\Table(name: 'vendor_translation')]
class VendorTranslationEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'translations')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 16)] private string $localeCode;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $displayName = null;
    #[ORM\Column(type: 'text', nullable: true)] private ?string $description = null;
    #[ORM\Column(type: 'json')] private array $meta = [];
    public function __construct(VendorEntity $vendor, string $localeCode, ?string $displayName = null, ?string $description = null, array $meta = [])
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->localeCode = $localeCode;
        $this->displayName = $displayName;
        $this->description = $description;
        $this->meta = $meta;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getLocaleCode(): string
    {
        return $this->localeCode;
    }
}
