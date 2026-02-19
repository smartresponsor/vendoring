<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Compliance\Access;

interface SnapshotterInterface
{

    public function snapshot(): string;
}
