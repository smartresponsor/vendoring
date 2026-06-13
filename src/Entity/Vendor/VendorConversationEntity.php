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
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $status = '';
    #[ORM\Column(type: 'json')] private array $meta = [];
    #[ORM\Column(type: 'datetime_immutable', nullable: false)] private ?\DateTimeImmutable $openedAt = null;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $closedAt = null;
    public function __construct(VendorEntity $vendor, array $meta = [])
    {
        parent::__construct('open');
        $this->vendor = $vendor;
        $this->meta = $meta;
        $this->openedAt = new \DateTimeImmutable();
    }

    public function update(?string $subject, string $channel, ?string $counterpartyType, ?string $counterpartyId, ?string $counterpartyName, string $status, array $meta): self
    {
        $this->subject = $subject;
        $this->channel = $channel;
        $this->counterpartyType = $counterpartyType;
        $this->counterpartyId = $counterpartyId;
        $this->counterpartyName = $counterpartyName;
        $this->status = $status;
        $this->meta = $meta;

        return $this->setStatus($status);
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->vendor ?? null;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getCounterpartyType()
    {
        return $this->counterpartyType;
    }

    public function getCounterpartyId()
    {
        return $this->counterpartyId;
    }

    public function getCounterpartyName()
    {
        return $this->counterpartyName;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function getOpenedAt()
    {
        return $this->openedAt;
    }

    public function getClosedAt()
    {
        return $this->closedAt;
    }
}
