<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Statement\Export;

use App\Vendoring\ServiceInterface\Api\VendorStatementWindowQueryRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementExporterPdfServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRequestResolverServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\Vendoring\Support\Http\VendorApiErrorResponseTrait;
use App\Vendoring\Support\Http\VendorStatementRequestHttpResolutionTrait;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Read-side HTTP HTTP service for exporting vendor statement data as base64-wrapped PDF content.
 *
 * The HTTP service builds a statement projection for a requested tenant/vendor period and exposes
 * export metadata through JSON. It performs file inspection only for the generated export path and
 * does not send mail or mutate statement-delivery state.
 */
final class VendorStatementExportService
{
    use VendorApiErrorResponseTrait;
    use VendorStatementRequestHttpResolutionTrait;

    public function __construct(
        private readonly VendorStatementServiceInterface $svc,
        private readonly VendorStatementExporterPdfServiceInterface $pdf,
        private readonly VendorStatementRequestResolverServiceInterface $requestResolver,
        private readonly VendorStatementWindowQueryRequestResolverServiceInterface $statementWindowQueryRequestResolver,
    ) {
    }

    /**
     * Build and export a vendor statement for the requested tenant/vendor period.
     *
     * Query parameters `tenantId`, `from`, and `to` are required and forwarded unchanged to the
     * statement service. When the export file cannot be read, the response returns the stable error
     * code `statement_export_unreadable` together with the unresolved export path.
     *
     * @param string  $vendorId vendor identifier used for statement and export lookup
     * @param Request $r        HTTP request containing tenant and period query parameters
     *
     * @return JsonResponse JSON payload containing either validation/error metadata or a `data`
     *                      object with tenant/vendor scope, requested period, export path, and
     *                      base64-encoded PDF content
     *
     * @throws Exception
     */
    public function export(string $vendorId, Request $r): JsonResponse
    {
        $dto = $this->resolveStatementRequestOrErrorResponse(
            $vendorId,
            $r,
            $this->statementWindowQueryRequestResolver,
            $this->requestResolver,
        );
        if ($dto instanceof JsonResponse) {
            return $dto;
        }

        $data = $this->svc->build($dto);
        $path = $this->pdf->export($dto, $data);

        if (!is_file($path) || !is_readable($path)) {
            return $this->runtimeErrorResponse(
                'statement_export_unreadable',
                sprintf('Unable to read export file at path: %s.', $path),
            );
        }

        $content = file_get_contents($path);
        if (false === $content) {
            return $this->runtimeErrorResponse(
                'statement_export_unreadable',
                sprintf('Unable to read export file at path: %s.', $path),
            );
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
