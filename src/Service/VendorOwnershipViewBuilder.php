<?php

declare(strict_types=1);

namespace App\Vendoring\Service;

use App\Vendoring\Entity\VendorCategory;
use App\Vendoring\Entity\VendorCodeStorage;
use App\Vendoring\Entity\VendorCommission;
use App\Vendoring\Entity\VendorCommissionHistory;
use App\Vendoring\Entity\VendorConversation;
use App\Vendoring\Entity\VendorConversationMessage;
use App\Vendoring\Entity\VendorCustomerOrder;
use App\Vendoring\Entity\VendorFavourite;
use App\Vendoring\Entity\VendorGroup;
use App\Vendoring\Entity\VendorLog;
use App\Vendoring\Entity\VendorPayment;
use App\Vendoring\Entity\VendorRememberMeToken;
use App\Vendoring\Entity\VendorShipment;
use App\Vendoring\Entity\VendorWishlist;
use App\Vendoring\Entity\VendorWishlistItem;
use App\Vendoring\Projection\VendorOwnershipView;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\ServiceInterface\Security\VendorAuthorizationMatrixInterface;
use App\Vendoring\ServiceInterface\VendorOwnershipViewBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Builds a vendor-local ownership/access summary without pulling any external User aggregate.
 */
final readonly class VendorOwnershipViewBuilder implements VendorOwnershipViewBuilderInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository,
        private VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private VendorAuthorizationMatrixInterface $authorizationMatrix,
        private EntityManagerInterface $entityManager,
    ) {}

    public function buildForVendorId(int $vendorId): ?VendorOwnershipView
    {
        $vendor = $this->vendorRepository->find($vendorId);

        if (null === $vendor) {
            return null;
        }

        $assignments = [];
        foreach ($this->assignmentRepository->findActiveByVendorId($vendorId) as $assignment) {
            $assignments[] = [
                'userId' => $assignment->getUserId(),
                'role' => $assignment->getRole(),
                'status' => $assignment->getStatus(),
                'capabilities' => $this->authorizationMatrix->capabilitiesForRole($assignment->getRole()),
                'isPrimary' => $assignment->isPrimary(),
                'grantedAt' => $assignment->getGrantedAt()->format(DATE_ATOM),
                'revokedAt' => $assignment->getRevokedAt()?->format(DATE_ATOM),
            ];
        }

        return new VendorOwnershipView(
            vendorId: $vendorId,
            ownerUserId: $vendor->getOwnerUserId(),
            assignments: $assignments,
            relationCounts: $this->buildRelationCounts($vendor),
        );
    }

    /** @return array<string, int> */
    private function buildRelationCounts(object $vendor): array
    {
        $criteria = ['vendor' => $vendor];

        return [
            'payments' => $this->countByVendor(VendorPayment::class, $criteria),
            'commissions' => $this->countByVendor(VendorCommission::class, $criteria),
            'commissionHistory' => $this->countByVendor(VendorCommissionHistory::class, $criteria),
            'conversations' => $this->countByVendor(VendorConversation::class, $criteria),
            'conversationMessages' => $this->countConversationMessages($vendor),
            'shipments' => $this->countByVendor(VendorShipment::class, $criteria),
            'groups' => $this->countByVendor(VendorGroup::class, $criteria),
            'categories' => $this->countByVendor(VendorCategory::class, $criteria),
            'favourites' => $this->countByVendor(VendorFavourite::class, $criteria),
            'wishlists' => $this->countByVendor(VendorWishlist::class, $criteria),
            'wishlistItems' => $this->countWishlistItems($vendor),
            'codes' => $this->countByVendor(VendorCodeStorage::class, $criteria),
            'rememberMeTokens' => $this->countByVendor(VendorRememberMeToken::class, $criteria),
            'customerOrders' => $this->countByVendor(VendorCustomerOrder::class, $criteria),
            'logs' => $this->countByVendor(VendorLog::class, $criteria),
        ];
    }

    /** @param array<string, int> $criteria */
    private function countByVendor(string $entityClass, array $criteria): int
    {
        return $this->entityManager->getRepository($entityClass)->count($criteria);
    }

    private function countConversationMessages(object $vendor): int
    {
        return (int) $this->entityManager->createQuery(
            'SELECT COUNT(message.id) FROM App\\Vendoring\\Entity\\VendorConversationMessage message JOIN message.conversation conversation WHERE conversation.vendor = :vendor',
        )
            ->setParameter('vendor', $vendor)
            ->getSingleScalarResult();
    }

    private function countWishlistItems(object $vendor): int
    {
        return (int) $this->entityManager->createQuery(
            'SELECT COUNT(item.id) FROM App\\Vendoring\\Entity\\VendorWishlistItem item JOIN item.wishlist wishlist WHERE wishlist.vendor = :vendor',
        )
            ->setParameter('vendor', $vendor)
            ->getSingleScalarResult();
    }
}
