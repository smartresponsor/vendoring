<?php

declare(strict_types=1);

namespace App\Vendoring\Provider\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRecipientDTO;
use App\Vendoring\ProviderInterface\Statement\VendorStatementRecipientProviderInterface;
use App\Vendoring\RepositoryInterface\VendorBillingRepositoryInterface;

final readonly class VendorStatementRecipientProvider implements VendorStatementRecipientProviderInterface
{
    public function __construct(private VendorBillingRepositoryInterface $billings)
    {
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

            $recipients[] = new VendorStatementRecipientDTO(
                vendorId: (string) $vendorId,
                email: $email,
                currency: 'USD',
            );
        }

        return $recipients;
    }
}
