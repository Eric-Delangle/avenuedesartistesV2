<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330120003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Marketplace: crée la table offer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE offer (
            id INT AUTO_INCREMENT NOT NULL,
            proposed_work_id INT DEFAULT NULL,
            target_work_id INT DEFAULT NULL,
            sender_id INT DEFAULT NULL,
            type VARCHAR(20) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT \'pending\',
            offer_price NUMERIC(10, 2) DEFAULT NULL,
            offer_message LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            INDEX IDX_29D6873EE6B4C3B6 (proposed_work_id),
            INDEX IDX_29D6873E6BDEA911 (target_work_id),
            INDEX IDX_29D6873EF624B39D (sender_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE offer
            ADD CONSTRAINT FK_offer_proposed_work FOREIGN KEY (proposed_work_id) REFERENCES artistic_work (id) ON DELETE SET NULL,
            ADD CONSTRAINT FK_offer_target_work FOREIGN KEY (target_work_id) REFERENCES artistic_work (id) ON DELETE SET NULL,
            ADD CONSTRAINT FK_offer_sender FOREIGN KEY (sender_id) REFERENCES user (id) ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_offer_proposed_work');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_offer_target_work');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_offer_sender');
        $this->addSql('DROP TABLE offer');
    }
}
