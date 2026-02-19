<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Reliability\Chao;

interface InjectorInterface
{

    public function latency(int $ms): bool;
}
