<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200915133649 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shoot DROP INDEX UNIQ_7044FCBE99E6F5DF, ADD INDEX IDX_7044FCBE99E6F5DF (player_id)');
        $this->addSql('ALTER TABLE shoot CHANGE player_id player_id INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shoot DROP INDEX IDX_7044FCBE99E6F5DF, ADD UNIQUE INDEX UNIQ_7044FCBE99E6F5DF (player_id)');
        $this->addSql('ALTER TABLE shoot CHANGE player_id player_id INT NOT NULL');
    }
}
