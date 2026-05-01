<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_remember_me_token')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_remember_me_series', columns: ['series'])]
final class VendorRememberMeTokenEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(type: 'string', length: 128)]
    private string $series;

    #[ORM\Column(name: 'token_value', type: 'string', length: 255)]
    private string $tokenValue;

    #[ORM\Column(name: 'last_used_at', type: 'datetime_immutable')]
    private DateTimeImmutable $lastUsedAt;

    #[ORM\Column(name: 'provider_class', type: 'string', length: 255)]
    private string $providerClass;

    #[ORM\Column(type: 'string', length: 255)]
    private string $username;

    public function __construct(VendorEntity $vendor, string $series, string $tokenValue, string $providerClass, string $username)
    {
        $this->vendor = $vendor;
        $this->series = $series;
        $this->tokenValue = $tokenValue;
        $this->providerClass = $providerClass;
        $this->username = $username;
        $this->lastUsedAt = new DateTimeImmutable();
    }

    public function rotate(string $tokenValue, string $providerClass, string $username): void
    {
        $this->tokenValue = $tokenValue;
        $this->providerClass = $providerClass;
        $this->username = $username;
        $this->lastUsedAt = new DateTimeImmutable();
    }
}
