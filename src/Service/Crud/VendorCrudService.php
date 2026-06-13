<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Crud;

use App\Vendoring\DTO\VendorCreateDTO;
use App\Vendoring\DTO\VendorUpdateDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Event\Vendor\VendorActivatedEvent;
use App\Vendoring\Event\Vendor\VendorCreatedEvent;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\ServiceInterface\Assignment\VendorUserAssignmentServiceInterface;
use App\Vendoring\ServiceInterface\Crud\VendorCrudServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class VendorCrudService implements VendorCrudServiceInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $dispatcher,
        private VendorUserAssignmentServiceInterface $vendorUserAssignmentService,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @return list<VendorEntity>
     */
    public function index(): array
    {
        return array_values(array_filter(
            $this->vendorRepository->findBy([]),
            static fn (object $entity): bool => $entity instanceof VendorEntity,
        ));
    }

    public function find(int|string $id): ?VendorEntity
    {
        $vendor = $this->vendorRepository->find($id);

        return $vendor instanceof VendorEntity ? $vendor : null;
    }

    public function create(VendorCreateDTO $dto): VendorEntity
    {
        $this->assertValid($dto);

        $ownerUserId = $dto->resolveOwnerUserId();
        $brandName = $this->normalizeRequiredBrandName($dto->brandName);
        $vendor = new VendorEntity($brandName, $ownerUserId);

        $this->entityManager->persist($vendor);
        $this->entityManager->flush();

        if (null !== $ownerUserId && null !== $vendor->getId()) {
            $this->vendorUserAssignmentService->assignOwner($vendor->getId(), $ownerUserId);
        }

        $this->dispatcher->dispatch(new VendorCreatedEvent($vendor));

        return $vendor;
    }

    public function update(VendorEntity $vendor, VendorUpdateDTO $dto): VendorEntity
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

        $this->entityManager->flush();

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

        throw new \InvalidArgumentException($message);
    }

    private function normalizeRequiredBrandName(string $brandName): string
    {
        $normalized = trim($brandName);

        if ('' === $normalized) {
            throw new \InvalidArgumentException('brand_name_required');
        }

        return $normalized;
    }
}
