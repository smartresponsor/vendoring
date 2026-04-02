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

final class VendorBillingService implements VendorBillingServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VendorBillingRepositoryInterface $repository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function upsert(Vendor $vendor, VendorBillingDTO $dto): VendorBilling
    {
        $billing = $this->repository->findOneBy(['vendor' => $vendor]) ?? new VendorBilling($vendor);
        $ref = new \ReflectionClass($billing);

        $this->setProperty($ref, $billing, 'iban', $this->normalizeNullableString($dto->iban));
        $this->setProperty($ref, $billing, 'swift', $this->normalizeNullableString($dto->swift));
        $this->setProperty($ref, $billing, 'payoutMethod', $this->normalizePayoutMethod($dto->payoutMethod));
        $this->setProperty($ref, $billing, 'billingEmail', $this->normalizeNullableString($dto->billingEmail));

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

    private function setProperty(\ReflectionClass $ref, VendorBilling $billing, string $property, mixed $value): void
    {
        $rp = $ref->getProperty($property);
        $rp->setAccessible(true);
        $rp->setValue($billing, $value);
    }

    private function normalizeNullableString(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }

    private function normalizePayoutMethod(string $value): string
    {
        $trimmed = trim($value);

        return '' === $trimmed ? 'bank' : $trimmed;
    }
}
