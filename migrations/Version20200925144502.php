<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200925144502 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE game_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE player_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE ship_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE shoot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE game (id INT NOT NULL, player1_id INT DEFAULT NULL, player2_id INT DEFAULT NULL, current_player_id INT DEFAULT NULL, winner_id INT DEFAULT NULL, hash VARCHAR(255) NOT NULL, status VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_232B318CC0990423 ON game (player1_id)');
        $this->addSql('CREATE INDEX IDX_232B318CD22CABCD ON game (player2_id)');
        $this->addSql('CREATE INDEX IDX_232B318C42C04473 ON game (current_player_id)');
        $this->addSql('CREATE INDEX IDX_232B318C5DFCD4B8 ON game (winner_id)');
        $this->addSql('CREATE TABLE player (id INT NOT NULL, name VARCHAR(255) NOT NULL, hash VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE ship (id INT NOT NULL, game_id INT DEFAULT NULL, player_id INT DEFAULT NULL, coordinates TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FA30EB24E48FD905 ON ship (game_id)');
        $this->addSql('CREATE INDEX IDX_FA30EB2499E6F5DF ON ship (player_id)');
        $this->addSql('COMMENT ON COLUMN ship.coordinates IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE shoot (id INT NOT NULL, game_id INT DEFAULT NULL, player_id INT DEFAULT NULL, coordinates TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7044FCBEE48FD905 ON shoot (game_id)');
        $this->addSql('CREATE INDEX IDX_7044FCBE99E6F5DF ON shoot (player_id)');
        $this->addSql('COMMENT ON COLUMN shoot.coordinates IS \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CC0990423 FOREIGN KEY (player1_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CD22CABCD FOREIGN KEY (player2_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C42C04473 FOREIGN KEY (current_player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ship ADD CONSTRAINT FK_FA30EB24E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ship ADD CONSTRAINT FK_FA30EB2499E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shoot ADD CONSTRAINT FK_7044FCBEE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shoot ADD CONSTRAINT FK_7044FCBE99E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE ship DROP CONSTRAINT FK_FA30EB24E48FD905');
        $this->addSql('ALTER TABLE shoot DROP CONSTRAINT FK_7044FCBEE48FD905');
        $this->addSql('ALTER TABLE game DROP CONSTRAINT FK_232B318CC0990423');
        $this->addSql('ALTER TABLE game DROP CONSTRAINT FK_232B318CD22CABCD');
        $this->addSql('ALTER TABLE game DROP CONSTRAINT FK_232B318C42C04473');
        $this->addSql('ALTER TABLE game DROP CONSTRAINT FK_232B318C5DFCD4B8');
        $this->addSql('ALTER TABLE ship DROP CONSTRAINT FK_FA30EB2499E6F5DF');
        $this->addSql('ALTER TABLE shoot DROP CONSTRAINT FK_7044FCBE99E6F5DF');
        $this->addSql('DROP SEQUENCE game_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE player_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE ship_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE shoot_id_seq CASCADE');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE ship');
        $this->addSql('DROP TABLE shoot');
    }
}
