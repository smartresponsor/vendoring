<?php

declare(strict_types=1);

namespace App\Vendoring\Provider\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRecipientDTO;
use App\Vendoring\Entity\Vendor\VendorLedgerBindingEntity;
use App\Vendoring\ProviderInterface\Statement\VendorStatementRecipientProviderInterface;
use App\Vendoring\RepositoryInterface\VendorBillingRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorLedgerBindingRepositoryInterface;

final readonly class VendorStatementRecipientProvider implements VendorStatementRecipientProviderInterface
{
    public function __construct(
        private VendorBillingRepositoryInterface $billings,
        private VendorLedgerBindingRepositoryInterface $bindings,
    ) {
    }

    public function forPeriod(string $from, string $to): array
    {
        $recipients = [];

        foreach ($this->billings->findAll() as $billing) {
            $vendorId = $billing->getVendor()->getId();
            $email = null !== $billing->getBillingEmail() ? trim($billing->getBillingEmail()) : '';

            if (null === $vendorId || '' === $email) {
                continue;
            }

            $binding = $this->bindings->findOneBy(['vendor' => $billing->getVendor()]);
            if (!$binding instanceof VendorLedgerBindingEntity || null === $binding->getTenantId() || '' === trim($binding->getTenantId())) {
                continue;
            }

            $recipients[] = new VendorStatementRecipientDTO(
                tenantId: $binding->getTenantId(),
                vendorId: $binding->getLedgerVendorId(),
                email: $email,
                currency: 'USD',
            );
        }

        return $recipients;
    }
}
