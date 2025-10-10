<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0005VendorSecurityAnalytics extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create vendor_security and vendor_analytics tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE vendor_security (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, api_key VARCHAR(128) DEFAULT NULL, webhook_secret VARCHAR(128) DEFAULT NULL, two_factor_enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_VS_VENDOR (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE vendor_security ADD CONSTRAINT FK_VS_VENDOR FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE;");

        $this->addSql("CREATE TABLE vendor_analytics (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, total_orders BIGINT NOT NULL, total_revenue_minor BIGINT NOT NULL, avg_rating DOUBLE PRECISION NOT NULL, period_start DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', period_end DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_VA_VENDOR (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;");
        $this->addSql("ALTER TABLE vendor_analytics ADD CONSTRAINT FK_VA_VENDOR FOREIGN KEY (vendor_id) REFERENCES vendor (id) ON DELETE CASCADE;");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vendor_analytics;');
        $this->addSql('DROP TABLE vendor_security;');
    }
}
