<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Payout;

use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\ServiceInterface\Payout\VendorPayoutRequestServiceInterface;
use App\ServiceInterface\Payout\VendorPayoutServiceInterface;
use Doctrine\DBAL\Exception;
use InvalidArgumentException;
use JsonException;
use Random\RandomException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException as HttpFoundationJsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payout')]
final class PayoutController extends AbstractController
{
    public function __construct(
        private readonly VendorPayoutServiceInterface $payoutService,
        private readonly PayoutRepositoryInterface $payoutRepository,
        private readonly VendorPayoutRequestServiceInterface $payoutRequestService,
    ) {}

    #[Route('/create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $dto = $this->payoutRequestService->toCreateDto($request->toArray());
            $id = $this->payoutService->create($dto);
        } catch (HttpFoundationJsonException) {
            return new JsonResponse(['error' => 'malformed_json'], 400);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 422);
        } catch (Exception|JsonException|RandomException $exception) {
            return $this->runtimeFailureResponse('payout_create_failed');
        }

        if (null === $id) {
            return new JsonResponse(['data' => ['created' => false, 'reason' => 'threshold_not_met']], 200);
        }

        return new JsonResponse(['data' => ['created' => true, 'payoutId' => $id]], 201);
    }

    #[Route('/process/{payoutId}', methods: ['POST'])]
    public function process(string $payoutId): JsonResponse
    {
        try {
            $ok = $this->payoutService->process($payoutId);
        } catch (Exception|JsonException|RandomException $exception) {
            return $this->runtimeFailureResponse('payout_process_failed');
        }

        return new JsonResponse(['data' => ['processed' => $ok]], $ok ? 200 : 404);
    }

    #[Route('/{payoutId}', methods: ['GET'])]
    public function getOne(string $payoutId): JsonResponse
    {
        try {
            $payout = $this->payoutRepository->byId($payoutId);
        } catch (Exception $exception) {
            return $this->runtimeFailureResponse('payout_lookup_failed');
        }

        if (null === $payout) {
            return new JsonResponse(['error' => 'not_found'], 404);
        }

        return new JsonResponse(['data' => $this->payoutRequestService->normalizePayout($payout)], 200);
    }

    private function runtimeFailureResponse(string $errorCode): JsonResponse
    {
        return new JsonResponse([
            'error' => $errorCode,
            'hint' => 'Check runtime logs for details and retry the operation.',
        ], 500);
    }
}
