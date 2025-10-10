<?php
declare(strict_types=1);

namespace App\Service\Vendor;

use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorPassport;
use App\Event\Vendor\VendorVerifiedEvent;
use App\Repository\Vendor\VendorPassportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorPassportService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VendorPassportRepository $repository,
        private readonly EventDispatcherInterface $dispatcher
    ) {}

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
