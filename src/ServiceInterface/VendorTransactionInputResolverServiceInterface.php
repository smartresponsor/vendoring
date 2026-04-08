<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\ServiceInterface;

use App\ValueObject\VendorTransactionData;
use Symfony\Component\HttpFoundation\Request;

/**
 * Application contract for vendor transaction input resolver service operations.
 */
interface VendorTransactionInputResolverServiceInterface
{
    /**
     * Resolves the requested runtime subject.
     */
    public function resolveCreateData(Request $request): VendorTransactionData;

    /**
     * Resolves the requested runtime subject.
     */
    public function resolveStatus(Request $request): string;

    /**
     * Normalizes the supplied value set for downstream use.
     */
    public function normalizeErrorCode(string $message): string;
}
