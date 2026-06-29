<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorProfileRepository::class)]
#[ORM\Table(
    name: 'vendor_profile',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_vendor_profile_vendor_id', columns: ['vendor_id']),
    ],
    indexes: [
        new ORM\Index(name: 'idx_vendor_profile_public_status', columns: ['public_profile_status']),
    ],
)]
class VendorProfileEntity extends VendorAbstractEntity
{
    #[ORM\OneToOne(inversedBy: 'profile', targetEntity: VendorEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private VendorEntity $vendor;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $displayName = null;
    #[ORM\Column(type: 'text', nullable: true)] private ?string $about = null;
    #[ORM\Column(type: 'string', length: 512, nullable: true)] private ?string $website = null;
    #[ORM\Column(type: 'json', nullable: true)] private ?array $socials = null;
    #[ORM\Column(type: 'string', length: 255, nullable: true)] private ?string $seoTitle = null;
    #[ORM\Column(type: 'text', nullable: true)] private ?string $seoDescription = null;
    #[ORM\Column(type: 'string', length: 32)] private string $publicProfileStatus = 'draft';
    #[ORM\Column(type: 'datetime_immutable', nullable: true)] private ?\DateTimeImmutable $publicProfilePublishedAt = null;
    public function __construct(VendorEntity $vendor)
    {
        parent::__construct('draft');
        $this->vendor = $vendor;
    }

    public function getVendor(): VendorEntity
    {
        return $this->vendor;
    }

    public function updateContent(?string $displayName, ?string $about, ?string $website): self
    {
        $this->displayName = $displayName;
        $this->about = $about;
        $this->website = $website;
        $this->touchModified();

        return $this;
    }

    public function replaceSocials(?array $socials): self
    {
        $this->socials = $socials;
        $this->touchModified();

        return $this;
    }

    public function updateProfile(
        ?string $displayName,
        ?string $about,
        ?string $website,
        ?array $socials,
        ?string $seoTitle,
        ?string $seoDescription,
    ): self {
        $this->updateContent($displayName, $about, $website);
        $this->replaceSocials($socials);

        return $this->updateSeo($seoTitle, $seoDescription);
    }

    public function updateSeo(?string $seoTitle, ?string $seoDescription): self
    {
        $this->seoTitle = $seoTitle;
        $this->seoDescription = $seoDescription;
        $this->touchModified();

        return $this;
    }

    public function publish(): self
    {
        $this->publicProfileStatus = 'published';
        $this->publicProfilePublishedAt = new \DateTimeImmutable();

        $this->setStatus('published');

        return $this;
    }

    public function unpublish(): self
    {
        $this->publicProfileStatus = 'draft';
        $this->publicProfilePublishedAt = null;

        $this->setStatus('draft');

        return $this;
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

    public function getPublicProfilePublishedAt(): ?\DateTimeImmutable
    {
        return $this->publicProfilePublishedAt;
    }
}
