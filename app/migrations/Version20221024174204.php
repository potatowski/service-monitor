<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221024174204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO request_method (method) VALUES 
            (\'GET\'),
            (\'POST\'),
            (\'PUT\'),
            (\'PATCH\')
        ');
    }

    public function down(Schema $schema): void
    {

    }
}
