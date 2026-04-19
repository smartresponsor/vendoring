<?php

declare(strict_types=1);

namespace App\Vendoring\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\VendorApiKeyRepository')]
#[ORM\Table(
    name: 'vendor_api_key',
    indexes: [
        new ORM\Index(name: 'idx_vendor_api_key_status', columns: ['status']),
        new ORM\Index(name: 'idx_vendor_api_key_vendor_status', columns: ['vendor_id', 'status']),
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_vendor_api_key_token_hash', columns: ['token_hash']),
    ],
)]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorApiKey
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    #[ORM\Column(name: 'last_used_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $lastUsedAt = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Vendor::class)]
        #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        private readonly Vendor $vendor,
        #[ORM\Column(name: 'token_hash', type: 'string', length: 64)]
        private readonly string $tokenHash,
        #[ORM\Column(type: 'string', length: 255)]
        private readonly string $permissions,
    ) {
        $this->status = 'active';
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): Vendor
    {
        return $this->vendor;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getPermissions(): string
    {
        return $this->permissions;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLastUsedAt(): ?DateTimeImmutable
    {
        return $this->lastUsedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function deactivate(): void
    {
        $this->status = 'inactive';
    }

    public function hasPermission(string $permission): bool
    {
        $tokens = array_filter(array_map('trim', explode(',', $this->permissions)), static fn(string $value): bool => '' !== $value);

        return in_array($permission, $tokens, true);
    }

    public function touch(): void
    {
        $this->lastUsedAt = new DateTimeImmutable();
    }
}
