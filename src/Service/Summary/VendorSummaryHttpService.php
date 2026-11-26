<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Summary;

use App\Vendoring\ResolverInterface\Security\VendorFinancialScopeResolverInterface;
use App\Vendoring\ServiceInterface\Ledger\VendorSummaryServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorSummaryHttpService
{
    public function __construct(private readonly VendorSummaryServiceInterface $vendorSummaryService, private readonly VendorFinancialScopeResolverInterface $financialScope)
    {
    }

    public function summary(string $id, Request $r): JsonResponse
    {
        $scope = $this->financialScope->resolve($r, 'read:finance', $id);
        if (null === $scope || null === $scope->getTenantId()) {
            return new JsonResponse(['error' => 'financial_scope_denied'], 403);
        }

        $from = (string) ($r->query->get('from') ?? '');
        $to = (string) ($r->query->get('to') ?? '');
        $currency = (string) ($r->query->get('currency') ?? '');
        $summary = $this->vendorSummaryService->build($scope->getTenantId(), $scope->getLedgerVendorId(), $from, $to, $currency);

        return new JsonResponse(['data' => $summary], 200);
    }
}
