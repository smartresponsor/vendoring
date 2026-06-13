<?php

declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Crud;

use App\Vendoring\DTO\VendorCreateDTO;
use App\Vendoring\DTO\VendorUpdateDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;

interface VendorCrudServiceInterface
{
    /**
     * @return list<VendorEntity>
     */
    public function index(): array;

    public function find(int|string $id): ?VendorEntity;

    public function create(VendorCreateDTO $dto): VendorEntity;

    public function update(VendorEntity $vendor, VendorUpdateDTO $dto): VendorEntity;
}
