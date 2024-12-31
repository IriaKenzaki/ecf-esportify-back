<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241229155903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE blacklist (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3B17538571F7E88B (event_id), INDEX IDX_3B175385A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(64) NOT NULL, description VARCHAR(255) NOT NULL, players INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_time_start DATETIME NOT NULL, date_time_end DATETIME NOT NULL, created_by VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, visibility TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE list_participant (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, INDEX IDX_5820D89A71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE list_participant_user (list_participant_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_E2CF9929E44A9791 (list_participant_id), INDEX IDX_E2CF9929A76ED395 (user_id), PRIMARY KEY(list_participant_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE score (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, user_id INT NOT NULL, score INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3299375171F7E88B (event_id), INDEX IDX_32993751A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', username VARCHAR(64) DEFAULT NULL, api_token VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D6497BA2F5EB (api_token), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE blacklist ADD CONSTRAINT FK_3B17538571F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE blacklist ADD CONSTRAINT FK_3B175385A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE list_participant ADD CONSTRAINT FK_5820D89A71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE list_participant_user ADD CONSTRAINT FK_E2CF9929E44A9791 FOREIGN KEY (list_participant_id) REFERENCES list_participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE list_participant_user ADD CONSTRAINT FK_E2CF9929A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_3299375171F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE score ADD CONSTRAINT FK_32993751A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE blacklist DROP FOREIGN KEY FK_3B17538571F7E88B');
        $this->addSql('ALTER TABLE blacklist DROP FOREIGN KEY FK_3B175385A76ED395');
        $this->addSql('ALTER TABLE list_participant DROP FOREIGN KEY FK_5820D89A71F7E88B');
        $this->addSql('ALTER TABLE list_participant_user DROP FOREIGN KEY FK_E2CF9929E44A9791');
        $this->addSql('ALTER TABLE list_participant_user DROP FOREIGN KEY FK_E2CF9929A76ED395');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_3299375171F7E88B');
        $this->addSql('ALTER TABLE score DROP FOREIGN KEY FK_32993751A76ED395');
        $this->addSql('DROP TABLE blacklist');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE list_participant');
        $this->addSql('DROP TABLE list_participant_user');
        $this->addSql('DROP TABLE score');
        $this->addSql('DROP TABLE user');
    }
}
