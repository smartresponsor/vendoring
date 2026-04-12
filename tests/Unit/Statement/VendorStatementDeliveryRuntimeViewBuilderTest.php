<?php

declare(strict_types=1);

namespace App\Tests\Unit\Statement;

use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\DTO\Statement\VendorStatementRecipientDTO;
use App\DTO\Statement\VendorStatementRequestDTO;
use App\Projection\VendorOwnershipView;
use App\Service\Statement\VendorStatementDeliveryRuntimeViewBuilder;
use App\ServiceInterface\Statement\StatementExporterPDFInterface;
use App\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorStatementDeliveryRuntimeViewBuilderTest extends TestCase
{
    private VendorOwnershipViewBuilderInterface&MockObject $ownership;
    private VendorStatementServiceInterface&MockObject $statements;
    private StatementExporterPDFInterface&MockObject $exporter;
    private VendorStatementRecipientProviderInterface&MockObject $recipients;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipViewBuilderInterface::class);
        $this->statements = $this->createMock(VendorStatementServiceInterface::class);
        $this->exporter = $this->createMock(StatementExporterPDFInterface::class);
        $this->recipients = $this->createMock(VendorStatementRecipientProviderInterface::class);
    }

    public function testBuildIncludesOwnershipExportAndFilteredRecipients(): void
    {
        $pdf = tempnam(sys_get_temp_dir(), 'statement-runtime-');
        self::assertNotFalse($pdf);
        file_put_contents($pdf, 'pdf');

        $statement = [
            'tenantId' => 'tenant-1',
            'vendorId' => '101',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'opening' => 10.0,
            'earnings' => 20.0,
            'refunds' => 5.0,
            'fees' => 2.0,
            'closing' => 23.0,
            'items' => [],
        ];

        $this->statements
            ->expects(self::once())
            ->method('build')
            ->with(self::callback(function (VendorStatementRequestDTO $dto): bool {
                self::assertSame('tenant-1', $dto->tenantId);
                self::assertSame('101', $dto->vendorId);
                self::assertSame('2026-03-01', $dto->from);
                self::assertSame('2026-03-31', $dto->to);
                self::assertSame('USD', $dto->currency);

                return true;
            }))
            ->willReturn($statement);

        $this->ownership
            ->expects(self::once())
            ->method('buildForVendorId')
            ->with(101)
            ->willReturn(new VendorOwnershipView(101, 5001, [['userId' => 5002, 'role' => 'manager', 'status' => 'active', 'isPrimary' => false, 'grantedAt' => '2026-03-01T00:00:00+00:00', 'revokedAt' => null, 'capabilities' => []]]));

        $this->exporter
            ->expects(self::once())
            ->method('export')
            ->with(self::isInstanceOf(VendorStatementRequestDTO::class), $statement, null)
            ->willReturn($pdf);

        $this->recipients
            ->expects(self::once())
            ->method('forPeriod')
            ->with('2026-03-01', '2026-03-31')
            ->willReturn([
                new VendorStatementRecipientDTO('tenant-1', '101', 'keep@example.com', 'USD'),
                new VendorStatementRecipientDTO('tenant-1', '999', 'skip-vendor@example.com', 'USD'),
                new VendorStatementRecipientDTO('tenant-2', '101', 'skip-tenant@example.com', 'USD'),
            ]);

        $view = (new VendorStatementDeliveryRuntimeViewBuilder(
            $this->ownership,
            $this->statements,
            $this->exporter,
            $this->recipients,
        ))->build(new VendorStatementDeliveryRuntimeRequestDTO('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD', true))->toArray();

        self::assertSame('tenant-1', $view['tenantId']);
        self::assertSame('101', $view['vendorId']);
        self::assertSame('USD', $view['currency']);
        self::assertIsArray($view['ownership']);
        self::assertSame(5001, $view['ownership']['ownerUserId']);
        self::assertSame($statement, $view['statement']);
        self::assertIsArray($view['export']);
        self::assertSame($pdf, $view['export']['path']);
        self::assertTrue($view['export']['exists']);
        self::assertTrue($view['export']['readable']);
        self::assertSame([
            ['tenantId' => 'tenant-1', 'vendorId' => '101', 'email' => 'keep@example.com', 'currency' => 'USD'],
        ], $view['recipients']);

        if (is_file($pdf)) {
            unlink($pdf);
        }
    }

    public function testBuildCanSkipExportAndOwnershipForNonNumericVendorId(): void
    {
        $statement = [
            'tenantId' => 'tenant-1',
            'vendorId' => 'vendor-alpha',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'EUR',
            'opening' => 0.0,
            'earnings' => 0.0,
            'refunds' => 0.0,
            'fees' => 0.0,
            'closing' => 0.0,
            'items' => [],
        ];

        $this->statements->expects(self::once())->method('build')->willReturn($statement);
        $this->ownership->expects(self::never())->method('buildForVendorId');
        $this->exporter->expects(self::never())->method('export');
        $this->recipients->expects(self::once())->method('forPeriod')->willReturn([]);

        $view = (new VendorStatementDeliveryRuntimeViewBuilder(
            $this->ownership,
            $this->statements,
            $this->exporter,
            $this->recipients,
        ))->build(new VendorStatementDeliveryRuntimeRequestDTO('tenant-1', 'vendor-alpha', '2026-03-01', '2026-03-31', 'EUR', false))->toArray();

        self::assertNull($view['ownership']);
        self::assertNull($view['export']);
        self::assertSame([], $view['recipients']);
        self::assertSame('EUR', $view['currency']);
    }
}
