<?php

declare(strict_types=1);

namespace App\Vendoring\Migrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261126042200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align physical indexes with Doctrine ORM 3.7 class attributes without dropping business constraints.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform, 'Vendoring index normalization requires PostgreSQL.');

        foreach ([
            'uniq_133957ee4c6a6cb5' => 'uniq_vendor_address_uuid',
            'uniq_133957ee588a771' => 'uniq_vendor_address_slug',
            'uniq_20a537094c6a6cb5' => 'uniq_vendor_analytics_uuid',
            'uniq_20a53709588a771' => 'uniq_vendor_analytics_slug',
            'uniq_d765d5f24c6a6cb5' => 'uniq_vendor_api_key_uuid',
            'uniq_d765d5f2588a771' => 'uniq_vendor_api_key_slug',
            'uniq_e32a0ddc4c6a6cb5' => 'uniq_vendor_attachment_uuid',
            'uniq_e32a0ddc588a771' => 'uniq_vendor_attachment_slug',
            'uniq_f25574c54c6a6cb5' => 'uniq_vendor_billing_uuid',
            'uniq_f25574c5588a771' => 'uniq_vendor_billing_slug',
            'uniq_6b914f374c6a6cb5' => 'uniq_vendor_catalog_category_banner_uuid',
            'uniq_6b914f37588a771' => 'uniq_vendor_catalog_category_banner_slug',
            'uniq_ee1639c64c6a6cb5' => 'uniq_vendor_catalog_category_change_request_uuid',
            'uniq_ee1639c6588a771' => 'uniq_vendor_catalog_category_change_request_slug',
            'uniq_eeba0144c6a6cb5' => 'uniq_vendor_catalog_category_html_block_uuid',
            'uniq_eeba014588a771' => 'uniq_vendor_catalog_category_html_block_slug',
            'uniq_74e003dd4c6a6cb5' => 'uniq_vendor_catalog_category_pin_uuid',
            'uniq_74e003dd588a771' => 'uniq_vendor_catalog_category_pin_slug',
            'uniq_52cea7604c6a6cb5' => 'uniq_vendor_catalog_review_assignment_uuid',
            'uniq_52cea760588a771' => 'uniq_vendor_catalog_review_assignment_slug',
            'uniq_db5f12304c6a6cb5' => 'uniq_vendor_category_uuid',
            'uniq_db5f1230588a771' => 'uniq_vendor_category_slug',
            'uniq_bc8eb6284c6a6cb5' => 'uniq_vendor_channel_uuid',
            'uniq_bc8eb628588a771' => 'uniq_vendor_channel_slug',
            'uniq_c7df2e704c6a6cb5' => 'uniq_vendor_code_storage_uuid',
            'uniq_c7df2e70588a771' => 'uniq_vendor_code_storage_slug',
            'uniq_8610d53f4c6a6cb5' => 'uniq_vendor_commission_uuid',
            'uniq_8610d53f588a771' => 'uniq_vendor_commission_slug',
            'uniq_cb94bbac4c6a6cb5' => 'uniq_vendor_commission_history_uuid',
            'uniq_cb94bbac588a771' => 'uniq_vendor_commission_history_slug',
            'uniq_218c57314c6a6cb5' => 'uniq_vendor_conversation_uuid',
            'uniq_218c5731588a771' => 'uniq_vendor_conversation_slug',
            'uniq_f83387cf4c6a6cb5' => 'uniq_vendor_conversation_message_uuid',
            'uniq_f83387cf588a771' => 'uniq_vendor_conversation_message_slug',
            'uniq_55ff431c4c6a6cb5' => 'uniq_vendor_customer_order_uuid',
            'uniq_55ff431c588a771' => 'uniq_vendor_customer_order_slug',
            'uniq_60642ccf4c6a6cb5' => 'uniq_vendor_document_attachment_uuid',
            'uniq_60642ccf588a771' => 'uniq_vendor_document_attachment_slug',
            'uniq_57a81874c6a6cb5' => 'uniq_vendor_document_uuid',
            'uniq_57a8187588a771' => 'uniq_vendor_document_slug',
            'uniq_f52233f64c6a6cb5' => 'uniq_vendor_uuid',
            'uniq_f52233f6588a771' => 'uniq_vendor_slug',
            'uniq_a8c51b984c6a6cb5' => 'uniq_vendor_favourite_uuid',
            'uniq_a8c51b98588a771' => 'uniq_vendor_favourite_slug',
            'uniq_7b8646854c6a6cb5' => 'uniq_vendor_group_uuid',
            'uniq_7b864685588a771' => 'uniq_vendor_group_slug',
            'uniq_d0186bbd4c6a6cb5' => 'uniq_vendor_iban_uuid',
            'uniq_d0186bbd588a771' => 'uniq_vendor_iban_slug',
            'uniq_f3b052ab4c6a6cb5' => 'uniq_vendor_ledger_binding_uuid',
            'uniq_f3b052ab588a771' => 'uniq_vendor_ledger_binding_slug',
            'uniq_8d1ed64b4c6a6cb5' => 'uniq_vendor_ledger_entries_uuid',
            'uniq_8d1ed64b588a771' => 'uniq_vendor_ledger_entries_slug',
            'uniq_2d11233e4c6a6cb5' => 'uniq_vendor_log_uuid',
            'uniq_2d11233e588a771' => 'uniq_vendor_log_slug',
            'uniq_b69b9cdc4c6a6cb5' => 'uniq_vendor_media_attachment_uuid',
            'uniq_b69b9cdc588a771' => 'uniq_vendor_media_attachment_slug',
            'uniq_7c6aa34c4c6a6cb5' => 'uniq_vendor_media_uuid',
            'uniq_7c6aa34c588a771' => 'uniq_vendor_media_slug',
            'uniq_68b165f94c6a6cb5' => 'uniq_vendor_passport_uuid',
            'uniq_68b165f9588a771' => 'uniq_vendor_passport_slug',
            'uniq_735fbc624c6a6cb5' => 'uniq_vendor_payment_uuid',
            'uniq_735fbc62588a771' => 'uniq_vendor_payment_slug',
            'uniq_2dee01c84c6a6cb5' => 'uniq_vendor_payout_account_uuid',
            'uniq_2dee01c8588a771' => 'uniq_vendor_payout_account_slug',
            'uniq_38e4ae904c6a6cb5' => 'uniq_vendor_payout_uuid',
            'uniq_38e4ae90588a771' => 'uniq_vendor_payout_slug',
            'uniq_87f8d8a44c6a6cb5' => 'uniq_vendor_payout_item_uuid',
            'uniq_87f8d8a4588a771' => 'uniq_vendor_payout_item_slug',
            'uniq_10beb0f74c6a6cb5' => 'uniq_vendor_profile_avatar_uuid',
            'uniq_10beb0f7588a771' => 'uniq_vendor_profile_avatar_slug',
            'uniq_f27f41804c6a6cb5' => 'uniq_vendor_profile_cover_uuid',
            'uniq_f27f4180588a771' => 'uniq_vendor_profile_cover_slug',
            'uniq_9f2092604c6a6cb5' => 'uniq_vendor_profile_uuid',
            'uniq_9f209260588a771' => 'uniq_vendor_profile_slug',
            'uniq_ad8ff8fd4c6a6cb5' => 'uniq_vendor_remember_me_token_uuid',
            'uniq_ad8ff8fd588a771' => 'uniq_vendor_remember_me_token_slug',
            'uniq_1888de304c6a6cb5' => 'uniq_vendor_security_uuid',
            'uniq_1888de30588a771' => 'uniq_vendor_security_slug',
            'uniq_ffeaa2bd4c6a6cb5' => 'uniq_vendor_service_uuid',
            'uniq_ffeaa2bd588a771' => 'uniq_vendor_service_slug',
            'uniq_dfd82b2d4c6a6cb5' => 'uniq_vendor_shipment_uuid',
            'uniq_dfd82b2d588a771' => 'uniq_vendor_shipment_slug',
            'uniq_a17b84fe4c6a6cb5' => 'uniq_vendor_transaction_uuid',
            'uniq_a17b84fe588a771' => 'uniq_vendor_transaction_slug',
            'uniq_6725c4404c6a6cb5' => 'uniq_vendor_translation_uuid',
            'uniq_6725c440588a771' => 'uniq_vendor_translation_slug',
            'uniq_cc12cfee4c6a6cb5' => 'uniq_vendor_user_assignment_uuid',
            'uniq_cc12cfee588a771' => 'uniq_vendor_user_assignment_slug',
            'uniq_41f221c04c6a6cb5' => 'uniq_vendor_wishlist_uuid',
            'uniq_41f221c0588a771' => 'uniq_vendor_wishlist_slug',
            'uniq_ec87ac7f4c6a6cb5' => 'uniq_vendor_wishlist_item_uuid',
            'uniq_ec87ac7f588a771' => 'uniq_vendor_wishlist_item_slug',
            'idx_87f8d8a4c6d61b7f' => 'idx_vendor_payout_item_payout_id',
            'uniq_9f209260f603ee73' => 'uniq_vendor_profile_vendor_id',
            'uniq_10beb0f7f603ee73' => 'uniq_vendor_profile_avatar_vendor_id',
            'uniq_f27f4180f603ee73' => 'uniq_vendor_profile_cover_vendor_id',
        ] as $old => $new) {
            $this->addSql(sprintf('ALTER INDEX IF EXISTS %s RENAME TO %s', $old, $new));
        }

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_vendor_profile_public_status ON vendor_profile (public_profile_status)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_vendor_transaction_vendor_created ON vendor_transaction (vendor_id, transaction_created_at)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_vendor_payout_vendor_id ON vendor_payout (vendor_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_vendor_payout_status ON vendor_payout (payout_status)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_vendor_service_category ON vendor_service (category_id)');
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS uniq_vendor_service_vendor_category ON vendor_service (vendor_id, category_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_vendor_owner_user_id ON vendor (owner_user_id)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_vendor_payout_account_vendor_id ON vendor_payout_account (vendor_id)');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Canonical business index names and uniqueness must remain intact.');
    }
}
