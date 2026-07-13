<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorLogRepository::class)]
#[ORM\Table(name: 'vendor_log')]
class VendorLogEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'logs')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $actionName = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $payloadJson = '';
    /** @param array<string, mixed> $payload */
    public function __construct(VendorEntity $vendor, string $actionName, array $payload)
    {
        parent::__construct();
        $this->vendor = $vendor;
        $this->actionName = $actionName;
        $this->payloadJson = json_encode($payload, JSON_THROW_ON_ERROR);
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    public function getPayloadJson(): string
    {
        return $this->payloadJson;
    }
}
