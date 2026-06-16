<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Cruding\Dto\Crud\CrudContext;
use App\Cruding\Dto\Crud\Entrypoint\CrudEntrypointContext;
use App\Cruding\Value\Surface\CrudSurfaceContract;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\Service\Http\Vendor\VendorCreateService;
use App\Vendoring\Service\Http\Vendor\VendorDeleteService;
use App\Vendoring\Service\Http\Vendor\VendorHttpRouteResponseService;
use App\Vendoring\Service\Http\Vendor\VendorIndexService;
use App\Vendoring\Service\Http\Vendor\VendorShowService;
use App\Vendoring\Service\Http\Vendor\VendorUpdateService;
use App\Vendoring\ServiceInterface\Assignment\VendorUserAssignmentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorRouteServicesTest extends TestCase
{
    private VendorRepositoryInterface&MockObject $vendorRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private EventDispatcherInterface&MockObject $dispatcher;
    private VendorUserAssignmentServiceInterface&MockObject $assignmentService;
    private ValidatorInterface&MockObject $validator;
    private VendorHttpRouteResponseService $responseService;

    protected function setUp(): void
    {
        $this->vendorRepository = $this->createMock(VendorRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->assignmentService = $this->createMock(VendorUserAssignmentServiceInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->responseService = new VendorHttpRouteResponseService();
    }

    public function testIndexReturnsSurfaceContract(): void
    {
        $this->vendorRepository
            ->expects(self::once())
            ->method('findBy')
            ->with([])
            ->willReturn([new VendorEntity('Alpha')]);

        $service = new VendorIndexService($this->responseService, $this->vendorRepository);

        self::assertInstanceOf(CrudSurfaceContract::class, $service->get($this->context('index')));
    }

    public function testShowReturnsSurfaceContractForResolvedVendor(): void
    {
        $vendor = new VendorEntity('Alpha');

        $service = new VendorShowService($this->responseService, $this->vendorRepository);

        self::assertInstanceOf(CrudSurfaceContract::class, $service->get($this->context('show', object: $vendor, identifierValue: 1)));
    }

    public function testCreateTrimsBrandNameBeforeEntityConstruction(): void
    {
        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::callback(static fn (object $entity): bool => $entity instanceof VendorEntity && 'Smartresponsor' === $entity->getBrandName()));
        $this->entityManager->expects(self::once())->method('flush');
        $this->assignmentService->expects(self::never())->method('assignOwner');
        $this->dispatcher->expects(self::once())->method('dispatch');

        $service = new VendorCreateService(
            $this->responseService,
            $this->entityManager,
            $this->dispatcher,
            $this->assignmentService,
            $this->validator,
        );

        $response = $service->post($this->context('create', requestData: [
            'brandName' => '  Smartresponsor  ',
            'ownerUserId' => null,
        ]));

        self::assertInstanceOf(CrudSurfaceContract::class, $response);
    }

    public function testCreateThrowsDomainValidationExceptionWhenDtoInvalid(): void
    {
        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('brand_name_required', '', [], null, 'brandName', ''),
            ]));

        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');

        $service = new VendorCreateService(
            $this->responseService,
            $this->entityManager,
            $this->dispatcher,
            $this->assignmentService,
            $this->validator,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('brand_name_required');

        $service->post($this->context('create', requestData: [
            'brandName' => '',
            'ownerUserId' => null,
        ]));
    }

    public function testUpdateTrimsBrandNameWhenProvided(): void
    {
        $vendor = new VendorEntity('Old');

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects(self::once())->method('flush');
        $this->dispatcher->expects(self::once())->method('dispatch');
        $this->assignmentService->expects(self::never())->method('assignOwner');

        $service = new VendorUpdateService(
            $this->responseService,
            $this->vendorRepository,
            $this->entityManager,
            $this->dispatcher,
            $this->assignmentService,
            $this->validator,
        );

        $response = $service->post($this->context('update', object: $vendor, identifierValue: 1, requestData: [
            'brandName' => '  New Name  ',
            'status' => 'active',
            'ownerUserId' => null,
        ]));

        self::assertSame('New Name', $vendor->getBrandName());
        self::assertInstanceOf(CrudSurfaceContract::class, $response);
    }

    public function testDeleteRemovesVendorEntity(): void
    {
        $vendor = new VendorEntity('Old');

        $this->entityManager->expects(self::once())->method('remove')->with($vendor);
        $this->entityManager->expects(self::once())->method('flush');

        $service = new VendorDeleteService($this->responseService, $this->vendorRepository, $this->entityManager);

        $response = $service->delete($this->context('delete', object: $vendor, identifierValue: 1));

        self::assertInstanceOf(CrudSurfaceContract::class, $response);
    }

    private function context(
        string $operation,
        ?object $object = null,
        string|int|null $identifierValue = null,
        array $requestData = [],
    ): CrudEntrypointContext {
        $request = new Request([], $requestData);
        $request->attributes->set('_route', 'vendor.'.$operation);
        $request->attributes->set('_crud_actor_is_admin', true);

        if (null !== $identifierValue) {
            $request->attributes->set('_crud_actor_identity_value', $identifierValue);
        }

        return new CrudEntrypointContext(
            request: $request,
            crudContext: new CrudContext(
                surface: 'admin',
                operation: $operation,
                resourcePath: 'vendor',
                entityClass: VendorEntity::class,
                identifierField: 'id',
                identifierValue: $identifierValue,
                formTypeClass: null,
            ),
            object: $object,
        );
    }
}
