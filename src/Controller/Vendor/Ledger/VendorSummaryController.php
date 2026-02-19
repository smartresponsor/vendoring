<?php
declare(strict_types = 1);

namespace App\Controller\Vendor\Ledger;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\Vendor\Ledger\LedgerEntryRepository;

#[Route('/api/ledger/vendor')]
final class VendorSummaryController extends AbstractController
{
    public function __construct(private readonly LedgerEntryRepository $repo)
    {
    }

    #[Route('/{vendorId}/summary', methods: ['GET'])]
    public function summary(string $vendorId, Request $r): JsonResponse
    {
        $tenantId = (string)($r->query->get('tenantId') ?? '');
        $from = (string)($r->query->get('from') ?? '');
        $to = (string)($r->query->get('to') ?? '');
        if (!$tenantId) return new JsonResponse(['error' => 'tenantId required'], 422);
        $accounts = ['REVENUE', 'REFUNDS_PAYABLE', 'VENDOR_PAYABLE', 'CASH'];
        $data = [];
        foreach ($accounts as $acc) {
            $data[$acc] = $this->repo->sumByAccount($tenantId, $acc, $from ?: null, $to ?: null, $vendorId);
        }
        return new JsonResponse(['data' => ['vendorId' => $vendorId, 'from' => $from, 'to' => $to, 'balances' => $data]], 200);
    }
}
