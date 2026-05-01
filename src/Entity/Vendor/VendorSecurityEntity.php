<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use App\Vendoring\EntityInterface\Vendor\VendorSecurityEntityInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Transitional vendor-owned security state.
 *
 * This entity is not the human identity/authentication model of Vendoring.
 * It only reflects lightweight vendor-local state while canonical machine
 * access lives in VendorApiKeyEntity and external human credentials remain outside
 * this boundary.
 */
#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\Vendor\\VendorSecurityRepository')]
#[ORM\Table(name: 'vendor_security')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_security_vendor', columns: ['vendor_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorSecurityEntity implements VendorSecurityEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    public function __construct(VendorEntity $vendor, string $status = 'active')
    {
        $this->vendor = $vendor;
        $this->status = $status;
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getVendorId(): ?int
    {
        return $this->vendor->getId();
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
