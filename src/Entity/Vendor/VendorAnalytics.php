<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\Vendor\\VendorAnalyticsRepository')]
#[ORM\Table(name: 'vendor_analytics')]
class VendorAnalytics
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\OneToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(type: 'bigint')]
    private int $totalOrders = 0;

    #[ORM\Column(type: 'bigint')]
    private int $totalRevenueMinor = 0;

    #[ORM\Column(type: 'float')]
    private float $avgRating = 0.0;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $periodStart;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $periodEnd;

    public function __construct(Vendor $vendor, \DateTimeImmutable $start, \DateTimeImmutable $end)
    {
        $this->vendor = $vendor;
        $this->periodStart = $start;
        $this->periodEnd = $end;
    }
}
