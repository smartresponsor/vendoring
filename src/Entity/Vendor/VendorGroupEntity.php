<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorGroupRepository::class)]
#[ORM\Table(name: 'vendor_group')]
class VendorGroupEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'groups')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $code = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $nameEntity = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $status = '';
    #[ORM\Column(type: 'json')] private array $meta = [];
    public function __construct(VendorEntity $vendor, string $code, string $nameEntity, array $meta = [])
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->code = $code;
        $this->nameEntity = $nameEntity;
        $this->status = 'active';
        $this->meta = $meta;
    }

    public function update(string $nameEntity, string $status, array $meta = []): self
    {
        $this->nameEntity = $nameEntity;
        $this->status = $status;
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

    public function getName()
    {
        return $this->nameEntity;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMeta()
    {
        return $this->meta;
    }
}
