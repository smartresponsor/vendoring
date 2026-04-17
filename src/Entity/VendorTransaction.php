<?php

declare(strict_types=1);

namespace App\Entity;

use App\EntityInterface\VendorTransactionInterface;
use App\ValueObject\VendorTransactionStatus;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\\Repository\\VendorTransactionRepository')]
#[ORM\Table(
    name: 'vendor_transaction',
    indexes: [
        new ORM\Index(name: 'idx_vendor_transaction_vendor_created', columns: ['vendor_id', 'created_at', 'id']),
    ],
)]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 */
final class VendorTransaction implements VendorTransactionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @var int|null */
    private ?int $id = null;

    #[ORM\Column(name: 'status', type: 'string', length: 64)]
    private string $status = VendorTransactionStatus::PENDING;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\Column(name: 'vendor_id', type: 'string', length: 64)]
        private readonly string $vendorId,
        #[ORM\Column(name: 'order_id', type: 'string', length: 64)]
        private readonly string $orderId,
        #[ORM\Column(name: 'project_id', type: 'string', length: 64, nullable: true)]
        private readonly ?string $projectId,
        #[ORM\Column(type: 'decimal', precision: 12, scale: 2)]
        private readonly string $amount,
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendorId(): string
    {
        return $this->vendorId;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getProjectId(): ?string
    {
        return $this->projectId;
    }

    public function getAmount(): string
    {
        return number_format((float) $this->amount, 2, '.', '');
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
