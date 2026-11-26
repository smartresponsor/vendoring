<?php

declare(strict_types=1);

namespace App\Vendoring\Builder\Statement;

use App\Vendoring\BuilderInterface\Ownership\VendorOwnershipProjectionBuilderInterface;
use App\Vendoring\BuilderInterface\Statement\VendorStatementDeliveryRuntimeProjectionBuilderInterface;
use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Projection\VendorStatementDeliveryRuntimeProjection;
use App\Vendoring\ProviderInterface\Statement\VendorStatementRecipientProviderInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementExporterPdfServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use Doctrine\DBAL\Exception;

/**
 * Builds a vendor-local statement delivery summary with ownership, export and
 * recipient surfaces kept adjacent for runtime inspection.
 */
final readonly class VendorStatementDeliveryRuntimeProjectionBuilder implements VendorStatementDeliveryRuntimeProjectionBuilderInterface
{
    public function __construct(
        private VendorOwnershipProjectionBuilderInterface $ownershipProjectionBuilder,
        private VendorStatementServiceInterface $statementService,
        private VendorStatementExporterPdfServiceInterface $statementExporter,
        private VendorStatementRecipientProviderInterface $recipientProvider,
    ) {
    }

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
        if (null !== $request->canonicalVendorId) {
            $ownershipProjection = $this->ownershipProjectionBuilder->buildForVendorId($request->canonicalVendorId);
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
