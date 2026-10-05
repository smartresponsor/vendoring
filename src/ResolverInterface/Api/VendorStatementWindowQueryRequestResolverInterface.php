<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ResolverInterface\Api;

use App\Vendoring\DTO\Api\VendorStatementWindowQueryRequestDTO;
use Symfony\Component\HttpFoundation\Request;

interface VendorStatementWindowQueryRequestResolverInterface
{
    public function resolve(Request $request): VendorStatementWindowQueryRequestDTO;
}
