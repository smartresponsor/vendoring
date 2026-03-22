<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\DTO\VendorProfileDTO;
use App\Entity\Vendor\Vendor;
use App\Entity\Vendor\VendorProfile;
use App\Event\VendorProfileUpdatedEvent;
use App\RepositoryInterface\VendorProfileRepositoryInterface;
use App\ServiceInterface\VendorProfileServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorProfileService implements VendorProfileServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly VendorProfileRepositoryInterface $repository,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function upsert(Vendor $vendor, VendorProfileDTO $dto): VendorProfile
    {
        $profile = $this->repository->findOneBy(['vendor' => $vendor]) ?? new VendorProfile($vendor);

        $ref = new \ReflectionClass($profile);

        foreach (['displayName', 'about', 'website', 'socials', 'seoTitle', 'seoDescription'] as $prop) {
            if (property_exists($profile, $prop) && isset($dto->{$prop})) {
                $rp = $ref->getProperty($prop);
                $rp->setAccessible(true);
                $rp->setValue($profile, $dto->{$prop});
            }
        }

        $this->em->persist($profile);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorProfileUpdatedEvent($profile));

        return $profile;
    }
}
