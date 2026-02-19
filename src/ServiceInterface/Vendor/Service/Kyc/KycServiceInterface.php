<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Kyc;

interface KycServiceInterface
{

    public function __construct(private KycProviderPort $provider);

    public function verify(Passport $p): bool;
}
