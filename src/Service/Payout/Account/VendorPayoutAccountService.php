<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Payout\Account;

use App\Vendoring\ResolverInterface\Security\VendorFinancialScopeResolverInterface;
use App\Vendoring\ServiceInterface\Payout\VendorPayoutAccountServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorPayoutAccountService
{
    public function __construct(private readonly VendorPayoutAccountServiceInterface $payoutAccountService, private readonly VendorFinancialScopeResolverInterface $financialScope)
    {
    }

    public function upsert(Request $r): JsonResponse
    {
        $scope = $this->financialScope->resolve($r, 'write:payouts', null);
        if (null === $scope || null === $scope->getTenantId()) {
            return new JsonResponse(['error' => 'financial_scope_denied'], 403);
        }

        try {
            /** @var array<string, mixed> $payload */
            $payload = $r->toArray();
            if ((isset($payload['tenantId']) && $payload['tenantId'] !== $scope->getTenantId())
                || (isset($payload['vendorId']) && $payload['vendorId'] !== $scope->getLedgerVendorId())) {
                return new JsonResponse(['error' => 'financial_scope_denied'], 403);
            }
            $payload['tenantId'] = $scope->getTenantId();
            $payload['vendorId'] = $scope->getLedgerVendorId();
            $account = $this->payoutAccountService->upsertFromPayload($payload);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        }

        return new JsonResponse(['data' => ['provider' => $account->provider, 'accountRef' => $account->accountRef, 'active' => $account->active]], 200);
    }
}
