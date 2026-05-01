<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\Vendor\\VendorAnalyticsRepository')]
#[ORM\Table(name: 'vendor_analytics')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_analytics_vendor', columns: ['vendor_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorAnalyticsEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private readonly VendorEntity $vendor;

    /** @var array<string,mixed> */
    #[ORM\Column(type: 'json')]
    private array $metrics = [];

    /** @param array<string,mixed> $metrics */
    public function __construct(VendorEntity $vendor, array $metrics = [])
    {
        $this->vendor = $vendor;
        $this->metrics = $metrics;
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    /** @return array<string,mixed> */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
}
