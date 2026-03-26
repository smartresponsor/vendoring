<?php

declare(strict_types=1);

namespace App\Repository\Payout;

use App\Entity\Vendor\Payout\Payout;
use App\Entity\Vendor\Payout\PayoutItem;
use App\RepositoryInterface\Payout\PayoutRepositoryInterface;
use Doctrine\DBAL\Connection;

final class PayoutRepository implements PayoutRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function insert(Payout $p): void
    {
        $this->db->insert('payouts', [
            'id' => $p->id,
            'vendor_id' => $p->vendorId,
            'currency' => $p->currency,
            'gross_cents' => $p->grossCents,
            'fee_cents' => $p->feeCents,
            'net_cents' => $p->netCents,
            'status' => $p->status,
            'created_at' => $p->createdAt,
            'processed_at' => $p->processedAt,
            'meta' => json_encode($p->meta, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function insertItem(PayoutItem $i): void
    {
        $this->db->insert('payout_items', [
            'id' => $i->id,
            'payout_id' => $i->payoutId,
            'entry_id' => $i->entryId,
            'amount_cents' => $i->amountCents,
        ]);
    }

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
            $this->metaCell($row, 'meta')
        );
    }

    public function items(string $payoutId): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT * FROM payout_items WHERE payout_id=:p', ['p' => $payoutId]);

        return array_map(function (array $row): PayoutItem {
            return new PayoutItem(
                $this->stringCell($row, 'id'),
                $this->stringCell($row, 'payout_id'),
                $this->stringCell($row, 'entry_id'),
                $this->intCell($row, 'amount_cents')
            );
        }, $rows);
    }

    public function markProcessed(string $id, string $processedAt): void
    {
        $this->db->update('payouts', ['status' => 'processed', 'processed_at' => $processedAt], ['id' => $id]);
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
     *
     * @return array<string, mixed>
     */
    private function metaCell(array $row, string $key): array
    {
        $value = $row[$key] ?? '{}';
        if (!is_scalar($value)) {
            return [];
        }
        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
