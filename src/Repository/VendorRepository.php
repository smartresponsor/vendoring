<?php

declare(strict_types=1);

namespace App\Vendoring\Repository;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\VendorOwnershipProjectionRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorEntity> */
final class VendorRepository extends ServiceEntityRepository implements VendorRepositoryInterface, VendorOwnershipProjectionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorEntity::class);
    }

    public function find(mixed $id, \Doctrine\DBAL\LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?VendorEntity
    {
        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity instanceof VendorEntity ? $entity : null;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorEntity
    {
        $entity = parent::findOneBy($criteria, $orderBy);

        return $entity instanceof VendorEntity ? $entity : null;
    }

    public function save(VendorEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(VendorEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findIndexRows(): array
    {
        /** @var list<array{id: int, brandName: string, ownerUserId: int|null}> $rows */
        $rows = $this->createQueryBuilder('vendor')
            ->select('vendor.id', 'vendor.brandName', 'vendor.ownerUserId')
            ->orderBy('vendor.id', 'ASC')
            ->getQuery()
            ->getArrayResult();

        return $rows;
    }

    public function findOneForUserId(int $userId): ?VendorEntity
    {
        $vendor = $this->createQueryBuilder('vendor')
            ->leftJoin('vendor.userAssignments', 'assignment')
            ->andWhere('vendor.ownerUserId = :userId OR (assignment.userId = :userId AND assignment.status = :activeStatus)')
            ->setParameter('userId', $userId)
            ->setParameter('activeStatus', 'active')
            ->addOrderBy('assignment.primaryAssignment', 'DESC')
            ->addOrderBy('vendor.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $vendor instanceof VendorEntity ? $vendor : null;
    }

    public function byId(mixed $id): ?VendorEntity
    {
        return $this->find($id);
    }

    public function findAccessBootstrapRows(): array
    {
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            'SELECT id, email, display_name, roles FROM access ORDER BY id',
        );
    }

    public function findPublishedRetailServiceRows(): array
    {
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            "SELECT id, owner_id, type_path, catalog_code FROM retail WHERE kind = 'service' AND owner_type = 'vendor' AND object_status = 'published' ORDER BY owner_id, type_path, id",
        );
    }

    public function seedDeterministicVendor(int $id, string $brandName, int $ownerUserId): void
    {
        $connection = $this->getEntityManager()->getConnection();
        $createdAt = new \DateTimeImmutable();

        $connection->executeStatement(
            <<<'SQL'
INSERT INTO vendor (id, brand_name, owner_user_id, status, created_at)
VALUES (:id, :brand_name, :owner_user_id, :status, :created_at)
ON CONFLICT (id) DO UPDATE SET
    brand_name = EXCLUDED.brand_name,
    owner_user_id = EXCLUDED.owner_user_id,
    status = EXCLUDED.status
SQL,
            [
                'id' => $id,
                'brand_name' => $brandName,
                'owner_user_id' => $ownerUserId,
                'status' => 'active',
                'created_at' => $createdAt,
            ],
            [
                'id' => Types::INTEGER,
                'brand_name' => Types::STRING,
                'owner_user_id' => Types::INTEGER,
                'status' => Types::STRING,
                'created_at' => Types::DATETIME_IMMUTABLE,
            ],
        );

        $connection->executeStatement(
            <<<'SQL'
SELECT setval(
    pg_get_serial_sequence('vendor', 'id'),
    GREATEST((SELECT COALESCE(MAX(id), 1) FROM vendor), 1),
    true
)
SQL
        );
    }

    public function relationCounts(VendorEntity $vendor): array
    {
        $criteria = ['vendor' => $vendor];

        return [
            'payments' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorPaymentEntity::class, $criteria),
            'commissions' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorCommissionEntity::class, $criteria),
            'commissionHistory' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorCommissionHistoryEntity::class, $criteria),
            'conversations' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorConversationEntity::class, $criteria),
            'conversationMessages' => $this->countConversationMessages($vendor),
            'shipments' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorShipmentEntity::class, $criteria),
            'groups' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorGroupEntity::class, $criteria),
            'categories' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorCategoryEntity::class, $criteria),
            'favourites' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorFavouriteEntity::class, $criteria),
            'wishlists' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorWishlistEntity::class, $criteria),
            'wishlistItems' => $this->countWishlistItems($vendor),
            'codes' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorCodeStorageEntity::class, $criteria),
            'rememberMeTokens' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorRememberMeTokenEntity::class, $criteria),
            'customerOrders' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorCustomerOrderEntity::class, $criteria),
            'logs' => $this->countRelation(\App\Vendoring\Entity\Vendor\VendorLogEntity::class, $criteria),
        ];
    }

    private function countRelation(string $entityClass, array $criteria): int
    {
        return $this->getEntityManager()->getRepository($entityClass)->count($criteria);
    }

    private function countConversationMessages(VendorEntity $vendor): int
    {
        return (int) $this->getEntityManager()->createQuery('SELECT COUNT(message.id) FROM App\\Vendoring\\Entity\\Vendor\\VendorConversationMessageEntity message JOIN message.conversation conversation WHERE conversation.vendor = :vendor')
            ->setParameter('vendor', $vendor)
            ->getSingleScalarResult();
    }

    private function countWishlistItems(VendorEntity $vendor): int
    {
        return (int) $this->getEntityManager()->createQuery('SELECT COUNT(item.id) FROM App\\Vendoring\\Entity\\Vendor\\VendorWishlistItemEntity item JOIN item.wishlist wishlist WHERE wishlist.vendor = :vendor')
            ->setParameter('vendor', $vendor)
            ->getSingleScalarResult();
    }
}
