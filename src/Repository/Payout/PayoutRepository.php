<?php

declare(strict_types=1);

namespace App\Repository\Payout;

use App\Entity\Payout\Payout;
use App\Entity\Payout\PayoutItem;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use JsonException;

final readonly class PayoutRepository implements PayoutRepositoryInterface
{
    public function __construct(private Connection $db) {}

    /** @throws Exception|JsonException */
    public function insert(Payout $payout): void
    {
        $this->db->insert('payouts', [
            'id' => $payout->id,
            'vendor_id' => $payout->vendorId,
            'currency' => $payout->currency,
            'gross_cents' => $payout->grossCents,
            'fee_cents' => $payout->feeCents,
            'net_cents' => $payout->netCents,
            'status' => $payout->status,
            'created_at' => $payout->createdAt,
            'processed_at' => $payout->processedAt,
            'meta' => json_encode($payout->meta, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }

    /** @throws Exception */
    public function insertItem(PayoutItem $item): void
    {
        $this->db->insert('payout_items', [
            'id' => $item->id,
            'payout_id' => $item->payoutId,
            'entry_id' => $item->entryId,
            'amount_cents' => $item->amountCents,
        ]);
    }

    /** @throws Exception */
    public function byId(string $id): ?Payout
    {
        $row = $this->db->fetchAssociative('SELECT * FROM payouts WHERE id=:id', ['id' => $id]);
        if (!$row) {
            return null;
        }

        return new Payout(
            $this->stringCell($row, 'id'),
            $this->stringCell($row, 'vendor_id'),
            $this->stringCell($row, 'currency'),
            $this->intCell($row, 'gross_cents'),
            $this->intCell($row, 'fee_cents'),
            $this->intCell($row, 'net_cents'),
            $this->stringCell($row, 'status'),
            $this->stringCell($row, 'created_at'),
            '' !== $this->stringCell($row, 'processed_at') ? $this->stringCell($row, 'processed_at') : null,
            $this->decodedMetaCell($row),
        );
    }

    /**
     * @return list<PayoutItem>
     * @throws Exception
     */
    public function items(string $payoutId): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT * FROM payout_items WHERE payout_id=:p', ['p' => $payoutId]);

        return array_map(function (array $row): PayoutItem {
            return new PayoutItem(
                $this->stringCell($row, 'id'),
                $this->stringCell($row, 'payout_id'),
                $this->stringCell($row, 'entry_id'),
                $this->intCell($row, 'amount_cents'),
            );
        }, $rows);
    }

    /**
     * @param array<string, mixed> $meta
     * @throws Exception
     * @throws JsonException
     */
    public function markProcessed(string $id, string $processedAt, array $meta = []): void
    {
        $this->db->update('payouts', [
            'status' => 'processed',
            'processed_at' => $processedAt,
            'meta' => $this->encodeMergedMeta($id, $meta),
        ], ['id' => $id]);
    }

    /**
     * @param array<string, mixed> $meta
     * @throws Exception
     * @throws JsonException
     */
    public function markFailed(string $id, string $processedAt, array $meta = []): void
    {
        $this->db->update('payouts', [
            'status' => 'failed',
            'processed_at' => $processedAt,
            'meta' => $this->encodeMergedMeta($id, $meta),
        ], ['id' => $id]);
    }

    /** @param array<string, mixed> $row */
    private function stringCell(array $row, string $key): string
    {
        $value = $row[$key] ?? '';

        return is_scalar($value) ? (string) $value : '';
    }

    /** @param array<string, mixed> $row */
    private function intCell(array $row, string $key): int
    {
        $value = $row[$key] ?? 0;

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function decodedMetaCell(array $row): array
    {
        $value = $row['meta'] ?? '{}';
        if (!is_scalar($value)) {
            return [];
        }
        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param string $id
     * @param array<string, mixed> $meta
     * @return string
     * @throws Exception
     * @throws JsonException
     */
    private function encodeMergedMeta(string $id, array $meta): string
    {
        $existing = $this->byId($id)?->meta ?? [];

        return json_encode([...$existing, ...$meta], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
