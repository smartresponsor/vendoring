<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\ServiceInterface\Statement\VendorStatementExporterPDFServiceInterface;

final class VendorStatementExporterPDFService implements VendorStatementExporterPDFServiceInterface
{
    /** @param array{tenantId:string, vendorId:string, from:string, to:string, currency:string, opening:float, earnings:float, refunds:float, fees:float, closing:float, items:list<array{type:string, amount:float, currency:string}>} $data */
    public function export(VendorStatementRequestDTO $dto, array $data, ?string $logoPath = null): string
    {
        $fromTs = $this->safeTimestamp($dto->from);
        $toTs = $this->safeTimestamp($dto->to);
        $dir = sprintf('var/statements/%s/%s/%s', date('Y', $fromTs), date('m', $fromTs), $dto->vendorId);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $dir . '/statement_' . date('Ymd', $fromTs) . '_' . date('Ymd', $toTs) . '.pdf';
        $text = sprintf(
            "VendorEntity Statement\nTenant: %s\nVendor: %s\nPeriod: %s .. %s\nCurrency: %s\n---\nOpening: %.2f\nEarnings: %.2f\nRefunds: %.2f\nFees: %.2f\nClosing: %.2f\n",
            $dto->tenantId,
            $dto->vendorId,
            $dto->from,
            $dto->to,
            $dto->currency,
            $data['opening'],
            $data['earnings'],
            $data['refunds'],
            $data['fees'],
            $data['closing'],
        );

        $this->writeMinimalPdf($file, $text);

        return $file;
    }

    private function safeTimestamp(string $value): int
    {
        $timestamp = strtotime($value);

        return false === $timestamp ? time() : $timestamp;
    }

    private function writeMinimalPdf(string $path, string $text): void
    {
        $content = "%PDF-1.4\n";
        $content .= "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n";
        $content .= "2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n";
        $stream = 'BT /F1 12 Tf 72 720 Td (' . $this->escape($text) . ') Tj ET';
        $len = strlen($stream);
        $content .= "3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n";
        $content .= "4 0 obj<</Length $len>>stream\n$stream\nendstream endobj\n";
        $content .= "5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n";
        $xrefPos = strlen($content);
        $content .= "xref\n0 6\n0000000000 65535 f \n";
        $content .= "trailer<</Size 6/Root 1 0 R>>\nstartxref\n$xrefPos\n%%EOF";
        file_put_contents($path, $content);
    }

    private function escape(string $s): string
    {
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ') ('], $s);
    }
}
