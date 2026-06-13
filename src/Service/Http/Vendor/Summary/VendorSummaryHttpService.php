<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Summary;

use App\Vendoring\ServiceInterface\Ledger\VendorSummaryServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorSummaryHttpService
{
    public function __construct(private readonly VendorSummaryServiceInterface $vendorSummaryService)
    {
    }

    public function summary(string $vendorId, Request $r): JsonResponse
    {
        $tenantId = (string) ($r->query->get('tenantId') ?? '');
        $from = (string) ($r->query->get('from') ?? '');
        $to = (string) ($r->query->get('to') ?? '');
        $currency = (string) ($r->query->get('currency') ?? '');
        if (!$tenantId) {
            return new JsonResponse(['error' => 'tenantId required'], 422);
        }
        $summary = $this->vendorSummaryService->build($tenantId, $vendorId, $from, $to, $currency);

        return new JsonResponse(['data' => $summary], 200);
    }
}
