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

/**
 * Read-side HTTP controller for exporting vendor statement data as base64-wrapped PDF content.
 *
 * The controller builds a statement projection for a requested tenant/vendor period and exposes
 * export metadata through JSON. It performs file inspection only for the generated export path and
 * does not send mail or mutate statement-delivery state.
 */
#[Route('/api/payouts/statements')]
final class VendorStatementExportController extends AbstractController
{
    public function __construct(
        private readonly VendorStatementServiceInterface $svc,
        private readonly StatementExporterPDFInterface $pdf,
    ) {
    }

    /**
     * Build and export a vendor statement for the requested tenant/vendor period.
     *
     * Query parameters `tenantId`, `from`, and `to` are required and forwarded unchanged to the
     * statement service. When the export file cannot be read, the response returns the stable error
     * code `statement_export_unreadable` together with the unresolved export path.
     *
     * @param string  $vendorId Vendor identifier used for statement and export lookup.
     * @param Request $r        HTTP request containing tenant and period query parameters.
     *
     * @return JsonResponse JSON payload containing either validation/error metadata or a `data`
     *                      object with tenant/vendor scope, requested period, export path, and
     *                      base64-encoded PDF content.
     */
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
