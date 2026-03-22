<?php

declare(strict_types=1);

namespace App\Controller\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\ServiceInterface\Statement\StatementExporterPDFInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payouts/statements')]
final class VendorStatementExportController extends AbstractController
{
    public function __construct(
        private readonly VendorStatementServiceInterface $svc,
        private readonly StatementExporterPDFInterface $pdf,
    ) {
    }

    #[Route('/{vendorId}/export', methods: ['GET'])]
    public function export(string $vendorId, Request $r): JsonResponse
    {
        $tenantId = (string) ($r->query->get('tenantId') ?? '');
        $from = (string) ($r->query->get('from') ?? '');
        $to = (string) ($r->query->get('to') ?? '');
        $currency = (string) ($r->query->get('currency') ?? 'USD');

        if ('' === $tenantId || '' === $from || '' === $to) {
            return new JsonResponse(['error' => 'params required'], 422);
        }

        $dto = new VendorStatementRequestDTO($tenantId, $vendorId, $from, $to, $currency);
        $data = $this->svc->build($dto);
        $path = $this->pdf->export($dto, $data, null);

        if (!is_file($path) || !is_readable($path)) {
            return new JsonResponse([
                'error' => 'statement_export_unreadable',
                'data' => [
                    'tenantId' => $dto->tenantId,
                    'vendorId' => $dto->vendorId,
                    'path' => $path,
                ],
            ], 500);
        }

        $content = file_get_contents($path);
        if (false === $content) {
            return new JsonResponse([
                'error' => 'statement_export_unreadable',
                'data' => [
                    'tenantId' => $dto->tenantId,
                    'vendorId' => $dto->vendorId,
                    'path' => $path,
                ],
            ], 500);
        }

        return new JsonResponse(['data' => [
            'tenantId' => $dto->tenantId,
            'vendorId' => $dto->vendorId,
            'from' => $dto->from,
            'to' => $dto->to,
            'currency' => $dto->currency,
            'pdfBase64' => base64_encode($content),
            'path' => $path,
        ]], 200);
    }
}
