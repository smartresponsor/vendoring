<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorPayoutAccountEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutAccountRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<VendorPayoutAccountEntity> */
final class VendorPayoutAccountRepository extends ServiceEntityRepository implements VendorPayoutAccountRepositoryInterface
{
    private ?EntityManagerInterface $directEm = null;

    /**
     * Accepts either a ManagerRegistry (Symfony DI path) or an EntityManagerInterface
     * directly (unit test path without a full DI container).
     */
    public function __construct(ManagerRegistry|EntityManagerInterface $registry)
    {
        if ($registry instanceof EntityManagerInterface) {
            $this->directEm = $registry;

            return;
        }

        /* @var ManagerRegistry $registry */
        parent::__construct($registry, VendorPayoutAccountEntity::class);
    }

    private function em(): EntityManagerInterface
    {
        return $this->directEm ?? $this->getEntityManager();
    }

    public function save(object $entity, bool $flush = false): void
    {
        $this->em()->persist($entity);
        if ($flush) {
            $this->em()->flush();
        }
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        if (null !== $this->directEm) {
            return $this->directEm->getRepository(VendorPayoutAccountEntity::class)->findOneBy($criteria);
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

            $this->em()->flush();

            return $existing;
        }

        $this->em()->persist($account);
        $this->em()->flush();

        return $account;
    }

    public function byId(mixed $id): ?object
    {
        $identifier = trim((string) $id);
        if ('' === $identifier) {
            return null;
        }

        return $this->findOneBy(['accountId' => $identifier]);
    }
}
