<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version2000VendorSecurity extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create vendor_api_key table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE vendor_api_key (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, token VARCHAR(64) NOT NULL, permissions JSON NOT NULL, expires_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', status VARCHAR(16) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', last_used_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_VAK_TOKEN (token), INDEX IDX_VAK_VENDOR (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE vendor_api_key ADD CONSTRAINT FK_VAK_VENDOR FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vendor_api_key;');
    }
}
