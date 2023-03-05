<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230305153837 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artistic_work_echange DROP FOREIGN KEY FK_FB8F924012469DE2');
        $this->addSql('ALTER TABLE artistic_work_echange DROP FOREIGN KEY FK_FB8F92407C9DC91E');
        $this->addSql('ALTER TABLE gallery_echange DROP FOREIGN KEY FK_8D0DA297A76ED395');
        $this->addSql('ALTER TABLE gallery_echange DROP FOREIGN KEY FK_8D0DA29712469DE2');
        $this->addSql('DROP TABLE artistic_work_echange');
        $this->addSql('DROP TABLE gallery_echange');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artistic_work_echange (id INT AUTO_INCREMENT NOT NULL, gallery_echange_id INT DEFAULT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, picture VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, updated_at DATETIME NOT NULL, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_FB8F92407C9DC91E (gallery_echange_id), INDEX IDX_FB8F924012469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE gallery_echange (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, slug VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_8D0DA297A76ED395 (user_id), INDEX IDX_8D0DA29712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE artistic_work_echange ADD CONSTRAINT FK_FB8F924012469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE artistic_work_echange ADD CONSTRAINT FK_FB8F92407C9DC91E FOREIGN KEY (gallery_echange_id) REFERENCES gallery_echange (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE gallery_echange ADD CONSTRAINT FK_8D0DA297A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE gallery_echange ADD CONSTRAINT FK_8D0DA29712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
    }
}
