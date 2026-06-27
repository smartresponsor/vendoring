<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_catalog_category_pin')]
class VendorCatalogCategoryPinEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $code = null;
    #[ORM\Column(type: 'json')] private array $payload = [];
    public function __construct(?string $code = null, array $payload = [])
    {
        parent::__construct('active');
        $this->code = $code;
        $this->payload = $payload;
    }

    public function recordId(): string
    {
        return (string) ($this->payload['recordId'] ?? '');
    }

    public function position(): int
    {
        return (int) ($this->payload['position'] ?? 0);
    }

    public function reorder(int $position): void
    {
        $this->payload['position'] = $position;
    }
}
