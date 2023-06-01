<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230525134407 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE section ADD created_by_id INT DEFAULT NULL, ADD position INT NOT NULL, ADD created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE section ADD CONSTRAINT FK_2D737AEFB03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_2D737AEFB03A8386 ON section (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP created_at');
        $this->addSql('ALTER TABLE section DROP FOREIGN KEY FK_2D737AEFB03A8386');
        $this->addSql('DROP INDEX IDX_2D737AEFB03A8386 ON section');
        $this->addSql('ALTER TABLE section DROP created_by_id, DROP position, DROP created_at');
    }
}
