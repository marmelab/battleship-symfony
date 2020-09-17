<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200915135602 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shoot ADD game_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shoot ADD CONSTRAINT FK_7044FCBEE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
        $this->addSql('CREATE INDEX IDX_7044FCBEE48FD905 ON shoot (game_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shoot DROP FOREIGN KEY FK_7044FCBEE48FD905');
        $this->addSql('DROP INDEX IDX_7044FCBEE48FD905 ON shoot');
        $this->addSql('ALTER TABLE shoot DROP game_id');
    }
}
