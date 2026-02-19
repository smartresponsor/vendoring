<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service;

interface VendorServiceInterface
{

    public function __construct(private VendorRepositoryPort $repo);

    public function register(Vendor $vendor): void;

    public function rename(VendorId $id, string $newName): void;
}
