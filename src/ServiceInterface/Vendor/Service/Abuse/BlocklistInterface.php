<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Abuse;

interface BlocklistInterface
{

    public function blocked(string $id): bool;
}
