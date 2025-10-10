<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\Vendor\\VendorRepository')]
#[ORM\Table(name: 'vendor')]
class Vendor
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(length: 128, unique: true)]
    private string $brandName;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $userId = null;

    #[ORM\Column(length: 32)]
    private string $status = 'inactive';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $brandName, ?int $userId = null)
    {
        $this->brandName = $brandName;
        $this->userId = $userId;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getBrandName(): string { return $this->brandName; }
    public function getUserId(): ?int { return $this->userId; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function rename(string $brandName): void { $this->brandName = $brandName; }
    public function activate(): void { $this->status = 'active'; }
    public function deactivate(): void { $this->status = 'inactive'; }
}
