<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_commission_history')]
#[ORM\Index(name: 'idx_vendor_commission_history_vendor_changed_at', columns: ['vendor_id', 'changed_at'])]
final class VendorCommissionHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\ManyToOne(targetEntity: VendorCommission::class)]
    #[ORM\JoinColumn(name: 'vendor_commission_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?VendorCommission $commission;

    #[ORM\Column(name: 'changed_by_user_id', type: 'integer', nullable: true)]
    private ?int $changedByUserId;

    #[ORM\Column(name: 'previous_rate_percent', type: 'decimal', precision: 6, scale: 2, nullable: true)]
    private ?string $previousRatePercent;

    #[ORM\Column(name: 'new_rate_percent', type: 'decimal', precision: 6, scale: 2)]
    private string $newRatePercent;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $reason;

    #[ORM\Column(name: 'changed_at', type: 'datetime_immutable')]
    private DateTimeImmutable $changedAt;

    public function __construct(Vendor $vendor, ?VendorCommission $commission, ?string $previousRatePercent, string $newRatePercent, ?int $changedByUserId = null, ?string $reason = null)
    {
        $this->vendor = $vendor;
        $this->commission = $commission;
        $this->previousRatePercent = $previousRatePercent;
        $this->newRatePercent = $newRatePercent;
        $this->changedByUserId = $changedByUserId;
        $this->reason = $reason;
        $this->changedAt = new DateTimeImmutable();
    }
}
