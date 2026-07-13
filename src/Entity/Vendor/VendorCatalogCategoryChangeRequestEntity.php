<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorCatalogCategoryChangeRequestRepository::class)]
#[ORM\Table(name: 'vendor_catalog_category_change_request')]
class VendorCatalogCategoryChangeRequestEntity extends VendorAbstractEntity
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

    public function categoryId(): string
    {
        $categoryId = $this->payload['categoryId'] ?? '';

        return is_string($categoryId) ? $categoryId : '';
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        return $this->payload;
    }
}
