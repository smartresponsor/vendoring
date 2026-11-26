<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ledger;

final readonly class VendorLedgerAccountSumCriteriaDTO
{
    public function __construct(
        public string $tenantId,
        public string $vendorId,
        public string $accountCode,
        public ?string $from = null,
        public ?string $to = null,
        public ?string $currency = null,
    ) {
        if ('' === trim($tenantId)) {
            throw new \InvalidArgumentException('Tenant scope required');
        }
    }

    public function includesOccurredAt(\DateTimeInterface $occurredAt): bool
    {
        $value = $occurredAt->format('Y-m-d H:i:s');
        $from = $this->from;
        $to = $this->to;
        if (null !== $from && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $from)) {
            $from .= ' 00:00:00';
        }
        if (null !== $to && preg_match('/^\d{4}-\d{2}-\d{2}$/D', $to)) {
            $to .= ' 23:59:59';
        }

        return (null === $from || $value >= $from) && (null === $to || $value <= $to);
    }
}
