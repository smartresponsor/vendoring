<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\DTO\VendorProfileDTO;
use App\Entity\Vendor;
use App\Entity\VendorProfile;
use App\Event\VendorProfileUpdatedEvent;
use App\RepositoryInterface\VendorProfileRepositoryInterface;
use App\ServiceInterface\VendorProfileServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorProfileService implements VendorProfileServiceInterface
{
    public function __construct(
        private EntityManagerInterface           $em,
        private VendorProfileRepositoryInterface $repository,
        private EventDispatcherInterface         $dispatcher,
    ) {
    }

    /** @throws ORMException|OptimisticLockException */
    public function upsert(Vendor $vendor, VendorProfileDTO $dto): VendorProfile
    {
        $profile = $this->repository->findOneBy(['vendor' => $vendor]) ?? new VendorProfile($vendor);
        $profile->updateProfile(
            displayName: $dto->displayName,
            about: $dto->about,
            website: $dto->website,
            socials: $dto->socials,
            seoTitle: $dto->seoTitle,
            seoDescription: $dto->seoDescription,
        );

        if ('publish' === $dto->publicationAction && $profile->isPublishable()) {
            $profile->publish();
        } elseif ('unpublish' === $dto->publicationAction) {
            $profile->unpublish();
        }

        $this->em->persist($profile);
        $this->em->flush();

        $this->dispatcher->dispatch(new VendorProfileUpdatedEvent($profile));

        return $profile;
    }
}
