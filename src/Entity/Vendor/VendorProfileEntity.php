<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Vendoring\\Repository\\Vendor\\VendorProfileRepository')]
#[ORM\Table(name: 'vendor_profile')]
#[ORM\UniqueConstraint(name: 'uniq_vendor_profile_vendor', columns: ['vendor_id'])]
/**
 * @noinspection PhpPropertyNamingConventionInspection
 * @noinspection PhpTooManyParametersInspection
 */
final class VendorProfileEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    // @phpstan-ignore-next-line
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;

    #[ORM\Column(name: 'display_name', type: 'string', length: 255, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $about = null;

    #[ORM\Column(type: 'string', length: 512, nullable: true)]
    private ?string $website = null;

    /** @var array<string, string>|null */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $socials = null;

    #[ORM\Column(name: 'seo_title', type: 'string', length: 255, nullable: true)]
    private ?string $seoTitle = null;

    #[ORM\Column(name: 'seo_description', type: 'text', nullable: true)]
    private ?string $seoDescription = null;

    #[ORM\Column(name: 'public_profile_status', type: 'string', length: 32)]
    private string $publicProfileStatus = 'draft';

    #[ORM\Column(name: 'public_profile_published_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $publicProfilePublishedAt = null;

    public function __construct(VendorEntity $vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * @noinspection PhpTooManyParametersInspection
     * @param array<string, string>|null $socials
     */
    public function updateProfile(
        ?string $displayName = null,
        ?string $about = null,
        ?string $website = null,
        ?array $socials = null,
        ?string $seoTitle = null,
        ?string $seoDescription = null,
    ): void {
        $this->displayName = $displayName;
        $this->about = $about;
        $this->website = $website;
        $this->socials = $socials;
        $this->seoTitle = $seoTitle;
        $this->seoDescription = $seoDescription;
    }

    public function updateContent(?string $displayName = null, ?string $about = null, ?string $website = null): void
    {
        $this->displayName = $displayName;
        $this->about = $about;
        $this->website = $website;
    }

    /** @param array<string, string>|null $socials */
    public function replaceSocials(?array $socials): void
    {
        $this->socials = $socials;
    }

    public function updateSeo(?string $seoTitle = null, ?string $seoDescription = null): void
    {
        $this->seoTitle = $seoTitle;
        $this->seoDescription = $seoDescription;
    }

    public function publish(): void
    {
        $this->publicProfileStatus = 'published';
        $this->publicProfilePublishedAt = new DateTimeImmutable();
    }

    public function unpublish(): void
    {
        $this->publicProfileStatus = 'draft';
        $this->publicProfilePublishedAt = null;
    }

    public function isPublishable(): bool
    {
        return null !== $this->displayName && '' !== trim($this->displayName);
    }

    public function getId(): ?int
    {
        return is_int($this->id) ? $this->id : null;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    /** @return array<string,string>|null */
    public function getSocials(): ?array
    {
        return $this->socials;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function getPublicProfileStatus(): string
    {
        return $this->publicProfileStatus;
    }

    public function getPublicProfilePublishedAt(): ?DateTimeImmutable
    {
        return $this->publicProfilePublishedAt;
    }
}
