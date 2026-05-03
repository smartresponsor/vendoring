<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Security;

use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\Service\Security\VendorAccessResolverService;
use App\Vendoring\Service\Security\VendorAuthorizationMatrixService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorAccessResolverTest extends TestCase
{
    private VendorUserAssignmentRepositoryInterface&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(VendorUserAssignmentRepositoryInterface::class);
    }

    public function testCapabilityGrantedWhenAnyActiveRoleMatches(): void
    {
        $this->repository
            ->method('findActiveByVendorId')
            ->with(42)
            ->willReturn([
                new VendorUserAssignmentEntity(42, 7, 'viewer'),
                new VendorUserAssignmentEntity(42, 7, 'finance'),
            ]);

        $resolver = new VendorAccessResolverService($this->repository, new VendorAuthorizationMatrixService());

        self::assertTrue($resolver->canUserAccessVendorCapability(42, 7, 'payouts.write'));
        self::assertSame('role_grants_capability', $resolver->explainUserAccessVendorCapability(42, 7, 'payouts.write')['reason']);
    }

    public function testCapabilityDeniedWithoutAssignments(): void
    {
        $this->repository->method('findActiveByVendorId')->with(42)->willReturn([]);

        $resolver = new VendorAccessResolverService($this->repository, new VendorAuthorizationMatrixService());
        $explanation = $resolver->explainUserAccessVendorCapability(42, 7, 'ownership.read');

        self::assertFalse($explanation['granted']);
        self::assertSame('no_active_assignment', $explanation['reason']);
        self::assertSame([], $explanation['roles']);
    }

    public function testCapabilityDeniedWhenRolesDoNotGrantPermission(): void
    {
        $this->repository
            ->method('findActiveByVendorId')
            ->with(42)
            ->willReturn([
                new VendorUserAssignmentEntity(42, 7, 'viewer'),
            ]);

        $resolver = new VendorAccessResolverService($this->repository, new VendorAuthorizationMatrixService());
        $explanation = $resolver->explainUserAccessVendorCapability(42, 7, 'ownership.write');

        self::assertFalse($explanation['granted']);
        self::assertSame('capability_not_granted', $explanation['reason']);
        self::assertSame(['viewer'], $explanation['roles']);
    }
}
