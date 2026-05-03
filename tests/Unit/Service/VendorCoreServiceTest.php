<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\DTO\VendorCreateDTO;
use App\Vendoring\DTO\VendorUpdateDTO;
use App\Vendoring\Entity\Vendor;
use App\Vendoring\Service\Core\VendorCoreService;
use App\Vendoring\ServiceInterface\Assignment\VendorUserAssignmentServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class VendorCoreServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private EventDispatcherInterface&MockObject $dispatcher;
    private VendorUserAssignmentServiceInterface&MockObject $assignmentService;
    private ValidatorInterface&MockObject $validator;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->assignmentService = $this->createMock(VendorUserAssignmentServiceInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
    }

    public function testCreateTrimsBrandNameBeforeEntityConstruction(): void
    {
        $dto = new VendorCreateDTO('  Smartresponsor  ');

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects(self::once())->method('persist');
        $this->entityManager->expects(self::once())->method('flush');
        $this->assignmentService->expects(self::never())->method('assignOwner');
        $this->dispatcher->expects(self::once())->method('dispatch');

        $service = $this->buildService();

        $vendor = $service->create($dto);

        self::assertSame('Smartresponsor', $vendor->getBrandName());
    }

    public function testCreateThrowsDomainValidationExceptionWhenDtoInvalid(): void
    {
        $dto = new VendorCreateDTO('');

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList([
                new ConstraintViolation('brand_name_required', '', [], null, 'brandName', ''),
            ]));

        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');

        $service = $this->buildService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('brand_name_required');

        $service->create($dto);
    }

    public function testUpdateTrimsBrandNameWhenProvided(): void
    {
        $vendor = new Vendor('Old');
        $dto = new VendorUpdateDTO(brandName: '  New Name  ');

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects(self::once())->method('flush');
        $this->dispatcher->expects(self::never())->method('dispatch');
        $this->assignmentService->expects(self::never())->method('assignOwner');

        $service = $this->buildService();

        $updated = $service->update($vendor, $dto);

        self::assertSame('New Name', $updated->getBrandName());
    }

    public function testUpdateRejectsWhitespaceOnlyBrandNameAfterNormalization(): void
    {
        $vendor = new Vendor('Old');
        $dto = new VendorUpdateDTO(brandName: '   ');

        $this->validator
            ->expects(self::once())
            ->method('validate')
            ->with($dto)
            ->willReturn(new ConstraintViolationList());

        $this->entityManager->expects(self::never())->method('flush');

        $service = $this->buildService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('brand_name_required');

        $service->update($vendor, $dto);
    }

    private function buildService(): VendorCoreService
    {
        return new VendorCoreService(
            $this->entityManager,
            $this->dispatcher,
            $this->assignmentService,
            $this->validator,
        );
    }
}
