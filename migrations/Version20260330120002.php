<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330120002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Marketplace: ajoute les champs listing_type, price, currency, exchange_description, status sur artistic_work';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE artistic_work
            ADD listing_type VARCHAR(20) NOT NULL DEFAULT 'none',
            ADD price NUMERIC(10, 2) DEFAULT NULL,
            ADD currency VARCHAR(3) NULL DEFAULT 'EUR',
            ADD exchange_description LONGTEXT DEFAULT NULL,
            ADD status VARCHAR(20) NOT NULL DEFAULT 'available'
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE artistic_work DROP listing_type, DROP price, DROP currency, DROP exchange_description, DROP status');
    }
}
