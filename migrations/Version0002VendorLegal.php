<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0002VendorLegal extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create vendor_passport and vendor_document tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE vendor_passport (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, tax_id VARCHAR(64) NOT NULL, registration_country VARCHAR(64) NOT NULL, kyc_status VARCHAR(32) NOT NULL, verified_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_VENDOR_PASSPORT_VENDOR (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE vendor_passport ADD CONSTRAINT FK_VP_VENDOR FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE;");

        $this->addSql("CREATE TABLE vendor_document (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, type VARCHAR(48) NOT NULL, file_path VARCHAR(255) NOT NULL, expires_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', uploader_id INT DEFAULT NULL, INDEX IDX_VDOC_VENDOR (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE vendor_document ADD CONSTRAINT FK_VDOC_VENDOR FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vendor_document;');
        $this->addSql('DROP TABLE vendor_passport;');
    }
}
