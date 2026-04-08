<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\Entity\Vendor;
use App\Entity\VendorPassport;
use App\Event\VendorVerifiedEvent;
use App\ServiceInterface\VendorPassportServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Application service for vendor passport operations.
 */
final class VendorPassportService implements VendorPassportServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * Determines whether the requested condition is satisfied.
     */
    public function issue(Vendor $vendor, string $taxId, string $country): VendorPassport
    {
        $passport = new VendorPassport($vendor, $taxId, $country);
        $this->em->persist($passport);
        $this->em->flush();

        return $passport;
    }

    /**
     * Executes the verify operation for this runtime surface.
     */
    public function verify(VendorPassport $passport): VendorPassport
    {
        $passport->markVerified();
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorVerifiedEvent($passport));

        return $passport;
    }
}
