<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Statement;

use Doctrine\DBAL\Exception;
use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Projection\Vendor\VendorStatementDeliveryRuntimeProjection;
use App\Vendoring\ServiceInterface\Statement\VendorStatementExporterPdfServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRecipientProviderServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;

/**
 * Builds a vendor-local statement delivery summary with ownership, export and
 * recipient surfaces kept adjacent for runtime inspection.
 */
final readonly class VendorStatementDeliveryRuntimeProjectionBuilderService implements VendorStatementDeliveryRuntimeProjectionBuilderServiceInterface
{
    public function __construct(
        private VendorOwnershipProjectionBuilderServiceInterface $ownershipProjectionBuilder,
        private VendorStatementServiceInterface $statementService,
        private VendorStatementExporterPdfServiceInterface $statementExporter,
        private VendorStatementRecipientProviderServiceInterface $recipientProvider,
    ) {}

    /** @throws Exception */
    public function build(VendorStatementDeliveryRuntimeRequestDTO $request): VendorStatementDeliveryRuntimeProjection
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
            $ownershipProjection = $this->ownershipProjectionBuilder->buildForVendorId((int) $request->vendorId);
            $ownership = $ownershipProjection?->toArray();
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

        return new VendorStatementDeliveryRuntimeProjection(
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
