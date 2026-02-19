<?php
declare(strict_types = 1);

namespace App\Controller\Vendor\Payout;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Vendor\Payout\PayoutService;
use App\DTO\Vendor\Payout\CreatePayoutDTO;
use App\Repository\Vendor\Payout\PayoutRepository;

#[Route('/api/payout')]
final class PayoutController extends AbstractController
{
    public function __construct(
        private readonly PayoutService    $svc,
        private readonly PayoutRepository $repo
    )
    {
    }

    #[Route('/create', methods: ['POST'])]
    public function create(Request $r): JsonResponse
    {
        $p = $r->toArray();
        foreach (['vendorId', 'currency', 'thresholdCents', 'retentionFeePercent'] as $k) if (!isset($p[$k])) return new JsonResponse(['error' => "$k required"], 422);
        $dto = new CreatePayoutDTO((string)$p['vendorId'], (string)$p['currency'], (int)$p['thresholdCents'], (float)$p['retentionFeePercent']);
        $id = $this->svc->create($dto);
        if ($id === null) return new JsonResponse(['data' => ['created' => false, 'reason' => 'threshold_not_met']], 200);
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
        if (!$p) return new JsonResponse(['error' => 'not_found'], 404);
        return new JsonResponse(['data' => [
            'id' => $p->id, 'vendorId' => $p->vendorId, 'currency' => $p->currency,
            'grossCents' => $p->grossCents, 'feeCents' => $p->feeCents, 'netCents' => $p->netCents,
            'status' => $p->status, 'createdAt' => $p->createdAt, 'processedAt' => $p->processedAt,
        ]], 200);
    }
}
