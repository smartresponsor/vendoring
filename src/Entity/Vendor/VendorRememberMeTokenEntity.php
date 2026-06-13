<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorRememberMeTokenRepository::class)]
#[ORM\Table(name: 'vendor_remember_me_token')]
class VendorRememberMeTokenEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'rememberMeTokens')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $series = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $tokenValue = '';
    #[ORM\Column(type: 'datetime_immutable', nullable: false)] private ?\DateTimeImmutable $lastUsedAt = null;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $providerClass = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $username = '';
    public function __construct(VendorEntity $vendor, string $series, string $tokenValue, string $providerClass, string $username)
    {
        parent::__construct('active');
        $this->vendor = $vendor;
        $this->series = $series;
        $this->tokenValue = $tokenValue;
        $this->providerClass = $providerClass;
        $this->username = $username;
        $this->lastUsedAt = new \DateTimeImmutable();
    }

    public function updateToken(string $tokenValue): self
    {
        $this->tokenValue = $tokenValue;
        $this->lastUsedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getSeries()
    {
        return $this->series;
    }

    public function getTokenValue()
    {
        return $this->tokenValue;
    }

    public function getLastUsedAt()
    {
        return $this->lastUsedAt;
    }

    public function getProviderClass()
    {
        return $this->providerClass;
    }

    public function getUsername()
    {
        return $this->username;
    }
}
