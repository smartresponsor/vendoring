<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\ResolverInterface\Transaction;

use App\Vendoring\ValueObject\VendorTransactionDataValueObject;
use Symfony\Component\HttpFoundation\Request;

interface VendorTransactionInputResolverInterface
{
    public function resolveCreateData(Request $request): VendorTransactionDataValueObject;

    public function resolveStatus(Request $request): string;

    public function normalizeErrorCode(string $message): string;
}
