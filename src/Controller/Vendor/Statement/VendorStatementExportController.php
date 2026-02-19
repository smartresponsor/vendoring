<?php
declare(strict_types = 1);

namespace App\Controller\Vendor\Statement;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Vendor\Statement\StatementExporterPDF;
use App\Service\Vendor\Statement\VendorStatementService;

// from previous drop (assumed)
use App\DTO\Vendor\Statement\VendorStatementRequestDTO;

#[Route('/api/payouts/statements')]
final class VendorStatementExportController extends AbstractController
{
    public function __construct(private readonly VendorStatementService $svc, private readonly StatementExporterPDF $pdf)
    {
    }

    #[Route('/{vendorId}/export', methods: ['GET'])]
    public function export(string $vendorId, Request $r): JsonResponse
    {
        $tenantId = (string)($r->query->get('tenantId') ?? '');
        $from = (string)($r->query->get('from') ?? '');
        $to = (string)($r->query->get('to') ?? '');
        $currency = (string)($r->query->get('currency') ?? 'USD');
        if (!$tenantId || !$from || !$to) return new JsonResponse(['error' => 'params required'], 422);
        $dto = new VendorStatementRequestDTO($tenantId, $vendorId, $from, $to, $currency);
        $data = $this->svc->build($dto);
        $path = $this->pdf->export($dto, $data, null);
        $b64 = base64_encode((string)@file_get_contents($path));
        return new JsonResponse(['data' => ['pdfBase64' => $b64, 'path' => $path]], 200);
    }
}
