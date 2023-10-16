<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231011085750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_7D3656A4DC2902E0');
        $this->addSql('DROP INDEX IDX_7D3656A4DC2902E0 ON account');
        $this->addSql('ALTER TABLE account CHANGE client_id_id client_id INT NOT NULL');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A419EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_7D3656A419EB6921 ON account (client_id)');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D149CB4726');
        $this->addSql('DROP INDEX IDX_723705D149CB4726 ON transaction');
        $this->addSql('ALTER TABLE transaction ADD amount_amount NUMERIC(15, 2) NOT NULL, DROP amount, CHANGE account_id_id account_id INT NOT NULL, CHANGE currency amount_currency VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D19B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id)');
        $this->addSql('CREATE INDEX IDX_723705D19B6B5FBA ON transaction (account_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE account DROP FOREIGN KEY FK_7D3656A419EB6921');
        $this->addSql('DROP INDEX IDX_7D3656A419EB6921 ON account');
        $this->addSql('ALTER TABLE account CHANGE client_id client_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A4DC2902E0 FOREIGN KEY (client_id_id) REFERENCES client (id)');
        $this->addSql('CREATE INDEX IDX_7D3656A4DC2902E0 ON account (client_id_id)');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D19B6B5FBA');
        $this->addSql('DROP INDEX IDX_723705D19B6B5FBA ON transaction');
        $this->addSql('ALTER TABLE transaction ADD amount NUMERIC(10, 2) NOT NULL, DROP amount_amount, CHANGE account_id account_id_id INT NOT NULL, CHANGE amount_currency currency VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D149CB4726 FOREIGN KEY (account_id_id) REFERENCES account (id)');
        $this->addSql('CREATE INDEX IDX_723705D149CB4726 ON transaction (account_id_id)');
    }
}
