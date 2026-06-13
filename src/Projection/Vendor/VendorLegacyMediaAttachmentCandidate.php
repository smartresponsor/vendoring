<?php

declare(strict_types=1);

namespace App\Vendoring\Projection\Vendor;

/**
 * Non-destructive migration candidate emitted from legacy Vendoring media path fields.
 */
final readonly class VendorLegacyMediaAttachmentCandidate
{
    public function __construct(
        public int $vendorId,
        public string $ownerType,
        public string $ownerId,
        public string $context,
        public string $slot,
        public string $legacyPath,
        public bool $primary,
        public ?int $position = null,
    ) {
    }

    /**
     * @return array{vendorId:int, ownerType:string, ownerId:string, context:string, slot:string, legacyPath:string, primary:bool, position:?int}
     */
    public function toArray(): array
    {
        return [
            'vendorId' => $this->vendorId,
            'ownerType' => $this->ownerType,
            'ownerId' => $this->ownerId,
            'context' => $this->context,
            'slot' => $this->slot,
            'legacyPath' => $this->legacyPath,
            'primary' => $this->primary,
            'position' => $this->position,
        ];
    }
}
