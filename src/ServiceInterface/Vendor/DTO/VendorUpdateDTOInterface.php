<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO;

interface VendorUpdateDTOInterface
{

    public function __construct(public ?string $brandName = null, public ?string $status = null);
}
