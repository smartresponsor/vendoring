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
use InvalidArgumentException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorService implements VendorServiceInterface
{
    public function __construct(
        private EntityManagerInterface               $em,
        private EventDispatcherInterface             $dispatcher,
        private VendorUserAssignmentServiceInterface $vendorUserAssignmentService,
        private ValidatorInterface                   $validator,
    ) {}

    public function create(VendorCreateDTO $dto): Vendor
    {
        $this->assertValid($dto);

        $ownerUserId = $dto->resolveOwnerUserId();
        $brandName = $this->normalizeRequiredBrandName($dto->brandName);
        $vendor = new Vendor($brandName, $ownerUserId);
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
        $this->assertValid($dto);

        if (null !== $dto->brandName) {
            $vendor->rename($this->normalizeRequiredBrandName($dto->brandName));
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

    private function assertValid(object $dto): void
    {
        $violations = $this->validator->validate($dto);

        if (0 === count($violations)) {
            return;
        }

        $firstViolation = $violations[0] ?? null;
        $message = null !== $firstViolation ? $firstViolation->getMessage() : 'vendor_validation_failed';
        $message = is_string($message) ? $message : (string) $message;

        throw new InvalidArgumentException($message);
    }

    private function normalizeRequiredBrandName(string $brandName): string
    {
        $normalized = trim($brandName);

        if ('' === $normalized) {
            throw new InvalidArgumentException('brand_name_required');
        }

        return $normalized;
    }
}
