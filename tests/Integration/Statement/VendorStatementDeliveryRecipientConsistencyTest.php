<?php

declare(strict_types=1);

namespace App\Tests\Integration\Statement;

use App\DTO\Statement\VendorStatementDeliveryRuntimeRequestDTO;
use App\DTO\Statement\VendorStatementRecipientDTO;
use App\Projection\VendorOwnershipView;
use App\Service\Statement\VendorStatementDeliveryRuntimeViewBuilder;
use App\ServiceInterface\Statement\StatementExporterPDFInterface;
use App\ServiceInterface\Statement\VendorStatementRecipientProviderInterface;
use App\ServiceInterface\Statement\VendorStatementServiceInterface;
use App\ServiceInterface\VendorOwnershipViewBuilderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class VendorStatementDeliveryRecipientConsistencyTest extends TestCase
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

    public function testBuildReturnsEmptyRecipientsWhenProviderYieldsNoMatches(): void
    {
        $statement = [
            'tenantId' => 'tenant-1',
            'vendorId' => '101',
            'from' => '2026-03-01',
            'to' => '2026-03-31',
            'currency' => 'USD',
            'opening' => 0.0,
            'earnings' => 0.0,
            'refunds' => 0.0,
            'fees' => 0.0,
            'closing' => 0.0,
            'items' => [],
        ];

        $this->ownership->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorOwnershipView(101, 5001, []));
        $this->statements->expects(self::once())->method('build')->willReturn($statement);
        $this->exporter->expects(self::never())->method('export');
        $this->recipients->expects(self::once())->method('forPeriod')->with('2026-03-01', '2026-03-31')
            ->willReturn([
                new VendorStatementRecipientDTO('tenant-2', '101', 'skip-tenant@example.com', 'USD'),
                new VendorStatementRecipientDTO('tenant-1', '999', 'skip-vendor@example.com', 'USD'),
            ]);

        $payload = (new VendorStatementDeliveryRuntimeViewBuilder(
            $this->ownership,
            $this->statements,
            $this->exporter,
            $this->recipients,
        ))->build(new VendorStatementDeliveryRuntimeRequestDTO('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD', false))->toArray();

        self::assertSame([], $payload['recipients']);
        self::assertNull($payload['export']);
        self::assertSame($statement, $payload['statement']);
    }

    public function testBuildKeepsMatchedBillingRecipient(): void
    {
        $pdf = tempnam(sys_get_temp_dir(), 'statement-delivery-');
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

        $this->ownership->expects(self::once())->method('buildForVendorId')->with(101)
            ->willReturn(new VendorOwnershipView(101, 5001, []));
        $this->statements->expects(self::once())->method('build')->willReturn($statement);
        $this->exporter->expects(self::once())->method('export')->willReturn($pdf);
        $this->recipients->expects(self::once())->method('forPeriod')->with('2026-03-01', '2026-03-31')
            ->willReturn([
                new VendorStatementRecipientDTO('tenant-1', '101', 'billing@example.com', 'USD'),
                new VendorStatementRecipientDTO('tenant-1', '999', 'skip@example.com', 'USD'),
            ]);

        $payload = (new VendorStatementDeliveryRuntimeViewBuilder(
            $this->ownership,
            $this->statements,
            $this->exporter,
            $this->recipients,
        ))->build(new VendorStatementDeliveryRuntimeRequestDTO('tenant-1', '101', '2026-03-01', '2026-03-31', 'USD', true))->toArray();

        self::assertSame([
            ['tenantId' => 'tenant-1', 'vendorId' => '101', 'email' => 'billing@example.com', 'currency' => 'USD'],
        ], $payload['recipients']);
        self::assertIsArray($payload['export']);
        self::assertSame($pdf, $payload['export']['path']);
        self::assertTrue($payload['export']['exists']);

        if (is_file($pdf)) {
            unlink($pdf);
        }
    }
}
