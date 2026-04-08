<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Controller\Ledger;

use App\ServiceInterface\Ledger\VendorSummaryServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for vendor summary operations.
 */
#[Route('/api/ledger/vendor')]
final class VendorSummaryController extends AbstractController
{
    public function __construct(private readonly VendorSummaryServiceInterface $vendorSummaryService)
    {
    }

    /**
     * Executes the summary operation for this runtime surface.
     */
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
        $summary = $this->vendorSummaryService->build($tenantId, $vendorId, $from, $to, $currency);

        return new JsonResponse(['data' => $summary], 200);
    }
}
