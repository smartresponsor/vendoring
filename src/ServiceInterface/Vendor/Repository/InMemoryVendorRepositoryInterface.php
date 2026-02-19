<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Repository;

interface InMemoryVendorRepositoryInterface
{

    public function get(VendorId $id): ?Vendor;

    public function listActive(): array;

    public function save(Vendor $v): void;

    public function remove(VendorId $id): void;
}
