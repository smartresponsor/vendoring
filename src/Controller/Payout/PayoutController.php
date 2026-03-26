<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Payout;

use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\ServiceInterface\Payout\VendorPayoutRequestServiceInterface;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payout')]
final class PayoutController extends AbstractController
{
    public function __construct(
        private readonly VendorPayoutServiceInterface $svc,
        private readonly PayoutRepositoryInterface $repo,
        private readonly VendorPayoutRequestServiceInterface $payoutRequestService,
    ) {
    }

    #[Route('/create', methods: ['POST'])]
    public function create(Request $r): JsonResponse
    {
        try {
            $dto = $this->payoutRequestService->toCreateDto($r->toArray());
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        }

        $id = $this->svc->create($dto);
        if (null === $id) {
            return new JsonResponse(['data' => ['created' => false, 'reason' => 'threshold_not_met']], 200);
        }

        return new JsonResponse(['data' => ['created' => true, 'payoutId' => $id]], 201);
    }

    #[Route('/process/{payoutId}', methods: ['POST'])]
    public function process(string $payoutId): JsonResponse
    {
        $ok = $this->svc->process($payoutId);

        return new JsonResponse(['data' => ['processed' => $ok]], $ok ? 200 : 404);
    }

    #[Route('/{payoutId}', methods: ['GET'])]
    public function getOne(string $payoutId): JsonResponse
    {
        $p = $this->repo->byId($payoutId);
        if (!$p) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        return new JsonResponse(['data' => $this->payoutRequestService->normalizePayout($p)], 200);
    }
}
