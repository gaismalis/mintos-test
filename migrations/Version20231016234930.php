<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231016234930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO client (id, name) VALUES (1, \'Kristaps\')');
        $this->addSql('INSERT INTO client (id, name) VALUES (2, \'Lebron James\')');

        $this->addSql('INSERT INTO account (id, client_id, balance_amount, balance_currency) VALUES (1, 1, 500, \'EUR\')');
        $this->addSql('INSERT INTO account (id, client_id, balance_amount, balance_currency) VALUES (2, 2, 1000, \'USD\')');

        $this->addSql('INSERT INTO transaction (amount_currency, amount_amount, account_id, type) VALUES (\'EUR\', 10.00, 2, \'incoming\')');
        $this->addSql('INSERT INTO transaction (amount_currency, amount_amount, account_id, type) VALUES (\'USD\', 10.53, 1, \'outgoing\')');
        $this->addSql('INSERT INTO transaction (amount_currency, amount_amount, account_id, type) VALUES (\'EUR\', 10.00, 2, \'incoming\')');
        $this->addSql('INSERT INTO transaction (amount_currency, amount_amount, account_id, type) VALUES (\'USD\', 10.53, 1, \'outgoing\')');
        $this->addSql('INSERT INTO transaction (amount_currency, amount_amount, account_id, type) VALUES (\'EUR\', 10.00, 2, \'incoming\')');
        $this->addSql('INSERT INTO transaction (amount_currency, amount_amount, account_id, type) VALUES (\'USD\', 10.53, 1, \'outgoing\')');
        $this->addSql('INSERT INTO transaction (amount_currency, amount_amount, account_id, type) VALUES (\'EUR\', 10.00, 2, \'incoming\')');
        $this->addSql('INSERT INTO transaction (amount_currency, amount_amount, account_id, type) VALUES (\'USD\', 10.53, 1, \'outgoing\')');
        $this->addSql('INSERT INTO transaction (amount_currency, amount_amount, account_id, type) VALUES (\'EUR\', 10.00, 2, \'incoming\')');
        $this->addSql('INSERT INTO transaction (amount_currency, amount_amount, account_id, type) VALUES (\'USD\', 10.53, 1, \'outgoing\')');

        $this->addSql('INSERT INTO exchange_rate (base_currency, target_currency, exchange_rate) VALUES (\'USD\', \'EUR\', 0.9499601497)');
        $this->addSql('INSERT INTO exchange_rate (base_currency, target_currency, exchange_rate) VALUES (\'USD\', \'GBP\', 0.8227501062)');
        $this->addSql('INSERT INTO exchange_rate (base_currency, target_currency, exchange_rate) VALUES (\'EUR\', \'GBP\', 0.8660890738)');
        $this->addSql('INSERT INTO exchange_rate (base_currency, target_currency, exchange_rate) VALUES (\'EUR\', \'USD\', 1.0526757363)');
        $this->addSql('INSERT INTO exchange_rate (base_currency, target_currency, exchange_rate) VALUES (\'GBP\', \'EUR\', 1.1546156513)');
        $this->addSql('INSERT INTO exchange_rate (base_currency, target_currency, exchange_rate) VALUES (\'GBP\', \'USD\', 1.2154358808)');
    }

    public function down(Schema $schema): void
    {
    }
}
