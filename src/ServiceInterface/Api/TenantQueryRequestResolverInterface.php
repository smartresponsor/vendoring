<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Api;

use App\DTO\Api\TenantQueryRequestDTO;
use Symfony\Component\HttpFoundation\Request;

interface TenantQueryRequestResolverInterface
{
    public function resolve(Request $request): TenantQueryRequestDTO;
}
