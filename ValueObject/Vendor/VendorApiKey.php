<?php
declare(strict_types=1);

namespace App\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\Vendor\\VendorApiKeyRepository')]
#[ORM\Table(name: 'vendor_api_key')]
class VendorApiKey
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Vendor $vendor;

    #[ORM\Column(length: 64, unique: true)]
    private string $token; // store hashed token

    #[ORM\Column(type: 'json')]
    private array $permissions = []; // e.g. ['vendor:read','vendor:write']

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(length: 16)]
    private string $status = 'active'; // active|revoked|expired

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastUsedAt = null;

    public function __construct(Vendor $vendor, string $token, array $permissions = [], ?\DateTimeImmutable $expiresAt = null)
    {
        $this->vendor = $vendor;
        $this->token = $token;
        $this->permissions = $permissions;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function markUsed(): void { $this->lastUsedAt = new \DateTimeImmutable(); }
    public function revoke(): void { $this->status = 'revoked'; }
    public function isActive(): bool
    {
        if ($this->status !== 'active') return false;
        if ($this->expiresAt && $this->expiresAt < new \DateTimeImmutable()) return false;
        return true;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getVendor(): Vendor { return $this->vendor; }
    public function getToken(): string { return $this->token; }
    public function getPermissions(): array { return $this->permissions; }
    public function getStatus(): string { return $this->status; }
    public function getLastUsedAt(): ?\DateTimeImmutable { return $this->lastUsedAt; }
}
