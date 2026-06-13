<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Payout\Account;

use App\Vendoring\ServiceInterface\Payout\VendorPayoutAccountServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class VendorPayoutAccountService
{
    public function __construct(private readonly VendorPayoutAccountServiceInterface $payoutAccountService)
    {
    }

    public function upsert(Request $r): JsonResponse
    {
        try {
            /** @var array<string, mixed> $payload */
            $payload = $r->toArray();
            $account = $this->payoutAccountService->upsertFromPayload($payload);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        }

        return new JsonResponse(['data' => ['provider' => $account->provider, 'accountRef' => $account->accountRef, 'active' => $account->active]], 200);
    }
}
