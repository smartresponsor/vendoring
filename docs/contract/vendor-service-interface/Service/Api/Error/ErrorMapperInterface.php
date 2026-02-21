<?php
// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Vendor\Service\Api\Error;

interface ErrorMapperInterface
{

    public function map(int $code, string $detail): array;
}

