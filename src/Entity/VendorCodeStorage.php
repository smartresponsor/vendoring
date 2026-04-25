<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_code_storage')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_code_storage_code', columns: ['code'])]
final class VendorCodeStorage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(type: 'string', length: 64)]
    private string $code;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $purpose;

    #[ORM\Column(name: 'is_login', type: 'boolean')]
    private bool $isLogin = false;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable')]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'consumed_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $consumedAt = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(Vendor $vendor, string $code, string $purpose, DateTimeImmutable $expiresAt)
    {
        $this->vendor = $vendor;
        $this->code = $code;
        $this->purpose = $purpose;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new DateTimeImmutable();
    }

    public function update(string $purpose, DateTimeImmutable $expiresAt, ?string $phone = null, ?bool $isLogin = null): void
    {
        $this->purpose = $purpose;
        $this->expiresAt = $expiresAt;
        $this->updateDelivery($phone, $isLogin);
    }

    public function updateDelivery(?string $phone = null, ?bool $isLogin = null): void
    {
        $this->phone = $phone;
        $this->isLogin = null === $isLogin ? $this->isLogin : $isLogin;
    }
}
