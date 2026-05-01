<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Vendor;

use App\Vendoring\Entity\Vendor\VendorPayoutAccountEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorPayoutAccountRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class VendorPayoutAccountRepository implements VendorPayoutAccountRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function get(string $tenantId, string $vendorId): ?VendorPayoutAccountEntity
    {
        $entity = $this->entityManager->getRepository(VendorPayoutAccountEntity::class)->findOneBy([
            'tenantId' => $tenantId,
            'vendorId' => $vendorId,
        ]);

        return $entity instanceof VendorPayoutAccountEntity ? $entity : null;
    }

    public function upsert(VendorPayoutAccountEntity $account): void
    {
        $existing = $this->get($account->tenantId, $account->vendorId);
        if ($existing instanceof VendorPayoutAccountEntity) {
            $existing->provider = $account->provider;
            $existing->accountRef = $account->accountRef;
            $existing->currency = $account->currency;
            $existing->active = $account->active;

            $this->entityManager->flush();

            return;
        }

        $this->entityManager->persist($account);
        $this->entityManager->flush();
    }
}
