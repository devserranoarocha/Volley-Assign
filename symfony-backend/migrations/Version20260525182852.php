<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260525182852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE referee (id SERIAL NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, base_venue_id INT NOT NULL, referre_level VARCHAR(255) NOT NULL, incompatibility_id INT DEFAULT NULL, other_class VARCHAR(255) DEFAULT NULL, others VARCHAR(255) NOT NULL, season_counter INT NOT NULL, week_counter INT NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D60FB342A76ED395 ON referee (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sports_hall (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, short_name VARCHAR(100) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE teams (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, team_level VARCHAR(100) NOT NULL, city VARCHAR(100) DEFAULT NULL, first_coach VARCHAR(255) DEFAULT NULL, second_coach VARCHAR(255) DEFAULT NULL, delegate VARCHAR(255) DEFAULT NULL, doctor VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee ADD CONSTRAINT FK_D60FB342A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee DROP CONSTRAINT FK_D60FB342A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE referee
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sports_hall
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE teams
        SQL);
    }
}
