<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorAnalyticsRepository::class)]
#[ORM\Table(name: 'vendor_analytics')]
class VendorAnalyticsEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(targetEntity: VendorEntity::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')] private array $metrics = [];
    /** @param array<string, mixed> $metrics */
    public function __construct(VendorEntity $vendor, array $metrics = [])
    {
        parent::__construct();
        $this->vendor = $vendor;
        $this->metrics = $metrics;
    }

    /** @param array<string, mixed> $metrics */
    public function replaceMetrics(array $metrics): self
    {
        $this->metrics = $metrics;

        return $this;
    }
}
