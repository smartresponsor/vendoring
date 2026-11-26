<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\DTO\VendorCreateDTO;
use App\Vendoring\DTO\VendorUpdateDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Event\VendorCreatedEvent;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\Service\Crud\VendorCrudService;
use App\Vendoring\ServiceInterface\Assignment\VendorUserAssignmentServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorCrudServiceTest extends TestCase
{
    public function testCreateRejectsWhitespaceBeforePersistence(): void
    {
        $repository = $this->createMock(VendorRepositoryInterface::class);
        $repository->expects(self::never())->method('save');
        $service = $this->service($repository);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('brand_name_required');
        $service->create(new VendorCreateDTO(brandName: '   '));
    }

    public function testUpdateRejectsWhitespaceBeforePersistence(): void
    {
        $repository = $this->createMock(VendorRepositoryInterface::class);
        $repository->expects(self::never())->method('save');
        $service = $this->service($repository);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('brand_name_required');
        $service->update(new VendorEntity('Existing', null), new VendorUpdateDTO(brandName: '   '));
    }

    public function testCreateTrimsBrandNameAndDispatchesPersistedVendorEvent(): void
    {
        $repository = $this->createMock(VendorRepositoryInterface::class);
        $repository->expects(self::once())->method('save')->with(self::callback(static fn (VendorEntity $vendor): bool => 'Acme' === $vendor->getBrandName()), true);
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::once())->method('dispatch')->with(self::isInstanceOf(VendorCreatedEvent::class))->willReturnArgument(0);
        $assignment = $this->createMock(VendorUserAssignmentServiceInterface::class);
        $assignment->expects(self::never())->method('assignOwner');
        $vendor = (new VendorCrudService($repository, $dispatcher, $assignment, $validator))->create(new VendorCreateDTO('  Acme  '));
        self::assertSame('Acme', $vendor->getBrandName());
        self::assertSame('inactive', $vendor->getStatus());
    }

    public function testUpdateTrimsBrandNameAndPersistsSameEntity(): void
    {
        $vendor = new VendorEntity('Old');
        $repository = $this->createMock(VendorRepositoryInterface::class);
        $repository->expects(self::once())->method('save')->with($vendor, true);
        $service = $this->service($repository);
        self::assertSame($vendor, $service->update($vendor, new VendorUpdateDTO(brandName: '  New  ')));
        self::assertSame('New', $vendor->getBrandName());
    }

    public function testConstraintViolationPreventsPersistenceAndEvent(): void
    {
        $repository = $this->createMock(VendorRepositoryInterface::class);
        $repository->expects(self::never())->method('save');
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList([
            new ConstraintViolation('invalid_vendor', null, [], null, 'brandName', 'invalid'),
        ]));
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::never())->method('dispatch');
        $assignment = $this->createMock(VendorUserAssignmentServiceInterface::class);
        $assignment->expects(self::never())->method('assignOwner');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid_vendor');
        (new VendorCrudService($repository, $dispatcher, $assignment, $validator))->create(new VendorCreateDTO('invalid'));
    }

    private function service(VendorRepositoryInterface $repository): VendorCrudService
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(self::never())->method('dispatch');
        $assignment = $this->createMock(VendorUserAssignmentServiceInterface::class);
        $assignment->expects(self::never())->method('assignOwner');

        return new VendorCrudService($repository, $dispatcher, $assignment, $validator);
    }
}
