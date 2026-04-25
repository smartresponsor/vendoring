<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\VendorAnalyticsRepository')]
#[ORM\Table(name: 'vendor_analytics')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_analytics_vendor', columns: ['vendor_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorAnalytics
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private readonly Vendor $vendor;

    /** @var array<string,mixed> */
    #[ORM\Column(type: 'json')]
    private array $metrics = [];

    /** @param array<string,mixed> $metrics */
    public function __construct(Vendor $vendor, array $metrics = [])
    {
        $this->vendor = $vendor;
        $this->metrics = $metrics;
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    /** @return array<string,mixed> */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
