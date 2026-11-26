<?php

declare(strict_types=1);

namespace App\Vendoring\Resolver\Security;

use App\Vendoring\Entity\Vendor\VendorLedgerBindingEntity;
use App\Vendoring\RepositoryInterface\VendorLedgerBindingRepositoryInterface;
use App\Vendoring\ResolverInterface\Security\VendorFinancialScopeResolverInterface;
use App\Vendoring\ServiceInterface\Security\VendorApiKeyServiceInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class VendorFinancialScopeResolver implements VendorFinancialScopeResolverInterface
{
    public function __construct(
        private VendorApiKeyServiceInterface $apiKeys,
        private VendorLedgerBindingRepositoryInterface $bindings,
    ) {
    }

    public function resolve(Request $request, string $permission, ?string $vendorId = null): ?VendorLedgerBindingEntity
    {
        $header = trim((string) $request->headers->get('Authorization', ''));
        if (!preg_match('/^Bearer\s+\S+$/iD', $header)) {
            return null;
        }

        $vendor = $this->apiKeys->validateAuthorizationHeader($header, $permission);
        if (null === $vendor || null === $vendor->getId()) {
            return null;
        }

        $binding = $this->bindings->findOneBy(['vendor' => $vendor]);
        if (!$binding instanceof VendorLedgerBindingEntity || $binding->getVendor()->getId() !== $vendor->getId()) {
            return null;
        }

        $tenant = $binding->getTenantId();
        $ledgerVendor = $binding->getLedgerVendorId();
        if (null === $tenant || '' === trim($tenant) || '' === trim($ledgerVendor)) {
            return null;
        }

        if (null !== $vendorId && $vendorId !== $ledgerVendor) {
            return null;
        }

        // The request may narrow the scope, never establish it.
        $query = $request->query->all();
        if (array_key_exists('tenantId', $query) && $query['tenantId'] !== $tenant) {
            return null;
        }
        if (array_key_exists('vendorId', $query) && $query['vendorId'] !== $ledgerVendor) {
            return null;
        }

        return $binding;
    }
}
