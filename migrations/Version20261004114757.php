<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261004114757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add post.published flag; posts already on the site stay published';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE post ADD published BOOLEAN DEFAULT false NOT NULL');
        // Until now every non-deleted post was public, so keep it that way.
        $this->addSql('UPDATE post SET published = true WHERE deleted_at IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP published');
    }
}
