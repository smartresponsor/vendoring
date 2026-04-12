<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\DTO\VendorBillingDTO;
use App\Entity\Vendor;
use App\Entity\VendorBilling;
use App\Event\VendorPayoutCompletedEvent;
use App\Event\VendorPayoutRequestedEvent;
use App\RepositoryInterface\VendorBillingRepositoryInterface;
use App\ServiceInterface\VendorBillingServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorBillingService implements VendorBillingServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private VendorBillingRepositoryInterface $repository,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function upsert(Vendor $vendor, VendorBillingDTO $dto): VendorBilling
    {
        $billing = $this->repository->findOneBy(['vendor' => $vendor]) ?? new VendorBilling($vendor);
        $billing->update(
            $this->normalizeNullableString($dto->iban),
            $this->normalizeNullableString($dto->swift),
            $this->normalizeRequiredPayoutMethod($dto->payoutMethod),
            $this->normalizeNullableString($dto->billingEmail),
        );

        $this->em->persist($billing);
        $this->em->flush();

        return $billing;
    }

    public function requestPayout(VendorBilling $billing, int $amountMinor): void
    {
        $billing->markPayoutRequested();
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorPayoutRequestedEvent($billing, $amountMinor));
    }

    public function completePayout(VendorBilling $billing, int $amountMinor): void
    {
        $billing->markPayoutCompleted();
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorPayoutCompletedEvent($billing, $amountMinor));
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }

    private function normalizeRequiredPayoutMethod(?string $value): string
    {
        $trimmed = null === $value ? '' : trim($value);

        return '' === $trimmed ? 'bank' : $trimmed;
    }
}
