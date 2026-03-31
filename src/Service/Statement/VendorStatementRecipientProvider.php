<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\DTO\Statement\VendorStatementRecipientDTO;
use App\Entity\VendorBilling;
use App\RepositoryInterface\VendorBillingRepositoryInterface;
use App\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;

final class VendorStatementRecipientProvider implements VendorStatementRecipientProviderInterface
{
    public function __construct(private readonly VendorBillingRepositoryInterface $billings)
    {
    }

    public function forPeriod(string $from, string $to): array
    {
        $recipients = [];

        foreach ($this->billings->findAll() as $billing) {
            if (!$billing instanceof VendorBilling) {
                continue;
            }

            $email = trim((string) $billing->getBillingEmail());
            $vendorId = $billing->getVendor()->getId();

            if ('' === $email || null === $vendorId) {
                continue;
            }

            $recipients[] = new VendorStatementRecipientDTO(
                tenantId: 'default',
                vendorId: (string) $vendorId,
                email: $email,
                currency: 'USD',
            );
        }

        return $recipients;
    }
}
