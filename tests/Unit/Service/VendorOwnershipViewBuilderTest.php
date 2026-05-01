<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Service;

use App\Vendoring\Entity\Vendor;
use App\Vendoring\Entity\VendorUserAssignment;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\Service\Ownership\VendorOwnershipViewBuilderService;
use App\Vendoring\ServiceInterface\Security\VendorAuthorizationMatrixServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorOwnershipViewBuilderTest extends TestCase
{
    private VendorRepositoryInterface&MockObject $vendors;
    private VendorUserAssignmentRepositoryInterface&MockObject $assignments;
    private VendorAuthorizationMatrixServiceInterface&MockObject $authorizationMatrix;

    protected function setUp(): void
    {
        $this->vendors = $this->createMock(VendorRepositoryInterface::class);
        $this->assignments = $this->createMock(VendorUserAssignmentRepositoryInterface::class);
        $this->authorizationMatrix = $this->createMock(VendorAuthorizationMatrixServiceInterface::class);
    }

    public function testBuildForVendorIdReturnsNullWhenVendorDoesNotExist(): void
    {
        $this->vendors->expects(self::once())->method('find')->with(404)->willReturn(null);
        $this->assignments->expects(self::never())->method('findActiveByVendorId');
        $this->authorizationMatrix->expects(self::never())->method('capabilitiesForRole');

        $view = (new VendorOwnershipViewBuilderService($this->vendors, $this->assignments, $this->authorizationMatrix))->buildForVendorId(404);

        self::assertNull($view);
    }

    public function testBuildForVendorIdBuildsOwnershipProjectionFromActiveAssignments(): void
    {
        $vendor = new Vendor('Vendor Example', 5001);
        $assignmentA = new VendorUserAssignment(
            vendorId: 101,
            userId: 5002,
            role: 'manager',
            status: 'active',
            isPrimary: true,
            grantedAt: new \DateTimeImmutable('2026-03-01T10:00:00+00:00'),
            revokedAt: null,
        );
        $assignmentB = new VendorUserAssignment(
            vendorId: 101,
            userId: 5003,
            role: 'viewer',
            status: 'active',
            isPrimary: false,
            grantedAt: new \DateTimeImmutable('2026-03-02T11:00:00+00:00'),
            revokedAt: new \DateTimeImmutable('2026-03-10T12:00:00+00:00'),
        );

        $this->vendors->expects(self::once())->method('find')->with(101)->willReturn($vendor);
        $this->assignments->expects(self::once())->method('findActiveByVendorId')->with(101)->willReturn([$assignmentA, $assignmentB]);
        $this->authorizationMatrix
            ->expects(self::exactly(2))
            ->method('capabilitiesForRole')
            ->willReturnMap([
                ['manager', ['transactions.write', 'billing.read']],
                ['viewer', ['billing.read']],
            ]);

        $view = (new VendorOwnershipViewBuilderService($this->vendors, $this->assignments, $this->authorizationMatrix))->buildForVendorId(101);
        self::assertNotNull($view);

        $payload = $view->toArray();
        self::assertSame(101, $payload['vendorId']);
        self::assertSame(5001, $payload['ownerUserId']);
        self::assertCount(2, $payload['assignments']);
        self::assertSame(5002, $payload['assignments'][0]['userId']);
        self::assertSame('manager', $payload['assignments'][0]['role']);
        self::assertSame(['transactions.write', 'billing.read'], $payload['assignments'][0]['capabilities']);
        self::assertTrue($payload['assignments'][0]['isPrimary']);
        self::assertSame('2026-03-01T10:00:00+00:00', $payload['assignments'][0]['grantedAt']);
        self::assertNull($payload['assignments'][0]['revokedAt']);
        self::assertSame(['billing.read'], $payload['assignments'][1]['capabilities']);
        self::assertSame('2026-03-10T12:00:00+00:00', $payload['assignments'][1]['revokedAt']);
    }
}
