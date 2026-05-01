<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'vendor_conversation')]
#[ORM\Index(name: 'idx_vendor_conversation_vendor_status', columns: ['vendor_id', 'status'])]
final class VendorConversationEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $channel = 'internal';

    #[ORM\Column(name: 'counterparty_type', type: 'string', length: 64, nullable: true)]
    private ?string $counterpartyType = null;

    #[ORM\Column(name: 'counterparty_id', type: 'string', length: 128, nullable: true)]
    private ?string $counterpartyId = null;

    #[ORM\Column(name: 'counterparty_name', type: 'string', length: 255, nullable: true)]
    private ?string $counterpartyName = null;

    #[ORM\Column(type: 'string', length: 32)]
    private string $status = 'open';

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $meta = [];

    #[ORM\Column(name: 'opened_at', type: 'datetime_immutable')]
    private DateTimeImmutable $openedAt;

    #[ORM\Column(name: 'closed_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $closedAt = null;

    /** @param array<string, mixed> $meta */
    public function __construct(VendorEntity $vendor, array $meta = [])
    {
        $this->vendor = $vendor;
        $this->meta = $meta;
        $this->openedAt = new DateTimeImmutable();
    }

    /** @param array<string, mixed> $meta */
    public function update(?string $subject = null, ?string $channel = null, ?string $counterpartyType = null, ?string $counterpartyId = null, ?string $counterpartyName = null, ?string $status = null, array $meta = []): void
    {
        $this->subject = $subject;
        $this->channel = null === $channel ? $this->channel : $channel;
        $this->counterpartyType = $counterpartyType;
        $this->counterpartyId = $counterpartyId;
        $this->counterpartyName = $counterpartyName;
        $this->status = null === $status ? $this->status : $status;
        $this->meta = [] === $meta ? $this->meta : $meta;
        $this->closedAt = 'closed' === $this->status ? new DateTimeImmutable() : null;
    }
}
