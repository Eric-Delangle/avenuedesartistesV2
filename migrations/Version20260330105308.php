<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260330105308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_offer_target_work');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_offer_proposed_work');
        $this->addSql('DROP INDEX idx_29d6873ee6b4c3b6 ON offer');
        $this->addSql('CREATE INDEX IDX_29D6873E5CA361F2 ON offer (proposed_work_id)');
        $this->addSql('DROP INDEX idx_29d6873e6bdea911 ON offer');
        $this->addSql('CREATE INDEX IDX_29D6873E705CEAB0 ON offer (target_work_id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_offer_target_work FOREIGN KEY (target_work_id) REFERENCES artistic_work (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_offer_proposed_work FOREIGN KEY (proposed_work_id) REFERENCES artistic_work (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_transaction_artwork');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_transaction_seller');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_transaction_buyer');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_transaction_offer');
        $this->addSql('DROP INDEX idx_55d46d7adb3f74ea ON transaction');
        $this->addSql('CREATE INDEX IDX_723705D1DB8FFA4 ON transaction (artwork_id)');
        $this->addSql('DROP INDEX idx_55d46d7a6c755722 ON transaction');
        $this->addSql('CREATE INDEX IDX_723705D16C755722 ON transaction (buyer_id)');
        $this->addSql('DROP INDEX idx_55d46d7a8de820d9 ON transaction');
        $this->addSql('CREATE INDEX IDX_723705D18DE820D9 ON transaction (seller_id)');
        $this->addSql('DROP INDEX uniq_55d46d7a53c674ee ON transaction');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_723705D153C674EE ON transaction (offer_id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_transaction_artwork FOREIGN KEY (artwork_id) REFERENCES artistic_work (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_transaction_seller FOREIGN KEY (seller_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_transaction_buyer FOREIGN KEY (buyer_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_transaction_offer FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E5CA361F2');
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E705CEAB0');
        $this->addSql('DROP INDEX idx_29d6873e5ca361f2 ON offer');
        $this->addSql('CREATE INDEX IDX_29D6873EE6B4C3B6 ON offer (proposed_work_id)');
        $this->addSql('DROP INDEX idx_29d6873e705ceab0 ON offer');
        $this->addSql('CREATE INDEX IDX_29D6873E6BDEA911 ON offer (target_work_id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E5CA361F2 FOREIGN KEY (proposed_work_id) REFERENCES artistic_work (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E705CEAB0 FOREIGN KEY (target_work_id) REFERENCES artistic_work (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1DB8FFA4');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D16C755722');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D18DE820D9');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D153C674EE');
        $this->addSql('DROP INDEX idx_723705d1db8ffa4 ON transaction');
        $this->addSql('CREATE INDEX IDX_55D46D7ADB3F74EA ON transaction (artwork_id)');
        $this->addSql('DROP INDEX idx_723705d16c755722 ON transaction');
        $this->addSql('CREATE INDEX IDX_55D46D7A6C755722 ON transaction (buyer_id)');
        $this->addSql('DROP INDEX idx_723705d18de820d9 ON transaction');
        $this->addSql('CREATE INDEX IDX_55D46D7A8DE820D9 ON transaction (seller_id)');
        $this->addSql('DROP INDEX uniq_723705d153c674ee ON transaction');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_55D46D7A53C674EE ON transaction (offer_id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1DB8FFA4 FOREIGN KEY (artwork_id) REFERENCES artistic_work (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D16C755722 FOREIGN KEY (buyer_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D18DE820D9 FOREIGN KEY (seller_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D153C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE SET NULL');
    }
}
