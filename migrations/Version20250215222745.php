<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215222745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F361016230A8');
        $this->addSql('DROP INDEX IDX_8C9F361016230A8 ON file');
        $this->addSql('ALTER TABLE file DROP video_game_id');
        $this->addSql('ALTER TABLE video_game ADD file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE video_game ADD CONSTRAINT FK_24BC6C5093CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_24BC6C5093CB796C ON video_game (file_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file ADD video_game_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F361016230A8 FOREIGN KEY (video_game_id) REFERENCES video_game (id)');
        $this->addSql('CREATE INDEX IDX_8C9F361016230A8 ON file (video_game_id)');
        $this->addSql('ALTER TABLE video_game DROP FOREIGN KEY FK_24BC6C5093CB796C');
        $this->addSql('DROP INDEX UNIQ_24BC6C5093CB796C ON video_game');
        $this->addSql('ALTER TABLE video_game DROP file_id');
    }
}
