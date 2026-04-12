<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface\Api;

use App\DTO\Api\StatementWindowQueryRequestDTO;
use Symfony\Component\HttpFoundation\Request;

interface StatementWindowQueryRequestResolverInterface
{
    public function resolve(Request $request): StatementWindowQueryRequestDTO;
}
