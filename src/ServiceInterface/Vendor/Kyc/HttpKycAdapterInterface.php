<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Kyc;

interface HttpKycAdapterInterface
{

    public function __construct(private string $endpoint);

    public function verify(string $vendorId, string $passportNumber): bool;
}
