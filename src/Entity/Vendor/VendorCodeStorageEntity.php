<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorCodeStorageRepository::class)]
#[ORM\Table(name: 'vendor_code_storage')]
class VendorCodeStorageEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'codeStorage')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $code = '';
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $phone = null;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $purpose = '';
    #[ORM\Column(type: 'boolean')] private bool $isLogin = false;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $expiresAt = null;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $consumedAt = null;
    public function __construct(VendorEntity $vendor, string $code, ?string $phone, string $purpose, bool $isLogin, \DateTimeImmutable $expiresAt)
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->code = $code;
        $this->phone = $phone;
        $this->purpose = $purpose;
        $this->isLogin = $isLogin;
        $this->expiresAt = $expiresAt;
    }

    public function consume(?\DateTimeImmutable $at = null): self
    {
        $this->consumedAt = $at ?? new \DateTimeImmutable();

        return $this;
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getPurpose()
    {
        return $this->purpose;
    }

    public function isIsLogin()
    {
        return $this->isLogin;
    }

    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    public function getConsumedAt()
    {
        return $this->consumedAt;
    }
}
