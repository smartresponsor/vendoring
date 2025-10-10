<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor')]
class Vendor
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(length: 128, unique: true)]
    private string $brandName;

    #[ORM\Column(length: 32)]
    private string $status = 'inactive';

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $brandName)
    {
        $this->brandName = $brandName;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int { return $this->id; }
    public function getBrandName(): string { return $this->brandName; }
    public function getStatus(): string { return $this->status; }
    public function activate(): void { $this->status = 'active'; }
}
