<?php

declare(strict_types=1);

namespace App\Entity;

use App\EntityInterface\VendorEntityInterface;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\VendorRepository')]
#[ORM\Table(name: 'vendor')]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class Vendor implements VendorEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @var int|null */
    private ?int $id = null;

    #[ORM\Column(name: 'brand_name', type: 'string', length: 255)]
    private string $brandName;

    #[ORM\Column(name: 'owner_user_id', type: 'integer', nullable: true)]
    private ?int $ownerUserId;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(string $brandName, ?int $ownerUserId = null)
    {
        $this->brandName = $brandName;
        $this->ownerUserId = $ownerUserId;
        $this->status = 'inactive';
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getBrandName(): string
    {
        return $this->brandName;
    }

    public function getOwnerUserId(): ?int
    {
        return $this->ownerUserId;
    }

    public function getUserId(): ?int
    {
        return $this->ownerUserId;
    }

    public function hasOwnerUserId(): bool
    {
        return null !== $this->ownerUserId;
    }

    public function changeOwnerUserId(?int $ownerUserId): void
    {
        $this->ownerUserId = $ownerUserId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function rename(string $brandName): void
    {
        $this->brandName = $brandName;
    }

    public function activate(): void
    {
        $this->status = 'active';
    }

    public function deactivate(): void
    {
        $this->status = 'inactive';
    }
}
