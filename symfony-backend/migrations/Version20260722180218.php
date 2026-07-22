<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260722180218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response ADD referee_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response ADD period_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response ADD is_available BOOLEAN NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response ADD displacement VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response ADD CONSTRAINT FK_8A893B854A087CA2 FOREIGN KEY (referee_id) REFERENCES referee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response ADD CONSTRAINT FK_8A893B85EC8B7ADE FOREIGN KEY (period_id) REFERENCES availability_request_period (period_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8A893B854A087CA2 ON referee_response (referee_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8A893B85EC8B7ADE ON referee_response (period_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response DROP CONSTRAINT FK_8A893B854A087CA2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response DROP CONSTRAINT FK_8A893B85EC8B7ADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8A893B854A087CA2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8A893B85EC8B7ADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response DROP referee_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response DROP period_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response DROP is_available
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE referee_response DROP displacement
        SQL);
    }
}
