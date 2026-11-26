<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use App\Vendoring\EntityInterface\VendorTransactionEntityInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorTransactionRepository::class)]
#[ORM\Table(name: 'vendor_transaction')]
#[ORM\Index(name: 'idx_vendor_transaction_vendor_created', columns: ['vendor_id', 'transaction_created_at'])]
#[ORM\Index(name: 'idx_vendor_transaction_owner', columns: ['owner_vendor_id'])]
class VendorTransactionEntity extends VendorAbstractEntity implements VendorTransactionEntityInterface
{
    #[ORM\Column(type: 'string', length: 64, name: 'vendor_id')] private string $vendorId;
    #[ORM\Column(type: 'string', length: 64, name: 'order_id')] private string $orderId;
    #[ORM\Column(type: 'string', length: 64, nullable: true, name: 'project_id')] private ?string $projectId = null;
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2)] private string $amount;
    #[ORM\Column(type: 'string', length: 64, name: 'transaction_status')] private string $status = 'pending';
    #[ORM\Column(type: 'datetime_immutable', name: 'transaction_created_at')] private \DateTimeImmutable $createdAt;
    #[ORM\ManyToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'owner_vendor_id', nullable: true, onDelete: 'RESTRICT')]
    private ?VendorEntity $ownerVendor = null;

    public function __construct(string $vendorId, string $orderId, ?string $projectId, string $amount, string $status = 'pending', ?VendorEntity $ownerVendor = null)
    {
        if (null !== $ownerVendor && (null === $ownerVendor->getId() || (string) $ownerVendor->getId() !== $vendorId)) {
            throw new \InvalidArgumentException('Transaction owner does not match canonical vendor');
        }
        parent::__construct($status);
        $this->ownerVendor = $ownerVendor;
        $this->vendorId = $vendorId;
        $this->orderId = $orderId;
        $this->projectId = $projectId;
        $this->amount = $amount;
        $this->status = $status;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getOwnerVendor(): ?VendorEntity
    {
        return $this->ownerVendor;
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
