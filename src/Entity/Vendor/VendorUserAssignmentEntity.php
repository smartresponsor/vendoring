<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use App\Vendoring\EntityInterface\Vendor\VendorUserAssignmentEntityInterface;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\Vendor\\VendorUserAssignmentRepository')]
#[ORM\Table(name: 'vendor_user_assignment')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_user_assignment_vendor_user', columns: ['vendor_id', 'user_id'])]
#[ORM\Index(name: 'idx_vendor_user_assignment_vendor_status', columns: ['vendor_id', 'status'])]
#[ORM\Index(name: 'idx_vendor_user_assignment_user_status', columns: ['user_id', 'status'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorUserAssignmentEntity implements VendorUserAssignmentEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'vendor_id', type: 'integer')]
    private readonly int $vendorId;

    #[ORM\Column(name: 'user_id', type: 'integer')]
    private readonly int $userId;

    #[ORM\Column(type: 'string', length: 64)]
    private string $role;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    #[ORM\Column(name: 'is_primary', type: 'boolean')]
    private bool $isPrimary;

    #[ORM\Column(name: 'granted_at', type: 'datetime_immutable')]
    private DateTimeImmutable $grantedAt;

    #[ORM\Column(name: 'revoked_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $revokedAt;

    public function __construct(
        int $vendorId,
        int $userId,
        string $role = 'owner',
        string $status = 'active',
        bool $isPrimary = false,
        ?DateTimeImmutable $grantedAt = null,
        ?DateTimeImmutable $revokedAt = null,
    ) {
        $this->vendorId = $vendorId;
        $this->userId = $userId;
        $this->role = trim($role);
        $this->status = trim($status);
        $this->isPrimary = $isPrimary;
        $this->grantedAt = $grantedAt ?? new DateTimeImmutable();
        $this->revokedAt = $revokedAt;
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendorId(): int
    {
        return $this->vendorId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function getGrantedAt(): DateTimeImmutable
    {
        return $this->grantedAt;
    }

    public function getRevokedAt(): ?DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function markPrimary(): void
    {
        $this->isPrimary = true;
    }

    public function clearPrimary(): void
    {
        $this->isPrimary = false;
    }

    public function revoke(): void
    {
        $this->status = 'revoked';
        $this->isPrimary = false;
        $this->revokedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->status = 'active';
        $this->revokedAt = null;
    }

    public function changeRole(string $role): void
    {
        $this->role = trim($role);
    }
}
