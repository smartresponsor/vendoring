<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\DTO;

interface VendorCreateDTOInterface
{

    public function __construct(public string $brandName, public ?int $userId = null);
}
