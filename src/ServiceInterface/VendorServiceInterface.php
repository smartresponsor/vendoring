<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface;

use App\DTO\VendorCreateDTO;
use App\DTO\VendorUpdateDTO;
use App\Entity\Vendor;

interface VendorServiceInterface
{
    public function create(VendorCreateDTO $dto): Vendor;

    public function update(Vendor $vendor, VendorUpdateDTO $dto): Vendor;
}
