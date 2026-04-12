<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\DTO\Statement\VendorStatementRequestDTO;
use App\ServiceInterface\Statement\VendorStatementRequestResolverInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorStatementRequestResolver implements VendorStatementRequestResolverInterface
{
    private const string DEFAULT_CURRENCY = 'USD';

    public function resolveStatementRequest(string $vendorId, Request $request): ?VendorStatementRequestDTO
    {
        $tenantId = $this->requiredString($request, 'tenantId');
        $from = $this->requiredString($request, 'from');
        $to = $this->requiredString($request, 'to');

        if (null === $tenantId || null === $from || null === $to) {
            return null;
        }

        return new VendorStatementRequestDTO(
            $tenantId,
            $vendorId,
            $from,
            $to,
            $this->resolveCurrency($request),
        );
    }

    public function resolveDeliveryRuntimeRequest(string $vendorId, Request $request): ?VendorStatementDeliveryRuntimeRequestDTO
    {
        $statementRequest = $this->resolveStatementRequest($vendorId, $request);
        if (null === $statementRequest) {
            return null;
        }

        return new VendorStatementDeliveryRuntimeRequestDTO(
            tenantId: $statementRequest->tenantId,
            vendorId: $statementRequest->vendorId,
            from: $statementRequest->from,
            to: $statementRequest->to,
            currency: $statementRequest->currency,
            includeExport: filter_var($request->query->get('includeExport', true), FILTER_VALIDATE_BOOL),
        );
    }

    private function requiredString(Request $request, string $key): ?string
    {
        return $this->nullableQueryString($request, $key);
    }

    private function resolveCurrency(Request $request): string
    {
        return $this->nullableQueryString($request, 'currency') ?? self::DEFAULT_CURRENCY;
    }

    private function nullableQueryString(Request $request, string $key): ?string
    {
        $value = $request->query->get($key);
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return '' === $normalized ? null : $normalized;
    }
}
