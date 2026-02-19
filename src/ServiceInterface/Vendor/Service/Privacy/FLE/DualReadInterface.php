<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Privacy\FLE;

interface DualReadInterface
{

    public function choose(string $plain = null, string $enc = null, bool $cutover = false): ?string;
}
