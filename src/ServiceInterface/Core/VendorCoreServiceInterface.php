<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\ServiceInterface\Core;

use App\Vendoring\DTO\VendorCreateDTO;
use App\Vendoring\DTO\VendorUpdateDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;

interface VendorCoreServiceInterface
{
    public function create(VendorCreateDTO $dto): VendorEntity;

    public function update(VendorEntity $vendor, VendorUpdateDTO $dto): VendorEntity;
}
