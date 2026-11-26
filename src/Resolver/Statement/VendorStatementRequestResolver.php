<?php

declare(strict_types=1);

namespace App\Vendoring\Resolver\Statement;

use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\ResolverInterface\Security\VendorFinancialScopeResolverInterface;
use App\Vendoring\ResolverInterface\Statement\VendorStatementRequestResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final readonly class VendorStatementRequestResolver implements VendorStatementRequestResolverInterface
{
    private const string DEFAULT_CURRENCY = 'USD';

    public function __construct(private VendorFinancialScopeResolverInterface $financialScope)
    {
    }

    public function resolveStatementRequest(string $vendorId, Request $request): ?VendorStatementRequestDTO
    {
        $from = $this->requiredString($request, 'from');
        $to = $this->requiredString($request, 'to');

        if (null === $from || null === $to) {
            return null;
        }

        $scope = $this->financialScope->resolve($request, 'read:finance', $vendorId);
        if (null === $scope || null === $scope->getTenantId()) {
            throw new AccessDeniedHttpException('financial_scope_denied');
        }
        if (ctype_digit($scope->getLedgerVendorId()) && $scope->getLedgerVendorId() !== (string) $scope->getVendor()->getId()) {
            throw new AccessDeniedHttpException('financial_identity_mismatch');
        }

        return new VendorStatementRequestDTO(
            $scope->getTenantId(),
            $scope->getLedgerVendorId(),
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
