<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0004VendorProfileMedia extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create profile, media, attachment tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE vendor_profile (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, display_name VARCHAR(128) DEFAULT NULL, about LONGTEXT DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, socials JSON DEFAULT NULL, seo_title VARCHAR(150) DEFAULT NULL, seo_description VARCHAR(300) DEFAULT NULL, UNIQUE INDEX UNIQ_VP_VENDOR (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE vendor_profile ADD CONSTRAINT FK_VP_VENDOR FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE;");

        $this->addSql("CREATE TABLE vendor_media (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, logo_path VARCHAR(255) DEFAULT NULL, banner_path VARCHAR(255) DEFAULT NULL, gallery JSON DEFAULT NULL, INDEX IDX_VM_VENDOR (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE vendor_media ADD CONSTRAINT FK_VM_VENDOR FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE;");

        $this->addSql("CREATE TABLE vendor_attachment (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, title VARCHAR(160) NOT NULL, file_path VARCHAR(255) NOT NULL, category VARCHAR(48) DEFAULT NULL, INDEX IDX_VA_VENDOR (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE vendor_attachment ADD CONSTRAINT FK_VA_VENDOR FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vendor_attachment;');
        $this->addSql('DROP TABLE vendor_media;');
        $this->addSql('DROP TABLE vendor_profile;');
    }
}
