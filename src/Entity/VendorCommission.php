<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_commission')]
#[ORM\Index(name: 'idx_vendor_commission_vendor_status', columns: ['vendor_id', 'status'])]
final class VendorCommission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(type: 'string', length: 64)]
    private string $code;

    #[ORM\Column(type: 'string', length: 32)]
    private string $direction;

    #[ORM\Column(name: 'rate_percent', type: 'decimal', precision: 6, scale: 2)]
    private string $ratePercent;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'active';

    #[ORM\Column(name: 'effective_from', type: 'datetime_immutable')]
    private DateTimeImmutable $effectiveFrom;

    #[ORM\Column(name: 'effective_to', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $effectiveTo = null;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $meta = [];

    /** @param array<string, mixed> $meta */
    public function __construct(Vendor $vendor, string $code, string $direction, string $ratePercent, array $meta = [])
    {
        $this->vendor = $vendor;
        $this->code = $code;
        $this->direction = $direction;
        $this->ratePercent = $ratePercent;
        $this->meta = $meta;
        $this->effectiveFrom = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function getRatePercent(): string
    {
        return $this->ratePercent;
    }

    /** @param array<string, mixed> $meta */
    public function updateConfiguration(string $direction, string $ratePercent, string $status = 'active', array $meta = [], ?DateTimeImmutable $effectiveTo = null): void
    {
        $this->direction = $direction;
        $this->ratePercent = $ratePercent;
        $this->status = $status;
        $this->effectiveTo = $effectiveTo;
        $this->meta = [] === $meta ? $this->meta : $meta;
    }
}
