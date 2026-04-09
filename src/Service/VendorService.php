<?php

declare(strict_types=1);

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

namespace App\Service;

use App\DTO\VendorCreateDTO;
use App\DTO\VendorUpdateDTO;
use App\Entity\Vendor;
use App\Event\VendorActivatedEvent;
use App\Event\VendorCreatedEvent;
use App\ServiceInterface\VendorServiceInterface;
use App\ServiceInterface\VendorUserAssignmentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorService implements VendorServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly VendorUserAssignmentServiceInterface $vendorUserAssignmentService,
    ) {
    }

    public function create(VendorCreateDTO $dto): Vendor
    {
        $ownerUserId = $dto->resolveOwnerUserId();
        $vendor = new Vendor($dto->brandName, $ownerUserId);
        $this->em->persist($vendor);
        $this->em->flush();

        if (null !== $ownerUserId && null !== $vendor->getId()) {
            $this->vendorUserAssignmentService->assignOwner($vendor->getId(), $ownerUserId);
        }

        $this->dispatcher->dispatch(new VendorCreatedEvent($vendor));

        return $vendor;
    }

    public function update(Vendor $vendor, VendorUpdateDTO $dto): Vendor
    {
        if (null !== $dto->brandName) {
            $vendor->rename($dto->brandName);
        }

        $resolvedOwnerUserId = $dto->resolveOwnerUserId();

        if (null !== $resolvedOwnerUserId && $resolvedOwnerUserId !== $vendor->getOwnerUserId()) {
            $vendor->changeOwnerUserId($resolvedOwnerUserId);
        }

        if ('active' === $dto->status) {
            $vendor->activate();
            $this->dispatcher->dispatch(new VendorActivatedEvent($vendor));
        } elseif ('inactive' === $dto->status) {
            $vendor->deactivate();
        }

        $this->em->flush();

        if (null !== $resolvedOwnerUserId && null !== $vendor->getId()) {
            $this->vendorUserAssignmentService->assignOwner($vendor->getId(), $resolvedOwnerUserId);
        }

        return $vendor;
    }
}
