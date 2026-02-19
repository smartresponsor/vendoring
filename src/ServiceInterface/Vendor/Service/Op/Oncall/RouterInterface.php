<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Op\Oncall;

interface RouterInterface
{

    public function route(string $sev): array;
}
