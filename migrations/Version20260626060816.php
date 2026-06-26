<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260626060816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY `FK_D88926221137ABCF`');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT FK_D88926221137ABCF FOREIGN KEY (album_id) REFERENCES albums (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rating DROP FOREIGN KEY FK_D88926221137ABCF');
        $this->addSql('ALTER TABLE rating ADD CONSTRAINT `FK_D88926221137ABCF` FOREIGN KEY (album_id) REFERENCES albums (id)');
    }
}
