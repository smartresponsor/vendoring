<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Billing;

use App\Vendoring\DTO\VendorBillingDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use App\Vendoring\Entity\Vendor\VendorIbanEntity;
use App\Vendoring\Event\Vendor\VendorPayoutCompletedEvent;
use App\Vendoring\Event\Vendor\VendorPayoutRequestedEvent;
use App\Vendoring\RepositoryInterface\Vendor\VendorBillingRepositoryInterface;
use App\Vendoring\ServiceInterface\Billing\VendorBillingServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorBillingService implements VendorBillingServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private VendorBillingRepositoryInterface $repository,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function upsert(VendorEntity $vendor, VendorBillingDTO $dto): VendorBillingEntity
    {
        $billing = $this->repository->findOneBy(['vendor' => $vendor]) ?? new VendorBillingEntity($vendor);
        $billing->update(
            $this->normalizeNullableString($dto->iban),
            $this->normalizeNullableString($dto->swift),
            $this->normalizeRequiredPayoutMethod($dto->payoutMethod),
            $this->normalizeNullableString($dto->billingEmail),
        );

        $this->em->persist($billing);
        $this->synchronizeIban($vendor, $this->normalizeNullableString($dto->iban), $this->normalizeNullableString($dto->swift));
        $this->em->flush();

        return $billing;
    }

    public function requestPayout(VendorBillingEntity $billing, int $amountMinor): void
    {
        $billing->markPayoutRequested();
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorPayoutRequestedEvent($billing, $amountMinor));
    }

    public function completePayout(VendorBillingEntity $billing, int $amountMinor): void
    {
        $billing->markPayoutCompleted();
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorPayoutCompletedEvent($billing, $amountMinor));
    }


    private function synchronizeIban(VendorEntity $vendor, ?string $iban, ?string $swift): void
    {
        $repository = $this->em->getRepository(VendorIbanEntity::class);
        $existing = $repository->findOneBy(['vendor' => $vendor]);

        if (null === $iban) {
            if ($existing instanceof VendorIbanEntity) {
                $this->em->remove($existing);
            }

            return;
        }

        if ($existing instanceof VendorIbanEntity) {
            $existing->update($iban, $swift);

            return;
        }

        $this->em->persist(new VendorIbanEntity($vendor, $iban, $swift));
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
