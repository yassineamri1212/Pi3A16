<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250326124103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentaire (id_commentaire INT AUTO_INCREMENT NOT NULL, id_reservation INT NOT NULL, id_voyage INT NOT NULL, id_user INT NOT NULL, statut VARCHAR(255) NOT NULL, date_reservation DATETIME NOT NULL, PRIMARY KEY(id_commentaire)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, lieu VARCHAR(255) DEFAULT NULL, date_evenement DATETIME NOT NULL, image_evenement VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, start_location VARCHAR(255) NOT NULL, delivery_location VARCHAR(255) NOT NULL, is_delivered TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE moyen_de_transport (id INT AUTO_INCREMENT NOT NULL, prix INT NOT NULL, type VARCHAR(255) NOT NULL, nbre_places INT NOT NULL, evenement_id INT DEFAULT NULL, INDEX IDX_1E6E5727FD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE moyen_de_transport_user (user_id INT AUTO_INCREMENT NOT NULL, moyen_de_transport_id INT NOT NULL, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE offre (id_offre INT AUTO_INCREMENT NOT NULL, id_voiture INT NOT NULL, climatisee TINYINT(1) NOT NULL, photo VARCHAR(255) DEFAULT NULL, type_fuel VARCHAR(255) NOT NULL, PRIMARY KEY(id_offre)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE packag (id INT AUTO_INCREMENT NOT NULL, weight_packag INT NOT NULL, description_packag VARCHAR(255) NOT NULL, id_livrai INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE package (id_package INT AUTO_INCREMENT NOT NULL, weight DOUBLE PRECISION NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id_package)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE parcours (id_parcours INT AUTO_INCREMENT NOT NULL, trajet VARCHAR(255) NOT NULL, distance NUMERIC(10, 2) NOT NULL, estimation_temps INT NOT NULL, PRIMARY KEY(id_parcours)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE post (id_forum INT AUTO_INCREMENT NOT NULL, id_user INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id_forum)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, num_tele INT NOT NULL, etat VARCHAR(255) NOT NULL, sujet VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date DATETIME NOT NULL, utilisateur_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reductions (id INT AUTO_INCREMENT NOT NULL, reduction_percentage NUMERIC(5, 2) NOT NULL, valid_until DATETIME DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_58D6C135A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, reponse LONGTEXT NOT NULL, id_reclamation INT NOT NULL, utilisateur_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, prix NUMERIC(10, 2) NOT NULL, dispo TINYINT(1) NOT NULL, id_voiture INT NOT NULL, type_offre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, user_name VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D64924A232CF (user_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_transport_reservations (user_id INT NOT NULL, moyen_de_transport_id INT NOT NULL, INDEX IDX_29C2010FA76ED395 (user_id), INDEX IDX_29C2010F4B31287 (moyen_de_transport_id), PRIMARY KEY(user_id, moyen_de_transport_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE moyen_de_transport ADD CONSTRAINT FK_1E6E5727FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE reductions ADD CONSTRAINT FK_58D6C135A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_transport_reservations ADD CONSTRAINT FK_29C2010FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_transport_reservations ADD CONSTRAINT FK_29C2010F4B31287 FOREIGN KEY (moyen_de_transport_id) REFERENCES moyen_de_transport (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE moyen_de_transport DROP FOREIGN KEY FK_1E6E5727FD02F13');
        $this->addSql('ALTER TABLE reductions DROP FOREIGN KEY FK_58D6C135A76ED395');
        $this->addSql('ALTER TABLE user_transport_reservations DROP FOREIGN KEY FK_29C2010FA76ED395');
        $this->addSql('ALTER TABLE user_transport_reservations DROP FOREIGN KEY FK_29C2010F4B31287');
        $this->addSql('DROP TABLE commentaire');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('DROP TABLE moyen_de_transport');
        $this->addSql('DROP TABLE moyen_de_transport_user');
        $this->addSql('DROP TABLE offre');
        $this->addSql('DROP TABLE packag');
        $this->addSql('DROP TABLE package');
        $this->addSql('DROP TABLE parcours');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE reductions');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_transport_reservations');
    }
}
