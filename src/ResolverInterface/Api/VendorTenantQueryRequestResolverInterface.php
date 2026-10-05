<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ResolverInterface\Api;

use App\Vendoring\DTO\Api\VendorTenantQueryRequestDTO;
use Symfony\Component\HttpFoundation\Request;

interface VendorTenantQueryRequestResolverInterface
{
    public function resolve(Request $request): VendorTenantQueryRequestDTO;
}
