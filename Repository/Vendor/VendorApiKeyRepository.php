<?php
declare(strict_types=1);

namespace App\Repository\Vendor;

use App\Entity\Vendor\VendorApiKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class VendorApiKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VendorApiKey::class);
    }

    public function findActiveByToken(string $tokenHash): ?VendorApiKey
    {
        return $this->createQueryBuilder('k')
            ->andWhere('k.token = :t')->setParameter('t', $tokenHash)
            ->andWhere('k.status = :s')->setParameter('s', 'active')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }
}
