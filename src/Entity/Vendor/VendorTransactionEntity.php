<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use App\Vendoring\EntityInterface\Vendor\VendorTransactionEntityInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorTransactionRepository::class)]
#[ORM\Table(
    name: 'vendor_transaction',
    indexes: [
        new ORM\Index(name: 'idx_vendor_transaction_vendor_id', columns: ['vendor_id']),
    ],
)]
class VendorTransactionEntity extends VendorAbstractEntity implements VendorTransactionEntityInterface
{
    #[ORM\Column(type: 'string', length: 64, name: 'vendor_id')] private string $vendorId;
    #[ORM\Column(type: 'string', length: 64, name: 'order_id')] private string $orderId;
    #[ORM\Column(type: 'string', length: 64, nullable: true, name: 'project_id')] private ?string $projectId = null;
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)] private string $amount;
    #[ORM\Column(type: 'string', length: 64, name: 'status')] private string $status = 'pending';
    #[ORM\Column(type: 'datetime_immutable', name: 'created_at')] private \DateTimeImmutable $createdAt;
    public function __construct(string $vendorId, string $orderId, ?string $projectId, string $amount, string $status = 'pending')
    {
        parent::__construct($status);
        $this->vendorId = $vendorId;
        $this->orderId = $orderId;
        $this->projectId = $projectId;
        $this->amount = $amount;
        $this->status = $status;
        $this->createdAt = new \DateTimeImmutable();
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
        return $this->amount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        parent::setStatus($status);

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
