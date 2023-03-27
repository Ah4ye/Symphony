<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230326215323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE i23_paniers_produits (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, id_panier INTEGER NOT NULL, id_produit INTEGER NOT NULL, quantite INTEGER NOT NULL, CONSTRAINT FK_1B4F5C632FBB81F FOREIGN KEY (id_panier) REFERENCES i23_paniers (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1B4F5C63F7384557 FOREIGN KEY (id_produit) REFERENCES i23_produits (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_1B4F5C632FBB81F ON i23_paniers_produits (id_panier)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1B4F5C63F7384557 ON i23_paniers_produits (id_produit)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE i23_paniers_produits');
    }
}
