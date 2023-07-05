<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230705121308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visitor ADD page_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE visitor ADD CONSTRAINT FK_CAE5E19FC4663E4 FOREIGN KEY (page_id) REFERENCES article (id)');
        $this->addSql('CREATE INDEX IDX_CAE5E19FC4663E4 ON visitor (page_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE visitor DROP FOREIGN KEY FK_CAE5E19FC4663E4');
        $this->addSql('DROP INDEX IDX_CAE5E19FC4663E4 ON visitor');
        $this->addSql('ALTER TABLE visitor DROP page_id');
    }
}
