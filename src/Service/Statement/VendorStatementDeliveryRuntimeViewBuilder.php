<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\DTO\Statement\VendorStatementRequestDTO;
use App\Projection\VendorStatementDeliveryRuntimeView;
use App\ServiceInterface\Statement\StatementExporterPDFInterface;
use App\ServiceInterface\Statement\VendorStatementDeliveryRuntimeViewBuilderInterface;
use App\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;

/**
 * Builds a vendor-local statement delivery summary with ownership, export and
 * recipient surfaces kept adjacent for runtime inspection.
 */
final class VendorStatementDeliveryRuntimeViewBuilder implements VendorStatementDeliveryRuntimeViewBuilderInterface
{
    public function __construct(
        private readonly VendorOwnershipViewBuilderInterface $ownershipViewBuilder,
        private readonly VendorStatementServiceInterface $statementService,
        private readonly StatementExporterPDFInterface $statementExporter,
        private readonly VendorStatementRecipientProviderInterface $recipientProvider,
    ) {
    }

    /**
     * Builds the requested read model.
     */
    public function build(
        string $tenantId,
        string $vendorId,
        string $from,
        string $to,
        string $currency = 'USD',
        bool $includeExport = true,
    ): VendorStatementDeliveryRuntimeView {
        $dto = new VendorStatementRequestDTO($tenantId, $vendorId, $from, $to, $currency);
        $statement = $this->statementService->build($dto);

        $ownership = null;
        if (ctype_digit($vendorId)) {
            $ownershipView = $this->ownershipViewBuilder->buildForVendorId((int) $vendorId);
            $ownership = $ownershipView?->toArray();
        }

        $export = null;
        if ($includeExport) {
            $path = $this->statementExporter->export($dto, $statement, null);
            $export = [
                'path' => $path,
                'exists' => is_file($path),
                'readable' => is_readable($path),
            ];
        }

        $recipients = [];
        foreach ($this->recipientProvider->forPeriod($from, $to) as $candidate) {
            if ($candidate->tenantId !== $tenantId || $candidate->vendorId !== $vendorId) {
                continue;
            }

            $recipients[] = [
                'tenantId' => $candidate->tenantId,
                'vendorId' => $candidate->vendorId,
                'email' => $candidate->email,
                'currency' => $candidate->currency,
            ];
        }

        return new VendorStatementDeliveryRuntimeView(
            tenantId: $tenantId,
            vendorId: $vendorId,
            currency: $currency,
            ownership: $ownership,
            statement: $statement,
            export: $export,
            recipients: $recipients,
        );
    }
}
