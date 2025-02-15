<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215171552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE rental ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE type ADD is_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user ADD is_active TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE equipment DROP is_active');
        $this->addSql('ALTER TABLE type DROP is_active');
        $this->addSql('ALTER TABLE user DROP is_active');
        $this->addSql('ALTER TABLE rental DROP is_active');
    }
}
