<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Privacy\Consent;

interface ModelInterface
{

    public function grant(string $vendorId, string $purpose): bool;
}

