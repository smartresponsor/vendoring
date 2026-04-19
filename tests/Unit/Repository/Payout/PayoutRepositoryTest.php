<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Repository\Payout;

use App\Vendoring\Repository\Payout\PayoutRepository;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class PayoutRepositoryTest extends TestCase
{
    private Connection&MockObject $connection;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);
    }

    public function testByIdHydratesPayoutAndDecodesMeta(): void
    {
        $this->connection
            ->expects(self::once())
            ->method('fetchAssociative')
            ->with('SELECT * FROM payouts WHERE id=:id', ['id' => 'payout-1'])
            ->willReturn([
                'id' => 'payout-1',
                'vendor_id' => 'vendor-1',
                'currency' => 'USD',
                'gross_cents' => 10000,
                'fee_cents' => 500,
                'net_cents' => 9500,
                'status' => 'processed',
                'created_at' => '2026-03-30 10:00:00',
                'processed_at' => '2026-03-30 11:00:00',
                'meta' => '{"tenantId":"tenant-1","providerRef":"bank_ref_123"}',
            ]);

        $repository = new PayoutRepository($this->connection);
        $payout = $repository->byId('payout-1');

        self::assertNotNull($payout);
        self::assertSame('payout-1', $payout->id);
        self::assertSame('vendor-1', $payout->vendorId);
        self::assertSame('USD', $payout->currency);
        self::assertSame(10000, $payout->grossCents);
        self::assertSame(500, $payout->feeCents);
        self::assertSame(9500, $payout->netCents);
        self::assertSame('processed', $payout->status);
        self::assertSame('2026-03-30 11:00:00', $payout->processedAt);
        self::assertSame('tenant-1', $payout->meta['tenantId']);
        self::assertSame('bank_ref_123', $payout->meta['providerRef']);
    }

    public function testMarkProcessedMergesOutcomeMetadataIntoExistingMeta(): void
    {
        $this->connection
            ->expects(self::once())
            ->method('fetchAssociative')
            ->with('SELECT * FROM payouts WHERE id=:id', ['id' => 'payout-1'])
            ->willReturn([
                'id' => 'payout-1',
                'vendor_id' => 'vendor-1',
                'currency' => 'USD',
                'gross_cents' => 10000,
                'fee_cents' => 500,
                'net_cents' => 9500,
                'status' => 'pending',
                'created_at' => '2026-03-30 10:00:00',
                'processed_at' => null,
                'meta' => '{"tenantId":"tenant-1","provider":"bank"}',
            ]);

        $this->connection
            ->expects(self::once())
            ->method('update')
            ->with(
                'payouts',
                self::callback(function (array $data): bool {
                    self::assertSame('processed', $data['status']);
                    self::assertSame('2026-03-30 11:00:00', $data['processed_at']);
                    $metaJson = $data['meta'] ?? null;
                    self::assertIsString($metaJson);
                    self::assertJson($metaJson);

                    /** @var array<string, mixed> $data */
                    $meta = self::decodeMetaPayload($data);
                    self::assertSame('tenant-1', $meta['tenantId'] ?? null);
                    self::assertSame('bank', $meta['provider'] ?? null);
                    self::assertSame('bank_ref_123', $meta['providerRef'] ?? null);

                    return true;
                }),
                ['id' => 'payout-1'],
            );

        $repository = new PayoutRepository($this->connection);
        $repository->markProcessed('payout-1', '2026-03-30 11:00:00', ['providerRef' => 'bank_ref_123']);
    }

    public function testMarkFailedPersistsProvidedMetadataWhenPayoutIsNotFound(): void
    {
        $this->connection
            ->expects(self::once())
            ->method('fetchAssociative')
            ->with('SELECT * FROM payouts WHERE id=:id', ['id' => 'missing-payout'])
            ->willReturn(false);

        $this->connection
            ->expects(self::once())
            ->method('update')
            ->with(
                'payouts',
                self::callback(function (array $data): bool {
                    self::assertSame('failed', $data['status']);
                    self::assertSame('2026-03-30 11:30:00', $data['processed_at']);
                    $metaJson = $data['meta'] ?? null;
                    self::assertIsString($metaJson);
                    self::assertJson($metaJson);

                    /** @var array<string, mixed> $data */
                    $meta = self::decodeMetaPayload($data);
                    self::assertSame('tenant-1', $meta['tenantId'] ?? null);
                    self::assertSame('provider_declined', $meta['error'] ?? null);

                    return true;
                }),
                ['id' => 'missing-payout'],
            );

        $repository = new PayoutRepository($this->connection);
        $repository->markFailed('missing-payout', '2026-03-30 11:30:00', [
            'tenantId' => 'tenant-1',
            'error' => 'provider_declined',
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function decodeMetaPayload(array $data): array
    {
        self::assertArrayHasKey('meta', $data);
        self::assertIsString($data['meta']);

        $meta = $data['meta'];
        $decoded = json_decode($meta, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            self::fail('Decoded meta payload must be an array.');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
