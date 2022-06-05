<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211128134311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE artistic_work_echange (id INT AUTO_INCREMENT NOT NULL, gallery_echange_id INT DEFAULT NULL, category_id INT DEFAULT NULL, slug VARCHAR(191) NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, picture VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, description VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_FB8F9240989D9B62 (slug), INDEX IDX_FB8F92407C9DC91E (gallery_echange_id), INDEX IDX_FB8F924012469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE artistic_work_vente (id INT AUTO_INCREMENT NOT NULL, gallery_vente_id INT DEFAULT NULL, category_id INT DEFAULT NULL, slug VARCHAR(191) NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, picture VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, description VARCHAR(255) DEFAULT NULL, price NUMERIC(10, 0) DEFAULT NULL, UNIQUE INDEX UNIQ_C3A9B417989D9B62 (slug), INDEX IDX_C3A9B417484AC5B1 (gallery_vente_id), INDEX IDX_C3A9B41712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gallery_echange (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, INDEX IDX_8D0DA29712469DE2 (category_id), INDEX IDX_8D0DA297A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gallery_vente (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, INDEX IDX_6C17FA6F12469DE2 (category_id), INDEX IDX_6C17FA6FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, expediteur_id INT DEFAULT NULL, destinataire_id INT DEFAULT NULL, titre VARCHAR(255) DEFAULT NULL, message LONGTEXT NOT NULL, posted_at DATETIME NOT NULL, INDEX IDX_B6BD307F10335F61 (expediteur_id), INDEX IDX_B6BD307FA4F84F6E (destinataire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, expediteur_id INT DEFAULT NULL, destinataire_id INT DEFAULT NULL, message LONGTEXT NOT NULL, posted_at DATETIME NOT NULL, INDEX IDX_5FB6DEC710335F61 (expediteur_id), INDEX IDX_5FB6DEC7A4F84F6E (destinataire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, user_identifier VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, avatar VARCHAR(255) NOT NULL, registered_at DATETIME NOT NULL, niveau INT NOT NULL, description2 LONGTEXT DEFAULT NULL, adress VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(255) DEFAULT NULL, tel VARCHAR(255) DEFAULT NULL, activation_token VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_category (user_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_E6C1FDC1A76ED395 (user_id), INDEX IDX_E6C1FDC112469DE2 (category_id), PRIMARY KEY(user_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE artistic_work_echange ADD CONSTRAINT FK_FB8F92407C9DC91E FOREIGN KEY (gallery_echange_id) REFERENCES gallery_echange (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE artistic_work_echange ADD CONSTRAINT FK_FB8F924012469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE artistic_work_vente ADD CONSTRAINT FK_C3A9B417484AC5B1 FOREIGN KEY (gallery_vente_id) REFERENCES gallery_vente (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE artistic_work_vente ADD CONSTRAINT FK_C3A9B41712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE gallery_echange ADD CONSTRAINT FK_8D0DA29712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE gallery_echange ADD CONSTRAINT FK_8D0DA297A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE gallery_vente ADD CONSTRAINT FK_6C17FA6F12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE gallery_vente ADD CONSTRAINT FK_6C17FA6FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F10335F61 FOREIGN KEY (expediteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC710335F61 FOREIGN KEY (expediteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7A4F84F6E FOREIGN KEY (destinataire_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_category ADD CONSTRAINT FK_E6C1FDC1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_category ADD CONSTRAINT FK_E6C1FDC112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artistic_work_echange DROP FOREIGN KEY FK_FB8F924012469DE2');
        $this->addSql('ALTER TABLE artistic_work_vente DROP FOREIGN KEY FK_C3A9B41712469DE2');
        $this->addSql('ALTER TABLE gallery_echange DROP FOREIGN KEY FK_8D0DA29712469DE2');
        $this->addSql('ALTER TABLE gallery_vente DROP FOREIGN KEY FK_6C17FA6F12469DE2');
        $this->addSql('ALTER TABLE user_category DROP FOREIGN KEY FK_E6C1FDC112469DE2');
        $this->addSql('ALTER TABLE artistic_work_echange DROP FOREIGN KEY FK_FB8F92407C9DC91E');
        $this->addSql('ALTER TABLE artistic_work_vente DROP FOREIGN KEY FK_C3A9B417484AC5B1');
        $this->addSql('ALTER TABLE gallery_echange DROP FOREIGN KEY FK_8D0DA297A76ED395');
        $this->addSql('ALTER TABLE gallery_vente DROP FOREIGN KEY FK_6C17FA6FA76ED395');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F10335F61');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA4F84F6E');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC710335F61');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7A4F84F6E');
        $this->addSql('ALTER TABLE user_category DROP FOREIGN KEY FK_E6C1FDC1A76ED395');
        $this->addSql('DROP TABLE artistic_work_echange');
        $this->addSql('DROP TABLE artistic_work_vente');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE gallery_echange');
        $this->addSql('DROP TABLE gallery_vente');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_category');
    }
}
