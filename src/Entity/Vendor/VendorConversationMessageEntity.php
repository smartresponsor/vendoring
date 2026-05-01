<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_conversation_message')]
#[ORM\Index(name: 'idx_vendor_conversation_message_conversation_sent_at', columns: ['vendor_conversation_id', 'sent_at'])]
final class VendorConversationMessageEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VendorConversationEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_conversation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorConversationEntity $conversation;

    #[ORM\ManyToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'sender_vendor_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?VendorEntity $senderVendor;

    #[ORM\Column(type: 'string', length: 16)]
    private string $direction;

    #[ORM\Column(type: 'text')]
    private string $body;

    #[ORM\Column(name: 'external_message_id', type: 'string', length: 128, nullable: true)]
    private ?string $externalMessageId;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $meta = [];

    #[ORM\Column(name: 'sent_at', type: 'datetime_immutable')]
    private DateTimeImmutable $sentAt;

    #[ORM\Column(name: 'read_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $readAt = null;

    /** @param array<string, mixed> $meta */
    public function __construct(VendorConversationEntity $conversation, string $direction, string $body, ?VendorEntity $senderVendor = null, ?string $externalMessageId = null, array $meta = [])
    {
        $this->conversation = $conversation;
        $this->direction = $direction;
        $this->body = $body;
        $this->senderVendor = $senderVendor;
        $this->externalMessageId = $externalMessageId;
        $this->meta = $meta;
        $this->sentAt = new DateTimeImmutable();
    }

    public function markRead(): void
    {
        $this->readAt = new DateTimeImmutable();
    }
}
