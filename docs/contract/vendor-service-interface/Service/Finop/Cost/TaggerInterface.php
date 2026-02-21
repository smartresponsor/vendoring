<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Finop\Cost;

interface TaggerInterface
{

    public function tags(string $vendorId, string $feature): array;
}

