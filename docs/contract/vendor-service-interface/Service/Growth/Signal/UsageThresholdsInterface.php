<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Growth\Signal;

interface UsageThresholdsInterface
{

    public function crossed(int $used, int $quota): array;
}

