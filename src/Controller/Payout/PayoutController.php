<?php

declare(strict_types=1);

namespace App\Controller\Payout;

use App\DTO\Payout\CreatePayoutDTO;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use App\ServiceInterface\Payout\PayoutServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payout')]
final class PayoutController extends AbstractController
{
    public function __construct(
        private readonly PayoutServiceInterface $svc,
        private readonly PayoutRepositoryInterface $repo,
    ) {
    }

    #[Route('/create', methods: ['POST'])]
    public function create(Request $r): JsonResponse
    {
        $p = $r->toArray();
        foreach (['vendorId', 'currency', 'thresholdCents', 'retentionFeePercent'] as $k) {
            if (!isset($p[$k])) {
                return new JsonResponse(['error' => "$k required"], 422);
            }
        }
        $dto = new CreatePayoutDTO((string) $p['vendorId'], (string) $p['currency'], (int) $p['thresholdCents'], (float) $p['retentionFeePercent']);
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

        return new JsonResponse(['data' => [
            'id' => $p->id, 'vendorId' => $p->vendorId, 'currency' => $p->currency,
            'grossCents' => $p->grossCents, 'feeCents' => $p->feeCents, 'netCents' => $p->netCents,
            'status' => $p->status, 'createdAt' => $p->createdAt, 'processedAt' => $p->processedAt,
        ]], 200);
    }
}
