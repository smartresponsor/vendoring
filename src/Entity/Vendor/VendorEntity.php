<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use App\Objecting\EntityInterface\ObjectEntityInterface;
use App\Objecting\EntityTrait\Embeddable\ObjectTitleEmbeddableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\VendorRepository::class)]
#[ORM\Table(
    name: 'vendor',
    indexes: [
        new ORM\Index(name: 'idx_vendor_owner_user_id', columns: ['owner_user_id']),
    ],
)]
class VendorEntity extends VendorAbstractEntity implements ObjectEntityInterface
{
    use ObjectTitleEmbeddableTrait;
    #[ORM\Column(type: 'string', length: 255)]
    private string $brandName;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ownerUserId = null;

    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorProfileEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorProfileEntity $profile = null;
    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorMediaEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorMediaEntity $media = null;
    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorProfileAvatarEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorProfileAvatarEntity $profileAvatar = null;
    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorProfileCoverEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorProfileCoverEntity $profileCover = null;
    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorBillingEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorBillingEntity $billing = null;
    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorSecurityEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorSecurityEntity $security = null;
    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorPassportEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorPassportEntity $passport = null;
    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorAddressEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorAddressEntity $address = null;
    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorIbanEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorIbanEntity $iban = null;

    /** @var Collection<int, VendorDocumentEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorDocumentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $documents;
    /** @var Collection<int, VendorAttachmentEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorAttachmentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $attachments;
    /** @var Collection<int, VendorUserAssignmentEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorUserAssignmentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $userAssignments;
    /** @var Collection<int, VendorPaymentEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorPaymentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $payments;
    /** @var Collection<int, VendorCommissionEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCommissionEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $commissions;
    /** @var Collection<int, VendorCommissionHistoryEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCommissionHistoryEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $commissionHistory;
    /** @var Collection<int, VendorConversationEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorConversationEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $conversations;
    /** @var Collection<int, VendorConversationMessageEntity> */
    #[ORM\OneToMany(mappedBy: 'senderVendor', targetEntity: VendorConversationMessageEntity::class)]
    private Collection $sentConversationMessages;
    /** @var Collection<int, VendorShipmentEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorShipmentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $shipments;
    /** @var Collection<int, VendorGroupEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorGroupEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $groups;
    /** @var Collection<int, VendorCategoryEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCategoryEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $categories;
    /** @var Collection<int, VendorFavouriteEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorFavouriteEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $favourites;
    /** @var Collection<int, VendorWishlistEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorWishlistEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $wishlists;
    /** @var Collection<int, VendorCodeStorageEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCodeStorageEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $codeStorage;
    /** @var Collection<int, VendorRememberMeTokenEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorRememberMeTokenEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $rememberMeTokens;
    /** @var Collection<int, VendorCustomerOrderEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCustomerOrderEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $customerOrders;
    /** @var Collection<int, VendorLogEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorLogEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $logs;
    /** @var Collection<int, VendorChannelEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorChannelEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $channels;
    /** @var Collection<int, VendorTranslationEntity> */
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorTranslationEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    public function __construct(string $brandName, ?int $ownerUserId = null)
    {
        parent::__construct('inactive');
        $this->initializeObjectTitle(firstTitle: trim($brandName));
        $this->brandName = trim($brandName);
        $this->ownerUserId = $ownerUserId;
        foreach (['documents', 'attachments', 'userAssignments', 'payments', 'commissions', 'commissionHistory', 'conversations', 'sentConversationMessages', 'shipments', 'groups', 'categories', 'favourites', 'wishlists', 'codeStorage', 'rememberMeTokens', 'customerOrders', 'logs', 'channels', 'translations'] as $property) {
            $this->{$property} = new ArrayCollection();
        }
    }

    public function getBrandName(): string
    {
        return $this->brandName;
    }

    public function getTitle(): string
    {
        return $this->brandName;
    }

    public function rename(string $brandName): self
    {
        $this->brandName = trim($brandName);
        $this->touchModified();

        return $this;
    }

    public function getOwnerUserId(): ?int
    {
        return $this->ownerUserId;
    }

    public function getProfile(): ?VendorProfileEntity
    {
        return $this->profile;
    }

    public function setProfile(VendorProfileEntity $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getMedia(): ?VendorMediaEntity
    {
        return $this->media;
    }

    public function setMedia(VendorMediaEntity $media): self
    {
        $this->media = $media;

        return $this;
    }

    public function changeOwnerUserId(?int $ownerUserId): self
    {
        $this->ownerUserId = $ownerUserId;
        $this->touchModified();

        return $this;
    }

    public function getProfileAvatar(): ?VendorProfileAvatarEntity
    {
        return $this->profileAvatar;
    }

    public function getProfileCover(): ?VendorProfileCoverEntity
    {
        return $this->profileCover;
    }

    public function getDisplayName(): string
    {
        return $this->profile?->getDisplayName() ?: $this->brandName;
    }

    public function getAbout(): ?string
    {
        return $this->profile?->getAbout();
    }

    public function getWebsite(): ?string
    {
        return $this->profile?->getWebsite();
    }

    /** @return array<string, mixed>|null */
    public function getSocials(): ?array
    {
        return $this->profile?->getSocials();
    }

    public function getAvatarPath(): ?string
    {
        return $this->profileAvatar?->getFilePath() ?? $this->media?->getLogoPath();
    }

    public function getCoverPath(): ?string
    {
        return $this->profileCover?->getFilePath() ?? $this->media?->getBannerPath();
    }

    public function activate(): self
    {
        $this->setStatus('active');

        return $this;
    }

    public function deactivate(): self
    {
        $this->setStatus('inactive');

        return $this;
    }
}
