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
            (string) $row['id'],
            (string) $row['vendor_id'],
            (string) $row['currency'],
            (int) $row['gross_cents'],
            (int) $row['fee_cents'],
            (int) $row['net_cents'],
            (string) $row['status'],
            (string) $row['created_at'],
            $row['processed_at'] ? (string) $row['processed_at'] : null,
            json_decode((string) $row['meta'], true) ?: []
        );
    }

    public function items(string $payoutId): array
    {
        $rows = $this->db->fetchAllAssociative('SELECT * FROM payout_items WHERE payout_id=:p', ['p' => $payoutId]);

        return array_map(fn ($r) => new PayoutItem((string) $r['id'], (string) $r['payout_id'], (string) $r['entry_id'], (int) $r['amount_cents']), $rows);
    }

    public function markProcessed(string $id, string $processedAt): void
    {
        $this->db->update('payouts', ['status' => 'processed', 'processed_at' => $processedAt], ['id' => $id]);
    }
}
