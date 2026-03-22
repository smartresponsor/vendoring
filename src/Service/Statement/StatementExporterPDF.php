<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\ServiceInterface\Statement\StatementExporterPDFInterface;

/**
 * Lightweight PDF generator (no external deps): writes a minimal PDF with plain text.
 * For production replace with TCPDF/FPDF or a real renderer.
 */
final class StatementExporterPDF implements StatementExporterPDFInterface
{
    /** Returns absolute filepath to generated PDF */
    public function export(VendorStatementRequestDTO $dto, array $data, ?string $logoPath = null): string
    {
        $fromTs = strtotime($dto->from);
        $toTs = strtotime($dto->to);

        $dir = sprintf(
            'var/statements/%s/%s/%s',
            date('Y', $fromTs),
            date('m', $fromTs),
            $dto->vendorId
        );

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $dir.'/statement_'.date('Ymd', $fromTs).'_'.date('Ymd', $toTs).'.pdf';

        $text = sprintf(
            "Vendor Statement\nTenant: %s\nVendor: %s\nPeriod: %s .. %s\nCurrency: %s\n---\nOpening: %.2f\nEarnings: %.2f\nRefunds: %.2f\nFees: %.2f\nClosing: %.2f\n",
            $dto->tenantId,
            $dto->vendorId,
            $dto->from,
            $dto->to,
            $dto->currency,
            (float) ($data['opening'] ?? 0),
            (float) ($data['earnings'] ?? 0),
            (float) ($data['refunds'] ?? 0),
            (float) ($data['fees'] ?? 0),
            (float) ($data['closing'] ?? 0)
        );

        $this->writeMinimalPdf($file, $text);

        return $file;
    }

    private function writeMinimalPdf(string $path, string $text): void
    {
        $content = "%PDF-1.4\n";
        $content .= "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n";
        $content .= "2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n";

        $stream = 'BT /F1 12 Tf 72 720 Td ('.$this->escape($text).') Tj ET';
        $len = strlen($stream);

        $content .= "3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n";
        $content .= "4 0 obj<</Length $len>>stream\n$stream\nendstream endobj\n";
        $content .= "5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n";

        $xrefPos = strlen($content);
        $content .= "xref\n0 6\n0000000000 65535 f \n";

        // Minimal cross-reference trailer for the generated single-page document.
        $content .= "trailer<</Size 6/Root 1 0 R>>\nstartxref\n$xrefPos\n%%EOF";

        file_put_contents($path, $content);
    }

    private function escape(string $s): string
    {
        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', '', ') ('],
            $s
        );
    }
}
