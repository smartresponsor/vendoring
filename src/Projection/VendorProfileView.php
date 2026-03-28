<?php

declare(strict_types=1);

namespace App\Projection;

/**
 * Vendor profile readiness view for self-serve profile completion and publishability.
 */
final class VendorProfileView
{
    /**
     * @param array<string, mixed>                                                    $profile
     * @param array<string, mixed>                                                    $businessProfile
     * @param array<string, mixed>                                                    $publicProfile
     * @param array<string, mixed>                                                    $searchProfile
     * @param array<string, mixed>                                                    $publication
     * @param array<string, array{label:string, complete:bool, missing:list<string>}> $sections
     */
    public function __construct(
        private readonly int $vendorId,
        private readonly string $brandName,
        private readonly string $vendorStatus,
        private readonly array $profile,
        private readonly array $businessProfile,
        private readonly array $publicProfile,
        private readonly array $searchProfile,
        private readonly array $publication,
        private readonly array $sections,
        private readonly int $completionPercent,
        private readonly bool $readyForPublishing,
        private readonly ?string $nextAction,
    ) {
    }

    /**
     * @return array{
     *   vendorId:int,
     *   brandName:string,
     *   vendorStatus:string,
     *   profile:array<string,mixed>,
     *   businessProfile:array<string,mixed>,
     *   publicProfile:array<string,mixed>,
     *   searchProfile:array<string,mixed>,
     *   publication:array<string,mixed>,
     *   sections:array<string, array{label:string, complete:bool, missing:list<string>}>,
     *   completionPercent:int,
     *   readyForPublishing:bool,
     *   nextAction:?string
     * }
     */
    public function toArray(): array
    {
        return [
            'vendorId' => $this->vendorId,
            'brandName' => $this->brandName,
            'vendorStatus' => $this->vendorStatus,
            'profile' => $this->profile,
            'businessProfile' => $this->businessProfile,
            'publicProfile' => $this->publicProfile,
            'searchProfile' => $this->searchProfile,
            'publication' => $this->publication,
            'sections' => $this->sections,
            'completionPercent' => $this->completionPercent,
            'readyForPublishing' => $this->readyForPublishing,
            'nextAction' => $this->nextAction,
        ];
    }
}
