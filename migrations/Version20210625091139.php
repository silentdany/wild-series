<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210625091139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE program DROP owner_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_92ED7784989D9B62 ON program (slug)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F0E45BA9989D9B62 ON season (slug)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_92ED7784989D9B62 ON program');
        $this->addSql('ALTER TABLE program ADD owner_id INT NOT NULL');
        $this->addSql('DROP INDEX UNIQ_F0E45BA9989D9B62 ON season');
    }
}
