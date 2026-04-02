<?php

declare(strict_types=1);

namespace App\Tests\Unit\Command;

use App\Command\VendorRuntimeStatusCommand;
use App\Projection\VendorRuntimeStatusView;
use App\ServiceInterface\Ops\VendorRuntimeStatusViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class VendorRuntimeStatusCommandTest extends TestCase
{
    private VendorRuntimeStatusViewBuilderInterface&MockObject $runtimeStatusViewBuilder;

    protected function setUp(): void
    {
        $this->runtimeStatusViewBuilder = $this->createMock(VendorRuntimeStatusViewBuilderInterface::class);
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
                profile: [
                    'completionPercent' => 88,
                    'readyForPublishing' => false,
                    'nextAction' => 'add_website',
                ],
                finance: ['metricOverview' => []],
                statementDelivery: ['statement' => []],
                externalIntegration: ['surfaces' => []],
                surfaceStatus: [
                    'ownership' => true,
                    'profile' => true,
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
        self::assertStringContainsString('profile=ready', $tester->getDisplay());
        self::assertStringContainsString('profileCompletion=88', $tester->getDisplay());
        self::assertStringContainsString('profilePublishing=incomplete', $tester->getDisplay());
        self::assertStringContainsString('profileNextAction=add_website', $tester->getDisplay());
    }
}
