<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Repository;

interface PdoVendorRepositoryInterface
{

    public function __construct(private PDO $pdo);

    public function get(VendorId $id): ?Vendor;

    public function listActive(): array;

    public function save(Vendor $vendor): void;

    public function remove(VendorId $id): void;
}
