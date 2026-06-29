<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Statement;

use App\Vendoring\DTO\Statement\VendorStatementRecipientDTO;
use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorBillingRepositoryInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementRecipientProviderServiceInterface;

final readonly class VendorStatementRecipientProviderService implements VendorStatementRecipientProviderServiceInterface
{
    public function __construct(private VendorBillingRepositoryInterface $billings)
    {
    }

    public function forPeriod(string $from, string $to): array
    {
        $recipients = [];

        foreach ($this->billings->findAll() as $billing) {
            if (!$billing instanceof VendorBillingEntity) {
                continue;
            }

            $vendorId = $billing->getVendor()->getId();
            $email = null !== $billing->getBillingEmail() ? trim($billing->getBillingEmail()) : '';

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
