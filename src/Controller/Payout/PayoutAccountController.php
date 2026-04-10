<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Payout;

use App\ServiceInterface\Payout\VendorPayoutAccountServiceInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payouts/account')]
final class PayoutAccountController extends AbstractController
{
    public function __construct(private readonly VendorPayoutAccountServiceInterface $payoutAccountService)
    {
    }

    #[Route('', methods: ['POST'])]
    public function upsert(Request $r): JsonResponse
    {
        try {
            $account = $this->payoutAccountService->upsertFromPayload($r->toArray());
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        }

        return new JsonResponse(['data' => ['provider' => $account->provider, 'accountRef' => $account->accountRef, 'active' => $account->active]], 200);
    }
}
