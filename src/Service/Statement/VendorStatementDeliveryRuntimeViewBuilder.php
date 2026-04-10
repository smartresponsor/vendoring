<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
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
final readonly class VendorStatementDeliveryRuntimeViewBuilder implements VendorStatementDeliveryRuntimeViewBuilderInterface
{
    public function __construct(
        private VendorOwnershipViewBuilderInterface $ownershipViewBuilder,
        private VendorStatementServiceInterface $statementService,
        private StatementExporterPDFInterface $statementExporter,
        private VendorStatementRecipientProviderInterface $recipientProvider,
    ) {
    }

    public function build(VendorStatementDeliveryRuntimeRequestDTO $request): VendorStatementDeliveryRuntimeView
    {
        $dto = new VendorStatementRequestDTO(
            $request->tenantId,
            $request->vendorId,
            $request->from,
            $request->to,
            $request->currency,
        );
        $statement = $this->statementService->build($dto);

        $ownership = null;
        if (ctype_digit($request->vendorId)) {
            $ownershipView = $this->ownershipViewBuilder->buildForVendorId((int) $request->vendorId);
            $ownership = $ownershipView?->toArray();
        }

        $export = null;
        if ($request->includeExport) {
            $path = $this->statementExporter->export($dto, $statement);
            $export = [
                'path' => $path,
                'exists' => is_file($path),
                'readable' => is_readable($path),
            ];
        }

        $recipients = [];
        foreach ($this->recipientProvider->forPeriod($request->from, $request->to) as $candidate) {
            if ($candidate->tenantId !== $request->tenantId || $candidate->vendorId !== $request->vendorId) {
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
            tenantId: $request->tenantId,
            vendorId: $request->vendorId,
            currency: $request->currency,
            ownership: $ownership,
            statement: $statement,
            export: $export,
            recipients: $recipients,
        );
    }
}
