<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorConversationMessageRepository::class)]
#[ORM\Table(name: 'vendor_conversation_message')]
class VendorConversationMessageEntity extends VendorAbstractEntity
{
    #[ORM\ManyToOne(targetEntity: VendorConversationEntity::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private VendorConversationEntity $conversation;
    #[ORM\ManyToOne(targetEntity: VendorEntity::class, inversedBy: 'sentConversationMessages')] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?VendorEntity $senderVendor = null;
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $direction = '';
    #[ORM\Column(type: 'string', length: 255, nullable: false)] private string $body = '';
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $externalMessageId = null;
    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')] private array $meta = [];
    #[ORM\Column(type: 'datetime_immutable', nullable: false)] private ?\DateTimeImmutable $sentAt = null;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $readAt = null;
    /** @param array<string, mixed> $meta */
    public function __construct(VendorConversationEntity $conversation, string $direction, string $body, ?VendorEntity $senderVendor = null, ?string $externalMessageId = null, array $meta = [])
    {
        parent::__construct();
        $this->conversation = $conversation;
        $this->direction = $direction;
        $this->body = $body;
        $this->senderVendor = $senderVendor;
        $this->externalMessageId = $externalMessageId;
        $this->meta = $meta;
        $this->sentAt = new \DateTimeImmutable();
    }

    public function markRead(?\DateTimeImmutable $readAt = null): self
    {
        $this->readAt = $readAt ?? new \DateTimeImmutable();

        return $this;
    }

    public function getVendor(): ?VendorEntity
    {
        return $this->senderVendor;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getExternalMessageId(): ?string
    {
        return $this->externalMessageId;
    }

    /** @return array<string, mixed> */
    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }
}
