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

/**
 * Application service for vendor billing operations.
 */
final class VendorBillingService implements VendorBillingServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VendorBillingRepositoryInterface $repository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * Creates or updates the requested aggregate state.
     */
    public function upsert(Vendor $vendor, VendorBillingDTO $dto): VendorBilling
    {
        $billing = $this->repository->findOneBy(['vendor' => $vendor]) ?? new VendorBilling($vendor);

        $ref = new \ReflectionClass($billing);

        foreach (['iban', 'swift', 'payoutMethod', 'billingEmail'] as $prop) {
            if (property_exists($billing, $prop) && isset($dto->{$prop})) {
                $rp = $ref->getProperty($prop);
                $rp->setAccessible(true);
                $rp->setValue($billing, $dto->{$prop});
            }
        }

        $this->em->persist($billing);
        $this->em->flush();

        return $billing;
    }

    /**
     * Executes the request payout operation for this runtime surface.
     */
    public function requestPayout(VendorBilling $billing, int $amountMinor): void
    {
        $billing->markPayoutRequested();
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorPayoutRequestedEvent($billing, $amountMinor));
    }

    /**
     * Executes the complete payout operation for this runtime surface.
     */
    public function completePayout(VendorBilling $billing, int $amountMinor): void
    {
        $billing->markPayoutCompleted();
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorPayoutCompletedEvent($billing, $amountMinor));
    }
}
