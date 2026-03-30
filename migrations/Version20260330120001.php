<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260330120001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Marketplace: ajoute gallery_type sur la table gallery';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE gallery ADD gallery_type VARCHAR(20) NOT NULL DEFAULT 'showcase'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE gallery DROP gallery_type');
    }
}
