<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\DTO\VendorCreateDTO;
use App\Vendoring\DTO\VendorUpdateDTO;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\Service\Crud\VendorCrudService;
use App\Vendoring\ServiceInterface\Assignment\VendorUserAssignmentServiceInterface;
use PHPUnit\Framework\TestCase;
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
