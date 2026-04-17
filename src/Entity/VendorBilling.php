<?php

declare(strict_types=1);

namespace App\Entity;

/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorBilling
{
    /** @var int|null */
    private ?int $id = null;
    private ?string $iban = null;
    private ?string $swift = null;
    private string $payoutMethod = 'bank';
    private ?string $billingEmail = null;
    private string $payoutStatus = 'idle';

    public function __construct(private readonly Vendor $vendor) {}

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
