<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Command;

use App\Vendoring\BuilderInterface\Ops\VendorRuntimeStatusProjectionBuilderInterface;
use App\Vendoring\Command\VendorRuntimeStatusCommand;
use App\Vendoring\Entity\Vendor\VendorEntity;
use App\Vendoring\Entity\Vendor\VendorLedgerBindingEntity;
use App\Vendoring\Projection\VendorRuntimeStatusProjection;
use App\Vendoring\RepositoryInterface\VendorLedgerBindingRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class VendorRuntimeStatusCommandTest extends TestCase
{
    private VendorRuntimeStatusProjectionBuilderInterface&MockObject $runtimeStatusProjectionBuilder;

    protected function setUp(): void
    {
        $this->runtimeStatusProjectionBuilder = $this->createMock(VendorRuntimeStatusProjectionBuilderInterface::class);
    }

    public function testExecutePrintsProfileReadinessSummaryInTextMode(): void
    {
        $this->runtimeStatusProjectionBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD', 7)
            ->willReturn(new VendorRuntimeStatusProjection(
                tenantId: 'tenant-1',
                vendorId: '42',
                currency: 'USD',
                ownership: ['ownerUserId' => 7],
                finance: ['metricOverview' => []],
                statementDelivery: ['statement' => []],
                externalIntegration: ['surfaces' => []],
                surfaceStatus: [
                    'ownership' => true,
                    'finance' => true,
                    'statementDelivery' => true,
                    'externalIntegration' => true,
                ],
                generatedAt: '2025-01-31T00:00:00+00:00',
            ));

        $vendor = new VendorEntity('Fixture');
        $id = new \ReflectionProperty($vendor, 'id');
        $id->setValue($vendor, 7);
        $bindings = $this->createMock(VendorLedgerBindingRepositoryInterface::class);
        $bindings->expects(self::once())->method('findOneBy')->with(['tenantId' => 'tenant-1', 'ledgerVendorId' => '42'])->willReturn(new VendorLedgerBindingEntity($vendor, 'tenant-1', '42'));
        $tester = new CommandTester(new VendorRuntimeStatusCommand($this->runtimeStatusProjectionBuilder, $bindings));
        $statusCode = $tester->execute([
            '--tenantId' => 'tenant-1',
            '--vendorId' => '42',
            '--from' => '2025-01-01',
            '--to' => '2025-01-31',
            '--currency' => 'USD',
        ]);

        self::assertSame(Command::SUCCESS, $statusCode);
        self::assertStringContainsString('tenantId=tenant-1 vendorId=42 currency=USD', $tester->getDisplay());
        self::assertStringContainsString('ownership=ready finance=ready statementDelivery=ready externalIntegration=ready', $tester->getDisplay());
    }
}
