<?php

declare(strict_types=1);

namespace App\Vendoring\Projection\Vendor;

/**
 * Vendor profile readiness projection for self-serve profile completion and publishability.
 */
final readonly class VendorProfileProjection
{
    /**
     * @param array<string, mixed>                                                    $profile
     * @param array<string, mixed>                                                    $businessProfile
     * @param array<string, mixed>                                                    $publicProfile
     * @param array<string, mixed>                                                    $searchProfile
     * @param array<string, mixed>                                                    $publication
     * @param array<string, array{'label': string, 'complete': bool, 'missing': list<string>}> $sections
     */
    public function __construct(
        private int     $vendorId,
        private string  $brandName,
        private string  $vendorStatus,
        private array   $profile,
        private array   $businessProfile,
        private array   $publicProfile,
        private array   $searchProfile,
        private array   $publication,
        private array   $sections,
        private int     $completionPercent,
        private bool    $readyForPublishing,
        private ?string $nextAction,
    ) {}

    /**
     * @return array{
     *   'vendorId': int,
     *   'brandName': string,
     *   'vendorStatus': string,
     *   'profile': array<string, mixed>,
     *   'businessProfile': array<string, mixed>,
     *   'publicProfile': array<string, mixed>,
     *   'searchProfile': array<string, mixed>,
     *   'publication': array<string, mixed>,
     *   'sections': array<string, array{'label': string, 'complete': bool, 'missing': list<string>}>,
     *   'completionPercent': int,
     *   'readyForPublishing': bool,
     *   'nextAction': ?string
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
