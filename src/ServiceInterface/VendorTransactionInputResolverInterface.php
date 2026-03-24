<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\ValueObject\VendorTransactionData;
use Symfony\Component\HttpFoundation\Request;

interface VendorTransactionInputResolverInterface
{
    public function resolveCreateData(Request $request): VendorTransactionData;

    public function resolveStatus(Request $request): string;

    public function normalizeErrorCode(string $message): string;
}
