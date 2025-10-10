<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0001VendorCore extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create vendor core table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE vendor (id INT AUTO_INCREMENT NOT NULL, brand_name VARCHAR(128) NOT NULL, user_id INT DEFAULT NULL, status VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_VENDOR_BRAND (brand_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vendor;');
    }
}
