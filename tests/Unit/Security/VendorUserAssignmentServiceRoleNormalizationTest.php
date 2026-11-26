<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Security;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\RepositoryInterface\VendorRepositoryInterface;
use App\Vendoring\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\Service\Assignment\VendorUserAssignmentService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorUserAssignmentServiceRoleNormalizationTest extends TestCase
{
    private VendorUserAssignmentRepositoryInterface&MockObject $repository;

    private VendorRepositoryInterface&MockObject $vendors;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendorUserAssignmentRepositoryInterface::class);
        $this->vendors = $this->createMock(VendorRepositoryInterface::class);
    }

    public function testAssignRoleNormalizesCanonicalRole(): void
    {
        $vendor = new VendorEntity('Fixture');
        (new \ReflectionProperty($vendor, 'id'))->setValue($vendor, 42);
        $this->vendors->expects(self::once())->method('find')->with(42)->willReturn($vendor);
        $captured = null;
        $this->repository->method('findOneByVendorIdAndUserId')->with(42, 7)->willReturn(null);
        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(function ($assignment) use (&$captured): bool {
                $captured = $assignment;

                return $assignment instanceof VendorUserAssignmentEntity;
            }), true);

        $service = new VendorUserAssignmentService($this->repository, $this->vendors);
        $assignment = $service->assignRole(42, 7, 'FINANCE');

        self::assertSame('finance', $assignment->getRole());
        self::assertSame(42, $assignment->getVendorId());
        self::assertInstanceOf(VendorUserAssignmentEntity::class, $captured);
        self::assertSame('finance', $captured->getRole());
    }

    public function testAssignRoleRejectsUnresolvedVendorBeforeAnyAssignmentWrite(): void
    {
        $this->vendors->expects(self::once())->method('find')->with(42)->willReturn(null);
        $this->repository->expects(self::never())->method('save');
        $this->repository->expects(self::never())->method('flush');
        $this->expectException(\InvalidArgumentException::class);
        (new VendorUserAssignmentService($this->repository, $this->vendors))->assignOwner(42, 7);
    }

    public function testAssignRoleRejectsUnknownRole(): void
    {
        $service = new VendorUserAssignmentService($this->repository, $this->vendors);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported vendor role');

        $service->assignRole(42, 7, 'superadmin');
    }
}
