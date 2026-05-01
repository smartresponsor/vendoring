<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Command;

use App\Vendoring\Command\VendorRuntimeStatusCommand;
use App\Vendoring\Projection\Vendor\VendorRuntimeStatusView;
use App\Vendoring\ServiceInterface\Ops\VendorRuntimeStatusViewBuilderServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class VendorRuntimeStatusCommandTest extends TestCase
{
    private VendorRuntimeStatusViewBuilderServiceInterface&MockObject $runtimeStatusViewBuilder;

    protected function setUp(): void
    {
        $this->runtimeStatusViewBuilder = $this->createMock(VendorRuntimeStatusViewBuilderServiceInterface::class);
    }

    public function testExecutePrintsProfileReadinessSummaryInTextMode(): void
    {
        $this->runtimeStatusViewBuilder
            ->expects(self::once())
            ->method('build')
            ->with('tenant-1', '42', '2025-01-01', '2025-01-31', 'USD')
            ->willReturn(new VendorRuntimeStatusView(
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

        $tester = new CommandTester(new VendorRuntimeStatusCommand($this->runtimeStatusViewBuilder));
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
