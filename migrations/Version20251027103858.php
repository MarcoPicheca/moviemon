<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027103858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moviemon DROP FOREIGN KEY FK_1EF3C99AE48FD905');
        $this->addSql('DROP INDEX IDX_1EF3C99AE48FD905 ON moviemon');
        $this->addSql('ALTER TABLE moviemon CHANGE game_id captured_id INT NOT NULL');
        $this->addSql('ALTER TABLE moviemon ADD CONSTRAINT FK_1EF3C99AC001A529 FOREIGN KEY (captured_id) REFERENCES game_state (id)');
        $this->addSql('CREATE INDEX IDX_1EF3C99AC001A529 ON moviemon (captured_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moviemon DROP FOREIGN KEY FK_1EF3C99AC001A529');
        $this->addSql('DROP INDEX IDX_1EF3C99AC001A529 ON moviemon');
        $this->addSql('ALTER TABLE moviemon CHANGE captured_id game_id INT NOT NULL');
        $this->addSql('ALTER TABLE moviemon ADD CONSTRAINT FK_1EF3C99AE48FD905 FOREIGN KEY (game_id) REFERENCES game_state (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1EF3C99AE48FD905 ON moviemon (game_id)');
    }
}
