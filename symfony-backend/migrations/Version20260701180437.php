<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260701180437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE availability_request (request_id SERIAL NOT NULL, title VARCHAR(150) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status VARCHAR(30) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(request_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE availability_request_period (period_id SERIAL NOT NULL, request_id INT NOT NULL, date DATE NOT NULL, period VARCHAR(50) NOT NULL, PRIMARY KEY(period_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_979426CF427EB8A5 ON availability_request_period (request_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE referee_response (id SERIAL NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE availability_request_period ADD CONSTRAINT FK_979426CF427EB8A5 FOREIGN KEY (request_id) REFERENCES availability_request (request_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE availability_request_period DROP CONSTRAINT FK_979426CF427EB8A5
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE availability_request
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE availability_request_period
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE referee_response
        SQL);
    }
}
