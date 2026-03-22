<?php

declare(strict_types=1);

namespace App\Controller\Ledger;

use App\RepositoryInterface\Ledger\LedgerEntryRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ledger/vendor')]
final class VendorSummaryController extends AbstractController
{
    public function __construct(private readonly LedgerEntryRepositoryInterface $repo)
    {
    }

    #[Route('/{vendorId}/summary', methods: ['GET'])]
    public function summary(string $vendorId, Request $r): JsonResponse
    {
        $tenantId = (string) ($r->query->get('tenantId') ?? '');
        $from = (string) ($r->query->get('from') ?? '');
        $to = (string) ($r->query->get('to') ?? '');
        $currency = (string) ($r->query->get('currency') ?? '');
        if (!$tenantId) {
            return new JsonResponse(['error' => 'tenantId required'], 422);
        }
        $accounts = ['REVENUE', 'REFUNDS_PAYABLE', 'VENDOR_PAYABLE', 'CASH'];
        $data = [];
        foreach ($accounts as $acc) {
            $data[$acc] = $this->repo->sumByAccount($tenantId, $acc, $from ?: null, $to ?: null, $vendorId, '' !== $currency ? $currency : null);
        }

        return new JsonResponse(['data' => ['vendorId' => $vendorId, 'from' => $from, 'to' => $to, 'currency' => $currency, 'balances' => $data]], 200);
    }
}
