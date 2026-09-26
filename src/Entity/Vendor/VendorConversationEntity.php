<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorConversationRepository::class)]
#[ORM\Table(name: 'vendor_conversation')]
class VendorConversationEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'conversations')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $subject = null;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $channel = '';
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $counterpartyType = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $counterpartyId = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $counterpartyName = null;
    #[ORM\Column(name: 'conversation_status', type: 'string', length: 255, nullable: false)] private string $status = '';
    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')] private array $meta = [];
    #[ORM\Column(type: 'datetime_immutable', nullable: false)] private ?\DateTimeImmutable $openedAt = null;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $closedAt = null;
    /** @param array<string, mixed> $meta */
    public function __construct(VendorEntity $vendor, array $meta = [])
    {
        parent::__construct('open');
        $this->vendor = $vendor;
        $this->meta = $meta;
        $this->openedAt = new \DateTimeImmutable();
    }

    /** @param array<string, mixed> $meta */
    public function update(?string $subject, string $channel, ?string $counterpartyType, ?string $counterpartyId, ?string $counterpartyName, string $status, array $meta): self
    {
        $this->subject = $subject;
        $this->channel = $channel;
        $this->counterpartyType = $counterpartyType;
        $this->counterpartyId = $counterpartyId;
        $this->counterpartyName = $counterpartyName;
        $this->status = $status;
        $this->meta = $meta;

        $this->setStatus($status);

        return $this;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getCounterpartyType(): ?string
    {
        return $this->counterpartyType;
    }

    public function getCounterpartyId(): ?string
    {
        return $this->counterpartyId;
    }

    public function getCounterpartyName(): ?string
    {
        return $this->counterpartyName;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /** @return array<string, mixed> */
    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getOpenedAt(): ?\DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }
}
