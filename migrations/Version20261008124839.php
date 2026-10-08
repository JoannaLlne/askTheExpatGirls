<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261008124839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application ADD expat_package_id INT DEFAULT NULL, CHANGE service_id service_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC184B0ED91 FOREIGN KEY (expat_package_id) REFERENCES expat_packages (id)');
        $this->addSql('CREATE INDEX IDX_A45BDDC184B0ED91 ON application (expat_package_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC184B0ED91');
        $this->addSql('DROP INDEX IDX_A45BDDC184B0ED91 ON application');
        $this->addSql('ALTER TABLE application DROP expat_package_id, CHANGE service_id service_id INT NOT NULL');
    }
}
