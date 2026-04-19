<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ServiceInterface\Api;

use App\Vendoring\DTO\Api\TenantQueryRequestDTO;
use Symfony\Component\HttpFoundation\Request;

interface TenantQueryRequestResolverInterface
{
    public function resolve(Request $request): TenantQueryRequestDTO;
}
