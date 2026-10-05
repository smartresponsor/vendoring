<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Statement;

use App\Vendoring\Builder\Statement\VendorStatementDeliveryRuntimeProjectionBuilder;
use App\Vendoring\BuilderInterface\Ownership\VendorOwnershipProjectionBuilderInterface;
use App\Vendoring\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\Vendoring\DTO\Statement\VendorStatementRecipientDTO;
use App\Vendoring\DTO\Statement\VendorStatementRequestDTO;
use App\Vendoring\Projection\VendorOwnershipProjection;
use App\Vendoring\ProviderInterface\Statement\VendorStatementRecipientProviderInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementExporterPdfServiceInterface;
use App\Vendoring\ServiceInterface\Statement\VendorStatementServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorStatementDeliveryRuntimeProjectionBuilderTest extends TestCase
{
    private VendorOwnershipProjectionBuilderInterface&MockObject $ownership;
    private VendorStatementServiceInterface&MockObject $statements;
    private VendorStatementExporterPdfServiceInterface&MockObject $exporter;
    private VendorStatementRecipientProviderInterface&MockObject $recipients;

    protected function setUp(): void
    {
        $this->ownership = $this->createMock(VendorOwnershipProjectionBuilderInterface::class);
        $this->statements = $this->createMock(VendorStatementServiceInterface::class);
        $this->exporter = $this->createMock(VendorStatementExporterPdfServiceInterface::class);
        $this->recipients = $this->createMock(VendorStatementRecipientProviderInterface::class);
    }

    public function testBuildIncludesOwnershipExportAndFilteredRecipients(): void
    {
        $pdf = tempnam(sys_get_temp_dir(), 'statement-runtime-');
        self::assertNotFalse($pdf);
        file_put_contents($pdf, 'pdf');

        $statement = [
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
            ->willReturn(new VendorOwnershipProjection(101, 5001, [['userId' => 5002, 'role' => 'manager', 'status' => 'active', 'isPrimary' => false, 'grantedAt' => '2026-03-01T00:00:00+00:00', 'revokedAt' => null, 'capabilities' => []]]));

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
                new VendorStatementRecipientDTO('101', 'keep@example.com', 'USD'),
                new VendorStatementRecipientDTO('999', 'skip-vendor@example.com', 'USD'),
            ]);

        $view = (new VendorStatementDeliveryRuntimeProjectionBuilder(
            $this->ownership,
            $this->statements,
            $this->exporter,
            $this->recipients,
        ))->build(new VendorStatementDeliveryRuntimeRequestDTO('101', '2026-03-01', '2026-03-31', 'USD', true))->toArray();

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
            ['vendorId' => '101', 'email' => 'keep@example.com', 'currency' => 'USD'],
        ], $view['recipients']);

        if (is_file($pdf)) {
            unlink($pdf);
        }
    }

    public function testBuildCanSkipExportAndOwnershipForNonNumericVendorId(): void
    {
        $statement = [
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

        $view = (new VendorStatementDeliveryRuntimeProjectionBuilder(
            $this->ownership,
            $this->statements,
            $this->exporter,
            $this->recipients,
        ))->build(new VendorStatementDeliveryRuntimeRequestDTO('vendor-alpha', '2026-03-01', '2026-03-31', 'EUR', false))->toArray();

        self::assertNull($view['ownership']);
        self::assertNull($view['export']);
        self::assertSame([], $view['recipients']);
        self::assertSame('EUR', $view['currency']);
    }
}
