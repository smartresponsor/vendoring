<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Vendor;

use App\Cruding\DTO\Entrypoint\CrudServiceContextDTO;
use App\Cruding\ValueObject\Resource\CrudResourceContract;
use App\Vendoring\DTO\VendorUpdateDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Event\Vendor\VendorActivatedEvent;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\ServiceInterface\Assignment\VendorUserAssignmentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorUpdateService extends AbstractVendorCrudRouteService
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

    public function get(CrudServiceContextDTO $context): CrudResourceContract
    {
        return $this->responseService->read(
            $context,
            $this->resourcePath(),
            $this->operation(),
            $this->title(),
            $this->resolveVendor($context),
        );
    }

    public function post(CrudServiceContextDTO $context): CrudResourceContract
    {
        return $this->mutate($context);
    }

    public function put(CrudServiceContextDTO $context): CrudResourceContract
    {
        return $this->mutate($context);
    }

    public function patch(CrudServiceContextDTO $context): CrudResourceContract
    {
        return $this->mutate($context);
    }

    private function mutate(CrudServiceContextDTO $context): CrudResourceContract
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

    private function resolveVendor(CrudServiceContextDTO $context): VendorEntity
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

    protected function resourcePath(): string
    {
        return 'vendor';
    }

    protected function operation(): string
    {
        return 'update';
    }

    protected function title(): string
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
        if (is_int($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $normalized = trim($value);
        if ('' === $normalized || 1 !== preg_match('/^-?\d+$/D', $normalized)) {
            return null;
        }

        return (int) $normalized;
    }
}
