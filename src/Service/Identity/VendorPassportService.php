<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Vendoring\Service\Identity;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorPassportEntity;
use App\Vendoring\Event\VendorVerifiedEvent;
use App\Vendoring\RepositoryInterface\VendorPassportRepositoryInterface;
use App\Vendoring\ServiceInterface\Identity\VendorPassportServiceInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorPassportService implements VendorPassportServiceInterface
{
    public function __construct(
        private VendorPassportRepositoryInterface $passportRepository,
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function issue(VendorEntity $vendor, string $taxId, string $country): VendorPassportEntity
    {
        $passport = new VendorPassportEntity($vendor, $taxId, $country);
        $this->passportRepository->save($passport, true);

        return $passport;
    }

    public function verify(VendorPassportEntity $passport): VendorPassportEntity
    {
        $passport->markVerified();
        $this->passportRepository->save($passport, true);

        $this->dispatcher->dispatch(new VendorVerifiedEvent($passport));

        return $passport;
    }
}
