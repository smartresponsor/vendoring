<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Identity;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorPassportEntity;
use App\Vendoring\Event\Vendor\VendorVerifiedEvent;
use App\Vendoring\ServiceInterface\Identity\VendorPassportServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorPassportService implements VendorPassportServiceInterface
{
    public function __construct(
        private EntityManagerInterface   $em,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function issue(VendorEntity $vendor, string $taxId, string $country): VendorPassportEntity
    {
        $passport = new VendorPassportEntity($vendor, $taxId, $country);
        $this->em->persist($passport);
        $this->em->flush();

        return $passport;
    }

    public function verify(VendorPassportEntity $passport): VendorPassportEntity
    {
        $passport->markVerified();
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorVerifiedEvent($passport));

        return $passport;
    }
}
