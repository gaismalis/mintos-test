<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231011143250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19B6B5FBA');
        $this->addSql('DROP INDEX IDX_723705D19B6B5FBA ON transaction');
        $this->addSql('ALTER TABLE transaction ADD sender_account_id INT NOT NULL, CHANGE account_id recipient_account_id INT NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1E36B15C4 FOREIGN KEY (recipient_account_id) REFERENCES account (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1CFEF0177 FOREIGN KEY (sender_account_id) REFERENCES account (id)');
        $this->addSql('CREATE INDEX IDX_723705D1E36B15C4 ON transaction (recipient_account_id)');
        $this->addSql('CREATE INDEX IDX_723705D1CFEF0177 ON transaction (sender_account_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1E36B15C4');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1CFEF0177');
        $this->addSql('DROP INDEX IDX_723705D1E36B15C4 ON transaction');
        $this->addSql('DROP INDEX IDX_723705D1CFEF0177 ON transaction');
        $this->addSql('ALTER TABLE transaction ADD account_id INT NOT NULL, DROP recipient_account_id, DROP sender_account_id');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('CREATE INDEX IDX_723705D19B6B5FBA ON transaction (account_id)');
    }
}
