<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330120004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Marketplace: crée la table transaction';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE transaction (
            id INT AUTO_INCREMENT NOT NULL,
            artwork_id INT DEFAULT NULL,
            buyer_id INT DEFAULT NULL,
            seller_id INT DEFAULT NULL,
            offer_id INT DEFAULT NULL,
            type VARCHAR(20) NOT NULL,
            amount NUMERIC(10, 2) DEFAULT NULL,
            stripe_payment_intent_id VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) NOT NULL DEFAULT \'pending\',
            completed_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id),
            UNIQUE INDEX UNIQ_55D46D7A53C674EE (offer_id),
            INDEX IDX_55D46D7ADB3F74EA (artwork_id),
            INDEX IDX_55D46D7A6C755722 (buyer_id),
            INDEX IDX_55D46D7A8DE820D9 (seller_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE transaction
            ADD CONSTRAINT FK_transaction_artwork FOREIGN KEY (artwork_id) REFERENCES artistic_work (id) ON DELETE SET NULL,
            ADD CONSTRAINT FK_transaction_buyer FOREIGN KEY (buyer_id) REFERENCES user (id) ON DELETE SET NULL,
            ADD CONSTRAINT FK_transaction_seller FOREIGN KEY (seller_id) REFERENCES user (id) ON DELETE SET NULL,
            ADD CONSTRAINT FK_transaction_offer FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE SET NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_transaction_artwork');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_transaction_buyer');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_transaction_seller');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_transaction_offer');
        $this->addSql('DROP TABLE transaction');
    }
}
