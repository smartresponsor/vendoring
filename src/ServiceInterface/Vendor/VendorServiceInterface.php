<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\ServiceInterface\Vendor;

use App\DTO\Vendor\VendorCreateDTO;
use App\DTO\Vendor\VendorUpdateDTO;
use App\Entity\Vendor\Vendor;

interface VendorServiceInterface
{
    public function create(VendorCreateDTO $dto): Vendor;

    public function update(Vendor $vendor, VendorUpdateDTO $dto): Vendor;
}
