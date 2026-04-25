<?php

declare(strict_types=1);

namespace App\Vendoring\Repository\Payout;

use App\Vendoring\Entity\Payout\PayoutAccount;
use App\Vendoring\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class PayoutAccountRepository implements PayoutAccountRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function get(string $tenantId, string $vendorId): ?PayoutAccount
    {
        $entity = $this->entityManager->getRepository(PayoutAccount::class)->findOneBy([
            'tenantId' => $tenantId,
            'vendorId' => $vendorId,
        ]);

        return $entity instanceof PayoutAccount ? $entity : null;
    }

    public function upsert(PayoutAccount $account): void
    {
        $existing = $this->get($account->tenantId, $account->vendorId);
        if ($existing instanceof PayoutAccount) {
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
