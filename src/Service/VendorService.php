<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Service;

use App\DTO\VendorCreateDTO;
use App\DTO\VendorUpdateDTO;
use App\Entity\Vendor;
use App\Event\VendorActivatedEvent;
use App\Event\VendorCreatedEvent;
use App\ServiceInterface\VendorServiceInterface;
use App\ServiceInterface\VendorUserAssignmentServiceInterface;
use App\ValueObject\BrandName;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorService implements VendorServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly VendorUserAssignmentServiceInterface $vendorUserAssignmentService,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function create(VendorCreateDTO $dto): Vendor
    {
        $this->assertValidDto($dto);

        $ownerUserId = $dto->resolveOwnerUserId();
        $brandName = BrandName::fromRaw($dto->brandName);
        $vendor = new Vendor($brandName->value(), $ownerUserId);
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
        $this->assertValidDto($dto);

        if (null !== $dto->brandName) {
            $vendor->rename(BrandName::fromRaw($dto->brandName)->value());
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

    private function assertValidDto(object $dto): void
    {
        $violations = $this->validator->validate($dto);

        if (0 === $violations->count()) {
            return;
        }

        throw new \InvalidArgumentException($this->buildViolationMessage($violations));
    }

    private function buildViolationMessage(ConstraintViolationListInterface $violations): string
    {
        $messages = [];

        foreach ($violations as $violation) {
            $messages[] = $violation->getMessage();
        }

        return implode('; ', $messages);
    }
}
