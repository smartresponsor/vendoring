<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Billing;

use App\Vendoring\DTO\VendorBillingDTO;
use App\Vendoring\Entity\Vendor\VendorBillingEntity;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorIbanEntity;
use App\Vendoring\Event\VendorPayoutCompletedEvent;
use App\Vendoring\Event\VendorPayoutRequestedEvent;
use App\Vendoring\RepositoryInterface\VendorBillingRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorIbanRepositoryInterface;
use App\Vendoring\ServiceInterface\Billing\VendorBillingServiceInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorBillingService implements VendorBillingServiceInterface
{
    public function __construct(
        private VendorBillingRepositoryInterface $repository,
        private VendorIbanRepositoryInterface $ibanRepository,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function upsert(VendorEntity $vendor, VendorBillingDTO $dto): VendorBillingEntity
    {
        $billing = $this->repository->findOneBy(['vendor' => $vendor]) ?? new VendorBillingEntity($vendor);
        $billing->update(
            $this->normalizeNullableString($dto->iban),
            $this->normalizeNullableString($dto->swift),
            $this->normalizeRequiredPayoutMethod($dto->payoutMethod),
            $this->normalizeNullableString($dto->billingEmail),
        );

        $this->repository->save($billing);
        $this->synchronizeIban($vendor, $this->normalizeNullableString($dto->iban), $this->normalizeNullableString($dto->swift));
        $this->repository->flush();

        return $billing;
    }

    public function requestPayout(VendorBillingEntity $billing, int $amountMinor): void
    {
        $billing->markPayoutRequested();
        $this->repository->save($billing, true);

        $this->dispatcher->dispatch(new VendorPayoutRequestedEvent($billing, $amountMinor));
    }

    public function completePayout(VendorBillingEntity $billing, int $amountMinor): void
    {
        $billing->markPayoutCompleted();
        $this->repository->save($billing, true);

        $this->dispatcher->dispatch(new VendorPayoutCompletedEvent($billing, $amountMinor));
    }

    private function synchronizeIban(VendorEntity $vendor, ?string $iban, ?string $swift): void
    {
        $existing = $this->ibanRepository->findOneBy(['vendor' => $vendor]);

        if (null === $iban) {
            if ($existing instanceof VendorIbanEntity) {
                $this->ibanRepository->remove($existing);
            }

            return;
        }

        if ($existing instanceof VendorIbanEntity) {
            $existing->update($iban, $swift);

            return;
        }

        $this->ibanRepository->save(new VendorIbanEntity($vendor, $iban, $swift));
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
