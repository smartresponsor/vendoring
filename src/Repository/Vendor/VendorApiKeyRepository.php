<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorApiKeyEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorApiKeyRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorApiKeyEntity> */
final class VendorApiKeyRepository extends ServiceEntityRepository implements VendorApiKeyRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorApiKeyEntity::class);
    }

    public function find(mixed $id, \Doctrine\DBAL\LockMode|int|null $lockMode = null, ?int $lockVersion = null): ?VendorApiKeyEntity
    {
        $entity = parent::find($id, $lockMode, $lockVersion);

        return $entity instanceof VendorApiKeyEntity ? $entity : null;
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?VendorApiKeyEntity
    {
        $entity = parent::findOneBy($criteria, $orderBy);

        return $entity instanceof VendorApiKeyEntity ? $entity : null;
    }

    public function save(VendorApiKeyEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function byId(mixed $id): ?VendorApiKeyEntity
    {
        return $this->find($id);
    }

    public function findActiveByTokenHash(string $tokenHash): ?VendorApiKeyEntity
    {
        return $this->findOneBy([
            'tokenHash' => $tokenHash,
            'status' => 'active',
        ]);
    }
}
