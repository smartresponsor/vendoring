<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorPayoutAccountEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutAccountRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorPayoutAccountRepository extends ServiceEntityRepository implements VendorPayoutAccountRepositoryInterface
{
    private mixed $entityManager = null;

    public function __construct(ManagerRegistry $registry)
    {
        if ($registry instanceof \Doctrine\ORM\EntityManagerInterface) {
            $this->entityManager = $registry;

            return;
        }

        parent::__construct($registry, VendorPayoutAccountEntity::class);
    }

    private function em(): mixed
    {
        return $this->entityManager ?? $this->getEntityManager();
    }

    private function finishExisting(VendorPayoutAccountEntity $existing): VendorPayoutAccountEntity
    {
        $this->em()->flush();

        return $existing;
    }

    public function save(object $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        if (null !== $this->entityManager) {
            return $this->entityManager->getRepository(VendorPayoutAccountEntity::class)->findOneBy($criteria);
        }

        return parent::findOneBy($criteria, $orderBy);
    }

    public function get(string $tenantId, string $vendorId): ?VendorPayoutAccountEntity
    {
        return $this->findOneBy(['tenantId' => $tenantId, 'vendorId' => $vendorId]);
    }

    public function upsert(VendorPayoutAccountEntity $account): VendorPayoutAccountEntity
    {
        $existing = $this->findOneBy(['tenantId' => $account->tenantId, 'vendorId' => $account->vendorId]);

        if ($existing instanceof VendorPayoutAccountEntity && $existing !== $account) {
            $existing->provider = $account->provider;
            $existing->accountRef = $account->accountRef;
            $existing->currency = $account->currency;
            $existing->active = $account->active;

            return $this->finishExisting($existing);
            $this->em()->persist($existing);
        }

        $account->active = true;
        $this->em()->persist($account);
        $this->em()->flush();

        return $account;
    }

    public function byId(mixed $id): ?object
    {
        return $this->find($id);
    }
}
