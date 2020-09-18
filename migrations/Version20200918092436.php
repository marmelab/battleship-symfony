<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200918092436 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C42C04473');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CC0990423');
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CD22CABCD');
        $this->addSql('DROP INDEX UNIQ_232B318CC0990423 ON game');
        $this->addSql('DROP INDEX UNIQ_232B318C42C04473 ON game');
        $this->addSql('DROP INDEX UNIQ_232B318CD22CABCD ON game');
        $this->addSql('ALTER TABLE game DROP current_player_id, DROP player1_id, DROP player2_id');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game ADD current_player_id INT DEFAULT NULL, ADD player1_id INT DEFAULT NULL, ADD player2_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C42C04473 FOREIGN KEY (current_player_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CC0990423 FOREIGN KEY (player1_id) REFERENCES player (id)');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CD22CABCD FOREIGN KEY (player2_id) REFERENCES player (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_232B318CC0990423 ON game (player1_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_232B318C42C04473 ON game (current_player_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_232B318CD22CABCD ON game (player2_id)');
    }
}
