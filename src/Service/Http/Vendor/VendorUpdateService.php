<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor;

use App\Cruding\Dto\Crud\Entrypoint\CrudEntrypointContext;
use App\Cruding\Service\Crud\Entrypoint\AbstractCrudEntrypointService;
use App\Cruding\Value\Surface\CrudSurfaceContract;
use App\Vendoring\DTO\VendorUpdateDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Event\Vendor\VendorActivatedEvent;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\ServiceInterface\Assignment\VendorUserAssignmentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorUpdateService extends AbstractCrudEntrypointService
{
    public function __construct(
        private VendorHttpRouteResponseService $responseService,
        private VendorRepositoryInterface $vendorRepository,
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
            $this->resolveVendor($context),
        );
    }

    public function post(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return $this->mutate($context);
    }

    public function put(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return $this->mutate($context);
    }

    public function patch(CrudEntrypointContext $context): CrudSurfaceContract
    {
        return $this->mutate($context);
    }

    private function mutate(CrudEntrypointContext $context): CrudSurfaceContract
    {
        $input = $context->request->request->all();
        $vendor = $this->resolveVendor($context);
        $updated = $this->updateVendor($vendor, new VendorUpdateDTO(
            brandName: $this->nullableStringValue($input['brandName'] ?? null),
            status: $this->nullableStringValue($input['status'] ?? null),
            ownerUserId: $this->nullableIntValue($input['ownerUserId'] ?? null),
        ));

        return $this->responseService->mutation(
            $context,
            $this->resourcePath(),
            $this->operation(),
            $this->title(),
            $updated,
        );
    }

    private function updateVendor(VendorEntity $vendor, VendorUpdateDTO $dto): VendorEntity
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

    private function resolveVendor(CrudEntrypointContext $context): VendorEntity
    {
        if ($context->object instanceof VendorEntity) {
            return $context->object;
        }

        $identifier = $context->identifierValue();
        if (null === $identifier || '' === (string) $identifier) {
            throw new NotFoundHttpException('vendor_identifier_required');
        }

        $vendor = $this->vendorRepository->find($identifier);
        if (!$vendor instanceof VendorEntity) {
            throw new NotFoundHttpException('vendor_not_found');
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

    private function resourcePath(): string
    {
        return 'vendor';
    }

    private function operation(): string
    {
        return 'update';
    }

    private function title(): string
    {
        return 'Vendor '.$this->resourcePath().' '.$this->operation();
    }

    private function nullableStringValue(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return '' === $normalized ? null : $normalized;
    }

    private function nullableIntValue(mixed $value): ?int
    {
        if (null === $value || '' === $value) {
            return null;
        }

        return false !== filter_var($value, FILTER_VALIDATE_INT) ? (int) $value : null;
    }
}
