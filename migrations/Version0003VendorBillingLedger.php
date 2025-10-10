<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0003VendorBillingLedger extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create vendor_billing and vendor_ledger_binding tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE vendor_billing (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, iban VARCHAR(34) DEFAULT NULL, swift VARCHAR(64) DEFAULT NULL, payout_method VARCHAR(32) NOT NULL, billing_email VARCHAR(128) DEFAULT NULL, last_payout_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', payout_status VARCHAR(24) NOT NULL, UNIQUE INDEX UNIQ_VB_VENDOR (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE vendor_billing ADD CONSTRAINT FK_VB_VENDOR FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE;");

        $this->addSql("CREATE TABLE vendor_ledger_binding (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, ledger_account_id VARCHAR(96) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_VLB_VENDOR (vendor_id), UNIQUE INDEX UNIQ_VLB_ACCOUNT (ledger_account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE vendor_ledger_binding ADD CONSTRAINT FK_VLB_VENDOR FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vendor_ledger_binding;');
        $this->addSql('DROP TABLE vendor_billing;');
    }
}
