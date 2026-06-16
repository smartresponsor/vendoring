<?php

declare(strict_types=1);

namespace App\Vendoring\Entity\Vendor;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: \App\Vendoring\Repository\Vendor\VendorRepository::class)]
#[ORM\Table(name: 'vendor')]
class VendorEntity extends VendorAbstractEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $brandName;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $ownerUserId = null;

    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorProfileEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorProfileEntity $profile = null;
    #[ORM\OneToOne(mappedBy: 'vendor', targetEntity: VendorMediaEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?VendorMediaEntity $media = null;
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

    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorDocumentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $documents;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorAttachmentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $attachments;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorUserAssignmentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $userAssignments;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorPaymentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $payments;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCommissionEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $commissions;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCommissionHistoryEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $commissionHistory;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorConversationEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $conversations;
    #[ORM\OneToMany(mappedBy: 'senderVendor', targetEntity: VendorConversationMessageEntity::class)]
    private Collection $sentConversationMessages;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorShipmentEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $shipments;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorGroupEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $groups;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCategoryEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $categories;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorFavouriteEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $favourites;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorWishlistEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $wishlists;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCodeStorageEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $codeStorage;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorRememberMeTokenEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $rememberMeTokens;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorCustomerOrderEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $customerOrders;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorLogEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $logs;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorChannelEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $channels;
    #[ORM\OneToMany(mappedBy: 'vendor', targetEntity: VendorTranslationEntity::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    public function __construct(string $brandName, ?int $ownerUserId = null)
    {
        parent::__construct('inactive');
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

    public function changeOwnerUserId(?int $ownerUserId): self
    {
        $this->ownerUserId = $ownerUserId;
        $this->touchModified();

        return $this;
    }

    public function activate(): self
    {
        return $this->setStatus('active');
    }

    public function deactivate(): self
    {
        return $this->setStatus('inactive');
    }
}
