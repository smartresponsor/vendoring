<?php

declare(strict_types=1);

namespace App\Service\Statement;

use App\DTO\Statement\VendorStatementRecipientDTO;
use App\RepositoryInterface\VendorBillingRepositoryInterface;
use App\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;

final readonly class VendorStatementRecipientProvider implements VendorStatementRecipientProviderInterface
{
    public function __construct(private VendorBillingRepositoryInterface $billings) {}

    public function forPeriod(string $from, string $to): array
    {
        $recipients = [];

        foreach ($this->billings->findAll() as $billing) {
            $vendorId = $billing->getVendor()->getId();
            $email = trim((string) ($billing->getBillingEmail() ?? ''));

            if (null === $vendorId || '' === $email) {
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
