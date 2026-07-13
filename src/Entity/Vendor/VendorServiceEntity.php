<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorServiceRepository::class)]
#[ORM\Table(name: 'vendor_service')]
class VendorServiceEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $code = null;
    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')] private array $payload = [];
    /** @param array<string, mixed> $payload */
    public function __construct(?string $code = null, array $payload = [])
    {
        parent::__construct('active');
        $this->code = $code;
        $this->payload = $payload;
    }
}
