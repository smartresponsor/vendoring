<?php
declare(strict_types = 1);

namespace App\Controller\Vendor\Payout;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Vendor\Statement\VendorStatementService;
use App\DTO\Vendor\Statement\VendorStatementRequestDTO;

#[Route('/api/payouts/statements')]
final class VendorStatementController extends AbstractController
{
    public function __construct(private readonly VendorStatementService $svc)
    {
    }

    #[Route('/{vendorId}', methods: ['GET'])]
    public function build(string $vendorId, Request $r): JsonResponse
    {
        $tenantId = (string)($r->query->get('tenantId') ?? '');
        $from = (string)($r->query->get('from') ?? '');
        $to = (string)($r->query->get('to') ?? '');
        $currency = (string)($r->query->get('currency') ?? 'USD');
        if (!$tenantId || !$from || !$to) return new JsonResponse(['error' => 'params required'], 422);
        $dto = new VendorStatementRequestDTO($tenantId, $vendorId, $from, $to, $currency);
        $data = $this->svc->build($dto);
        return new JsonResponse(['data' => $data], 200);
    }
}
