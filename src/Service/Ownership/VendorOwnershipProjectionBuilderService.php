<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Ownership;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorCategoryEntity;
use App\Vendoring\Entity\Vendor\VendorCodeStorageEntity;
use App\Vendoring\Entity\Vendor\VendorCommissionEntity;
use App\Vendoring\Entity\Vendor\VendorCommissionHistoryEntity;
use App\Vendoring\Entity\Vendor\VendorConversationEntity;
use App\Vendoring\Entity\Vendor\VendorConversationMessageEntity;
use App\Vendoring\Entity\Vendor\VendorCustomerOrderEntity;
use App\Vendoring\Entity\Vendor\VendorFavouriteEntity;
use App\Vendoring\Entity\Vendor\VendorGroupEntity;
use App\Vendoring\Entity\Vendor\VendorLogEntity;
use App\Vendoring\Entity\Vendor\VendorPaymentEntity;
use App\Vendoring\Entity\Vendor\VendorRememberMeTokenEntity;
use App\Vendoring\Entity\Vendor\VendorShipmentEntity;
use App\Vendoring\Entity\Vendor\VendorWishlistEntity;
use App\Vendoring\Entity\Vendor\VendorWishlistItemEntity;
use App\Vendoring\Projection\Vendor\VendorOwnershipProjection;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\ServiceInterface\Security\VendorAuthorizationMatrixServiceInterface;
use App\Vendoring\ServiceInterface\Ownership\VendorOwnershipProjectionBuilderServiceInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Builds a vendor-local ownership/access summary without pulling any external User aggregate.
 */
final readonly class VendorOwnershipProjectionBuilderService implements VendorOwnershipProjectionBuilderServiceInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository,
        private VendorUserAssignmentRepositoryInterface $assignmentRepository,
        private VendorAuthorizationMatrixServiceInterface $authorizationMatrix,
        private EntityManagerInterface $entityManager,
    ) {}

    public function buildForVendorId(int $vendorId): ?VendorOwnershipProjection
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

        return new VendorOwnershipProjection(
            vendorId: $vendorId,
            ownerUserId: $vendor->getOwnerUserId(),
            assignments: $assignments,
            relationCounts: $this->buildRelationCounts($vendor),
        );
    }

    /** @return array<string, int> */
    private function buildRelationCounts(VendorEntity $vendor): array
    {
        $criteria = ['vendor' => $vendor];

        return [
            'payments' => $this->countByVendor(VendorPaymentEntity::class, $criteria),
            'commissions' => $this->countByVendor(VendorCommissionEntity::class, $criteria),
            'commissionHistory' => $this->countByVendor(VendorCommissionHistoryEntity::class, $criteria),
            'conversations' => $this->countByVendor(VendorConversationEntity::class, $criteria),
            'conversationMessages' => $this->countConversationMessages($vendor),
            'shipments' => $this->countByVendor(VendorShipmentEntity::class, $criteria),
            'groups' => $this->countByVendor(VendorGroupEntity::class, $criteria),
            'categories' => $this->countByVendor(VendorCategoryEntity::class, $criteria),
            'favourites' => $this->countByVendor(VendorFavouriteEntity::class, $criteria),
            'wishlists' => $this->countByVendor(VendorWishlistEntity::class, $criteria),
            'wishlistItems' => $this->countWishlistItems($vendor),
            'codes' => $this->countByVendor(VendorCodeStorageEntity::class, $criteria),
            'rememberMeTokens' => $this->countByVendor(VendorRememberMeTokenEntity::class, $criteria),
            'customerOrders' => $this->countByVendor(VendorCustomerOrderEntity::class, $criteria),
            'logs' => $this->countByVendor(VendorLogEntity::class, $criteria),
        ];
    }

    /**
     * @param class-string<object> $entityClass
     * @param array<string, object> $criteria
     */
    private function countByVendor(string $entityClass, array $criteria): int
    {
        return $this->entityManager->getRepository($entityClass)->count($criteria);
    }

    private function countConversationMessages(object $vendor): int
    {
        return (int) $this->entityManager->createQuery(
            'SELECT COUNT(message.id) FROM App\\Vendoring\\Entity\\Vendor\\VendorConversationMessageEntity message JOIN message.conversation conversation WHERE conversation.vendor = :vendor',
        )
            ->setParameter('vendor', $vendor)
            ->getSingleScalarResult();
    }

    private function countWishlistItems(object $vendor): int
    {
        return (int) $this->entityManager->createQuery(
            'SELECT COUNT(item.id) FROM App\\Vendoring\\Entity\\Vendor\\VendorWishlistItemEntity item JOIN item.wishlist wishlist WHERE wishlist.vendor = :vendor',
        )
            ->setParameter('vendor', $vendor)
            ->getSingleScalarResult();
    }
}
