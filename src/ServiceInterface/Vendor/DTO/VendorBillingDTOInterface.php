<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO;

interface VendorBillingDTOInterface
{

    public function __construct(public int $vendorId, public ?string $iban = null, public ?string $swift = null, public string $payoutMethod = 'bank', public ?string $billingEmail = null);
}
