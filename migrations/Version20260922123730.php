<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260922123730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application ADD questionnaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1CE07E8FF FOREIGN KEY (questionnaire_id) REFERENCES questionnaire (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A45BDDC1CE07E8FF ON application (questionnaire_id)');
        $this->addSql('ALTER TABLE appointment ADD application_id INT NOT NULL');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8443E030ACD FOREIGN KEY (application_id) REFERENCES application (id)');
        $this->addSql('CREATE INDEX IDX_FE38F8443E030ACD ON appointment (application_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC1CE07E8FF');
        $this->addSql('DROP INDEX UNIQ_A45BDDC1CE07E8FF ON application');
        $this->addSql('ALTER TABLE application DROP questionnaire_id');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8443E030ACD');
        $this->addSql('DROP INDEX IDX_FE38F8443E030ACD ON appointment');
        $this->addSql('ALTER TABLE appointment DROP application_id');
    }
}
