<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit;

use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorUserAssignmentEntity;
use App\Vendoring\RepositoryInterface\Vendor\VendorRepositoryInterface;
use App\Vendoring\RepositoryInterface\Vendor\VendorUserAssignmentRepositoryInterface;
use App\Vendoring\Service\Security\VendorAuthorizationMatrixService;
use App\Vendoring\Service\Ownership\VendorOwnershipProjectionBuilderService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorOwnershipProjectionBuilderTest extends TestCase
{
    private VendorRepositoryInterface&MockObject $vendorRepository;
    private VendorUserAssignmentRepositoryInterface&MockObject $assignmentRepository;

    protected function setUp(): void
    {
        $this->vendorRepository = $this->createMock(VendorRepositoryInterface::class);
        $this->assignmentRepository = $this->createMock(VendorUserAssignmentRepositoryInterface::class);
    }

    public function testBuildForVendorIdIncludesCapabilitiesPerAssignment(): void
    {
        $vendor = new VendorEntity(brandName: 'Acme', ownerUserId: 7);

        $this->vendorRepository->method('find')->with(42)->willReturn($vendor);
        $this->assignmentRepository->method('findActiveByVendorId')->with(42)->willReturn([
            new VendorUserAssignmentEntity(42, 7, 'owner', isPrimary: true),
            new VendorUserAssignmentEntity(42, 9, 'viewer'),
        ]);

        $builder = new VendorOwnershipProjectionBuilderService(
            $this->vendorRepository,
            $this->assignmentRepository,
            new VendorAuthorizationMatrixService(),
        );

        $payload = $builder->buildForVendorId(42)?->toArray();

        self::assertNotNull($payload);
        self::assertSame(['transactions.read', 'transactions.write', 'payouts.read', 'payouts.write', 'statements.read', 'statements.send', 'ownership.read', 'ownership.write'], $payload['assignments'][0]['capabilities']);
        self::assertSame(['transactions.read', 'payouts.read', 'statements.read', 'ownership.read'], $payload['assignments'][1]['capabilities']);
    }
}
