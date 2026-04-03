<?php

declare(strict_types=1);

namespace App\Tests\Unit\Security;

use App\Entity\VendorUserAssignment;
use App\RepositoryInterface\VendorUserAssignmentRepositoryInterface;
use App\Service\VendorUserAssignmentService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorUserAssignmentServiceRoleNormalizationTest extends TestCase
{
    private VendorUserAssignmentRepositoryInterface&MockObject $repository;
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendorUserAssignmentRepositoryInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    public function testAssignRoleNormalizesCanonicalRole(): void
    {
        $captured = null;
        $this->repository->method('findOneByVendorIdAndUserId')->with(42, 7)->willReturn(null);
        $this->repository
            ->expects(self::once())
            ->method('save')
            ->with(self::callback(function ($assignment) use (&$captured): bool {
                $captured = $assignment;

                return $assignment instanceof VendorUserAssignment;
            }), true);

        $service = new VendorUserAssignmentService($this->repository, $this->entityManager);
        $assignment = $service->assignRole(42, 7, 'FINANCE');

        self::assertSame('finance', $assignment->getRole());
        self::assertInstanceOf(VendorUserAssignment::class, $captured);
        self::assertSame('finance', $captured->getRole());
    }

    public function testAssignRoleRejectsUnknownRole(): void
    {
        $service = new VendorUserAssignmentService($this->repository, $this->entityManager);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported vendor role');

        $service->assignRole(42, 7, 'superadmin');
    }
}
