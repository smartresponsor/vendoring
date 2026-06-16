<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor;

use App\Cruding\Dto\Crud\Entrypoint\CrudEntrypointContext;
use App\Cruding\Service\Crud\Entrypoint\AbstractCrudEntrypointService;
use App\Cruding\Value\Surface\CrudSurfaceContract;
use App\Vendoring\DTO\VendorCreateDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Event\Vendor\VendorCreatedEvent;
use App\Vendoring\ServiceInterface\Assignment\VendorUserAssignmentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorCreateService extends AbstractCrudEntrypointService
{
    public function __construct(
        private VendorHttpRouteResponseService $responseService,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $dispatcher,
        private VendorUserAssignmentServiceInterface $vendorUserAssignmentService,
        private ValidatorInterface $validator,
    ) {
    }

    public function get(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return $this->responseService->read(
            $context,
            $this->resourcePath(),
            $this->operation(),
            $this->title(),
            [
                'brandName' => '',
                'ownerUserId' => null,
            ],
        );
    }

    public function post(CrudEntrypointContext $context): CrudSurfaceContract
    {
        $input = $context->request->request->all();
        $vendor = $this->createVendor(new VendorCreateDTO(
            brandName: $this->stringValue($input['brandName'] ?? null),
            ownerUserId: $this->nullableIntValue($input['ownerUserId'] ?? null),
        ));

        return $this->responseService->mutation(
            $context,
            $this->resourcePath(),
            $this->operation(),
            $this->title(),
            $vendor,
        );
    }

    private function createVendor(VendorCreateDTO $dto): VendorEntity
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

    private function resourcePath(): string
    {
        return 'vendor';
    }

    private function operation(): string
    {
        return 'create';
    }

    private function title(): string
    {
        return 'Vendor '.$this->resourcePath().' '.$this->operation();
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function nullableIntValue(mixed $value): ?int
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return false !== filter_var($value, FILTER_VALIDATE_INT) ? (int) $value : null;
    }
}
