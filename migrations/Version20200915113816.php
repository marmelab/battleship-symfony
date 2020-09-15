<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200915113816 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP INDEX IDX_232B318CC0990423, ADD UNIQUE INDEX UNIQ_232B318CC0990423 (player1_id)');
        $this->addSql('ALTER TABLE game DROP INDEX IDX_232B318CD22CABCD, ADD UNIQUE INDEX UNIQ_232B318CD22CABCD (player2_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP INDEX UNIQ_232B318CC0990423, ADD INDEX IDX_232B318CC0990423 (player1_id)');
        $this->addSql('ALTER TABLE game DROP INDEX UNIQ_232B318CD22CABCD, ADD INDEX IDX_232B318CD22CABCD (player2_id)');
    }
}
