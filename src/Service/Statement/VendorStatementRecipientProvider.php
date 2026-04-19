<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRecipientDTO;
use App\Vendoring\RepositoryInterface\VendorBillingRepositoryInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;

final readonly class VendorStatementRecipientProvider implements VendorStatementRecipientProviderInterface
{
    public function __construct(private VendorBillingRepositoryInterface $billings) {}

    public function forPeriod(string $from, string $to): array
    {
        $recipients = [];

        foreach ($this->billings->findAll() as $billing) {
            $vendorId = $billing->getVendor()->getId();
            $email = $billing->getBillingEmail() !== null ? trim($billing->getBillingEmail()) : '';

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
