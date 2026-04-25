<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Payout;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payout_items')]
#[ORM\Index(name: 'idx_payout_item_payout', columns: ['payout_id'])]
final class PayoutItem
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 36)]
        public string $id,
        #[ORM\Column(name: 'payout_id', type: 'string', length: 36)]
        public string $payoutId,
        #[ORM\Column(name: 'entry_id', type: 'string', length: 64)]
        public string $entryId,
        #[ORM\Column(name: 'amount_cents', type: 'integer')]
        public int $amountCents,
    ) {}
}
