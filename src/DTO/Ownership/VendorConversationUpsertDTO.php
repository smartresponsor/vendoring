<?php

declare(strict_types=1);

namespace App\Vendoring\DTO\Ownership;

/**
 * Write request for a vendor-owned conversation plus optional first message.
 */
final readonly class VendorConversationUpsertDTO
{
    /** @param array<string, mixed> $conversationMeta
     *  @param array<string, mixed> $messageMeta
     */
    public function __construct(
        public int $vendorId,
        public ?string $subject = null,
        public ?string $channel = 'internal',
        public ?string $counterpartyType = null,
        public ?string $counterpartyId = null,
        public ?string $counterpartyName = null,
        public ?string $status = 'open',
        public array $conversationMeta = [],
        public ?string $firstMessageBody = null,
        public ?string $firstMessageDirection = null,
        public ?string $externalMessageId = null,
        public array $messageMeta = [],
    ) {}
}
