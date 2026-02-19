<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Integration\Security;

interface HmacSignerInterface
{

    public function sign(string $k, string $m): string;
}
