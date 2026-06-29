<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorPayoutItemRepository::class)]
#[ORM\Table(
    name: 'vendor_payout_item',
    indexes: [
        new ORM\Index(name: 'idx_vendor_payout_item_payout_id', columns: ['payout_id']),
    ],
)]
class VendorPayoutItemEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorPayoutEntity::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] public VendorPayoutEntity $payout;
    #[ORM\Column(type: 'string', length: 64)] public string $entryId;
    #[ORM\Column(type: 'integer')] public int $amountCents;
    public function __construct(VendorPayoutEntity $payout, string $entryId, int $amountCents)
    {
        parent::__construct();
        $this->payout = $payout;
        $this->entryId = $entryId;
        $this->amountCents = $amountCents;
    }
}
