<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221021174204 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO message (identifier, subject, content) VALUES 
            (\'limited\', \'limited connection\', \'Connection to the service is limited, check the specified route\'),
            (\'failed\', \'No connection\', \'Unable to connect to the service, check the specified route\'),
            (\'sucess\', \'Connection successfully\', \'The connection to the service was successful\')
        ');
    }

    public function down(Schema $schema): void
    {

    }
}
