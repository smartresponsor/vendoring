<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\VendorProfile;
use App\Projection\VendorProfileView;
use App\RepositoryInterface\VendorProfileRepositoryInterface;
use App\RepositoryInterface\VendorRepositoryInterface;
use App\ServiceInterface\VendorProfileViewBuilderInterface;

final class VendorProfileViewBuilder implements VendorProfileViewBuilderInterface
{
    /**
     * @var array<string, string>
     */
    private const array SECTION_LABELS = [
        'business' => 'Business profile',
        'public' => 'Public profile',
        'search' => 'Search metadata',
    ];

    /**
     * @var array<string, list<string>>
     */
    private const array SECTION_FIELDS = [
        'business' => ['brandName', 'ownerUserId'],
        'public' => ['displayName', 'about', 'website', 'socials'],
        'search' => ['seoTitle', 'seoDescription'],
    ];

    /**
     * @var array<string, string>
     */
    private const array NEXT_ACTIONS = [
        'ownerUserId' => 'assign_owner',
        'displayName' => 'add_display_name',
        'about' => 'add_about',
        'website' => 'add_website',
        'socials' => 'add_social_link',
        'seoTitle' => 'add_seo_title',
        'seoDescription' => 'add_seo_description',
    ];

    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly VendorProfileRepositoryInterface $profileRepository,
    ) {}

    public function buildForVendorId(int $vendorId): ?VendorProfileView
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (null === $vendor) {
            return null;
        }

        $profile = $this->profileRepository->findOneBy(['vendor' => $vendor]);
        $profileData = $this->buildProfileData($profile, $vendor->getBrandName(), $vendor->getOwnerUserId(), $vendor->getStatus());
        $sections = $this->buildSections($profileData);
        $totalFields = 0;
        $completedFields = 0;
        $missingFields = [];

        foreach (self::SECTION_FIELDS as $fields) {
            $totalFields += count($fields);

            foreach ($fields as $field) {
                if ($this->isProfileFieldComplete($profileData[$field] ?? null)) {
                    ++$completedFields;

                    continue;
                }

                $missingFields[] = $field;
            }
        }

        $completionPercent = (int) round(($completedFields / max($totalFields, 1)) * 100);
        $readyForPublishing = [] === $missingFields;
        $nextAction = [] === $missingFields ? null : (self::NEXT_ACTIONS[$missingFields[0]] ?? null);

        return new VendorProfileView(
            vendorId: $vendorId,
            brandName: $vendor->getBrandName(),
            vendorStatus: $vendor->getStatus(),
            profile: $profileData,
            businessProfile: $this->buildBusinessProfile($profileData),
            publicProfile: $this->buildPublicProfile($profileData),
            searchProfile: $this->buildSearchProfile($profileData),
            publication: $this->buildPublication($profileData, $readyForPublishing),
            sections: $sections,
            completionPercent: $completionPercent,
            readyForPublishing: $readyForPublishing,
            nextAction: $nextAction,
        );
    }

    /**
     * @return array{
     *   'brandName': string,
     *   'vendorStatus': string,
     *   'ownerUserId': ?int,
     *   'publicProfileStatus': string,
     *   'publicProfilePublishedAt': ?string,
     *   'displayName': ?string,
     *   'publicDisplayName': string,
     *   'about': ?string,
     *   'website': ?string,
     *   'socials': array<string, string>,
     *   'seoTitle': ?string,
     *   'seoDescription': ?string
     * }
     */
    private function buildProfileData(
        ?VendorProfile $profile,
        string $brandName,
        ?int $ownerUserId = null,
        string $vendorStatus = 'inactive',
    ): array {
        $displayName = $profile?->getDisplayName();

        return [
            'brandName' => $brandName,
            'vendorStatus' => $vendorStatus,
            'ownerUserId' => $ownerUserId,
            'publicProfileStatus' => $profile?->getPublicProfileStatus() ?? 'draft',
            'publicProfilePublishedAt' => $profile?->getPublicProfilePublishedAt()?->format(DATE_ATOM),
            'displayName' => $displayName,
            'publicDisplayName' => $displayName ?? $brandName,
            'about' => $profile?->getAbout(),
            'website' => $profile?->getWebsite(),
            'socials' => $profile?->getSocials() ?? [],
            'seoTitle' => $profile?->getSeoTitle(),
            'seoDescription' => $profile?->getSeoDescription(),
        ];
    }

    /**
     * @param array{
     *   'brandName': string,
     *   'vendorStatus': string,
     *   'ownerUserId': ?int,
     *   'publicProfileStatus': string,
     *   'publicProfilePublishedAt': ?string,
     *   'displayName': ?string,
     *   'publicDisplayName': string,
     *   'about': ?string,
     *   'website': ?string,
     *   'socials': array<string, string>,
     *   'seoTitle': ?string,
     *   'seoDescription': ?string
     * } $profileData
     *
     * @return array<string, array{'label': string, 'complete': bool, 'missing': list<string>}>
     */
    private function buildSections(array $profileData): array
    {
        $sections = [];

        foreach (self::SECTION_FIELDS as $key => $fields) {
            $missing = [];

            foreach ($fields as $field) {
                if (!$this->isProfileFieldComplete($profileData[$field] ?? null)) {
                    $missing[] = $field;
                }
            }

            $sections[$key] = [
                'label' => self::SECTION_LABELS[$key],
                'complete' => [] === $missing,
                'missing' => $missing,
            ];
        }

        return $sections;
    }

    /**
     * @param array{
     *   'brandName': string,
     *   'vendorStatus': string,
     *   'ownerUserId': ?int,
     *   'publicProfileStatus': string,
     *   'publicProfilePublishedAt': ?string,
     *   'displayName': ?string,
     *   'publicDisplayName': string,
     *   'about': ?string,
     *   'website': ?string,
     *   'socials': array<string, string>,
     *   'seoTitle': ?string,
     *   'seoDescription': ?string
     * } $profileData
     *
     * @return array{'brandName': string, 'vendorStatus': string, 'ownerUserId': ?int}
     */
    private function buildBusinessProfile(array $profileData): array
    {
        return [
            'brandName' => $profileData['brandName'],
            'vendorStatus' => $profileData['vendorStatus'],
            'ownerUserId' => $profileData['ownerUserId'],
        ];
    }

    /**
     * @param array{
     *   'brandName': string,
     *   'vendorStatus': string,
     *   'ownerUserId': ?int,
     *   'publicProfileStatus': string,
     *   'publicProfilePublishedAt': ?string,
     *   'displayName': ?string,
     *   'publicDisplayName': string,
     *   'about': ?string,
     *   'website': ?string,
     *   'socials': array<string, string>,
     *   'seoTitle': ?string,
     *   'seoDescription': ?string
     * } $profileData
     *
     * @return array{
     *   'displayName': ?string,
     *   'publicDisplayName': string,
     *   'about': ?string,
     *   'website': ?string,
     *   'socials': array<string, string>,
     *   'status': string,
     *   'publishedAt': ?string
     * }
     */
    private function buildPublicProfile(array $profileData): array
    {
        return [
            'displayName' => $profileData['displayName'],
            'publicDisplayName' => $profileData['publicDisplayName'],
            'about' => $profileData['about'],
            'website' => $profileData['website'],
            'socials' => $profileData['socials'],
            'status' => $profileData['publicProfileStatus'],
            'publishedAt' => $profileData['publicProfilePublishedAt'],
        ];
    }

    /**
     * @param array{
     *   'brandName': string,
     *   'vendorStatus': string,
     *   'ownerUserId': ?int,
     *   'publicProfileStatus': string,
     *   'publicProfilePublishedAt': ?string,
     *   'displayName': ?string,
     *   'publicDisplayName': string,
     *   'about': ?string,
     *   'website': ?string,
     *   'socials': array<string, string>,
     *   'seoTitle': ?string,
     *   'seoDescription': ?string
     * } $profileData
     *
     * @return array{'seoTitle': ?string, 'seoDescription': ?string}
     */
    private function buildSearchProfile(array $profileData): array
    {
        return [
            'seoTitle' => $profileData['seoTitle'],
            'seoDescription' => $profileData['seoDescription'],
        ];
    }

    /**
     * @param array{
     *   'brandName': string,
     *   'vendorStatus': string,
     *   'ownerUserId': ?int,
     *   'publicProfileStatus': string,
     *   'publicProfilePublishedAt': ?string,
     *   'displayName': ?string,
     *   'publicDisplayName': string,
     *   'about': ?string,
     *   'website': ?string,
     *   'socials': array<string, string>,
     *   'seoTitle': ?string,
     *   'seoDescription': ?string
     * } $profileData
     *
     * @return array{'status': string, 'publishedAt': ?string, 'canPublish': bool}
     */
    private function buildPublication(array $profileData, bool $readyForPublishing): array
    {
        return [
            'status' => $profileData['publicProfileStatus'],
            'publishedAt' => $profileData['publicProfilePublishedAt'],
            'canPublish' => $readyForPublishing,
        ];
    }

    private function isProfileFieldComplete(mixed $value): bool
    {
        if (is_array($value)) {
            return [] !== $value;
        }

        if (!is_string($value)) {
            return is_int($value) || is_float($value);
        }

        return '' !== trim($value);
    }
}
