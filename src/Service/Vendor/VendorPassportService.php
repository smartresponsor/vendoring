<?php
declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service\Vendor;

use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorPassport;
use App\Event\Vendor\VendorVerifiedEvent;
use App\RepositoryInterface\Vendor\VendorPassportRepositoryInterface;
use App\ServiceInterface\Vendor\VendorPassportServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorPassportService implements VendorPassportServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface            $em,
        private readonly VendorPassportRepositoryInterface $repository,
        private readonly EventDispatcherInterface          $dispatcher
    )
    {
    }

    public function issue(Vendor $vendor, string $taxId, string $country): VendorPassport
    {
        $passport = new VendorPassport($vendor, $taxId, $country);
        $this->em->persist($passport);
        $this->em->flush();

        return $passport;
    }

    public function verify(VendorPassport $passport): VendorPassport
    {
        $passport->markVerified();
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorVerifiedEvent($passport));

        return $passport;
    }
}
