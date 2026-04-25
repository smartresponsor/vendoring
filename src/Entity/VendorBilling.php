<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\VendorBillingRepository')]
#[ORM\Table(name: 'vendor_billing')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_billing_vendor', columns: ['vendor_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorBilling
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $iban = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $swift = null;

    #[ORM\Column(name: 'payout_method', type: 'string', length: 32)]
    private string $payoutMethod = 'bank';

    #[ORM\Column(name: 'billing_email', type: 'string', length: 255, nullable: true)]
    private ?string $billingEmail = null;

    #[ORM\Column(name: 'payout_status', type: 'string', length: 32)]
    private string $payoutStatus = 'idle';

    public function __construct(Vendor $vendor)
    {
        $this->vendor = $vendor;
    }

    public function update(?string $iban = null, ?string $swift = null, string $payoutMethod = 'bank', ?string $billingEmail = null): void
    {
        $this->iban = $iban;
        $this->swift = $swift;
        $this->payoutMethod = $payoutMethod;
        $this->billingEmail = $billingEmail;
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function getBillingEmail(): ?string
    {
        return $this->billingEmail;
    }

    public function getPayoutStatus(): string
    {
        return $this->payoutStatus;
    }

    public function markPayoutRequested(): void
    {
        $this->payoutStatus = 'requested';
    }

    public function markPayoutCompleted(): void
    {
        $this->payoutStatus = 'completed';
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function getSwift(): ?string
    {
        return $this->swift;
    }

    public function getPayoutMethod(): string
    {
        return $this->payoutMethod;
    }
}
