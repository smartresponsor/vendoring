<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915004200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Qualify Vendoring business lifecycle columns that collide with canonical Objecting system fields.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Vendoring normalization supports PostgreSQL only.');

        foreach ([
            'vendor_payout' => ['status' => 'payout_status'],
            'vendor_payout_account' => ['active' => 'account_active'],
            'vendor_commission' => ['status' => 'commission_status'],
            'vendor_conversation' => ['status' => 'conversation_status'],
            'vendor_customer_order' => ['status' => 'customer_order_status'],
            'vendor_group' => ['status' => 'group_status'],
            'vendor_payment' => ['status' => 'payment_status'],
            'vendor_shipment' => ['status' => 'shipment_status'],
            'vendor_user_assignment' => ['status' => 'assignment_status'],
            'vendor_wishlist' => ['status' => 'wishlist_status'],
            'vendor_transaction' => [
                'status' => 'transaction_status',
                'created_at' => 'transaction_created_at',
            ],
        ] as $table => $renames) {
            foreach ($renames as $from => $to) {
                $this->renameRequiredWhenTableExists($schema, $table, $from, $to);
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Vendoring business/system column qualification is a forward-only schema transition.');
    }

    private function renameRequiredWhenTableExists(Schema $schema, string $tableName, string $from, string $to): void
    {
        if (!$schema->hasTable($tableName)) {
            return;
        }

        $table = $schema->getTable($tableName);
        if ($table->hasColumn($to)) {
            $this->abortIf($table->hasColumn($from), sprintf('Both %s and %s exist on %s; refusing ambiguous normalization.', $from, $to, $tableName));

            return;
        }

        $this->abortIf(!$table->hasColumn($from), sprintf('Required business column %s is missing from %s.', $from, $tableName));
        $table->renameColumn($from, $to);
    }
}
