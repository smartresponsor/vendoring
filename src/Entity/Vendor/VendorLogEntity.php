<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_log')]
#[ORM\Index(name: 'idx_vendor_log_vendor_created_at', columns: ['vendor_id', 'created_at'])]
final class VendorLogEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(name: 'action_name', type: 'string', length: 128)]
    private string $actionName;

    #[ORM\Column(name: 'payload_json', type: 'text')]
    private string $payloadJson;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    /** @param array<string, mixed> $payload */
    public function __construct(VendorEntity $vendor, string $actionName, array $payload)
    {
        $this->vendor = $vendor;
        $this->actionName = $actionName;
        $this->payloadJson = (string) json_encode($payload, JSON_THROW_ON_ERROR);
        $this->createdAt = new DateTimeImmutable();
    }
}
