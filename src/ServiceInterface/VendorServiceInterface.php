<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface;

use App\Vendoring\DTO\VendorCreateDTO;
use App\Vendoring\DTO\VendorUpdateDTO;
use App\Vendoring\Entity\Vendor;

interface VendorServiceInterface
{
    public function create(VendorCreateDTO $dto): Vendor;

    public function update(Vendor $vendor, VendorUpdateDTO $dto): Vendor;
}
