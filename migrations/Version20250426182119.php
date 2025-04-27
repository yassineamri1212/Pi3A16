<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250426182119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, lieu VARCHAR(255) DEFAULT NULL, date_evenement DATETIME NOT NULL, image_evenement VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE forum_commentaire (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, author_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_61C4EB1EF675F31B (author_id), INDEX IDX_61C4EB1E4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE forum_post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, author_id INT NOT NULL, INDEX IDX_996BCC5AF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, start_location VARCHAR(255) NOT NULL, delivery_location VARCHAR(255) NOT NULL, is_delivered TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE moyen_de_transport (id INT AUTO_INCREMENT NOT NULL, prix INT NOT NULL, type VARCHAR(255) NOT NULL, nbre_places INT NOT NULL, point_depart VARCHAR(255) NOT NULL, evenement_id INT DEFAULT NULL, INDEX IDX_1E6E5727FD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE moyen_de_transport_user (user_id INT AUTO_INCREMENT NOT NULL, moyen_de_transport_id INT NOT NULL, PRIMARY KEY(user_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE offre (id_offre INT AUTO_INCREMENT NOT NULL, climatisee TINYINT(1) NOT NULL, photo VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, type_fuel VARCHAR(50) NOT NULL, nombre_places INT NOT NULL, prix NUMERIC(10, 2) NOT NULL, date_depart DATETIME NOT NULL, conducteur_id INT NOT NULL, id_parcours INT NOT NULL, INDEX IDX_AF86866FF16F4AC6 (conducteur_id), INDEX IDX_AF86866FC96A3276 (id_parcours), PRIMARY KEY(id_offre)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE package (id INT AUTO_INCREMENT NOT NULL, weight_package INT NOT NULL, description_package VARCHAR(255) NOT NULL, livraison_id INT NOT NULL, INDEX IDX_DE6867958E54FB25 (livraison_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE parcours (id_parcours INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, pickup VARCHAR(255) NOT NULL, destination VARCHAR(255) NOT NULL, latitude_pickup DOUBLE PRECISION DEFAULT NULL, longitude_pickup DOUBLE PRECISION DEFAULT NULL, latitude_destination DOUBLE PRECISION DEFAULT NULL, longitude_destination DOUBLE PRECISION DEFAULT NULL, distance NUMERIC(10, 2) DEFAULT NULL, time INT DEFAULT NULL, PRIMARY KEY(id_parcours)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, num_tele INT DEFAULT NULL, etat VARCHAR(50) NOT NULL, sujet VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date DATETIME NOT NULL, utilisateur_id INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reductions (id INT AUTO_INCREMENT NOT NULL, reduction_percentage NUMERIC(5, 2) NOT NULL, valid_until DATETIME DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_58D6C135A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, date DATETIME NOT NULL, reponse LONGTEXT NOT NULL, utilisateur_id INT NOT NULL, username VARCHAR(255) NOT NULL, reclamation_id INT NOT NULL, INDEX IDX_5FB6DEC72D6BA2D9 (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, photo VARCHAR(255) DEFAULT NULL, prix NUMERIC(10, 2) NOT NULL, dispo TINYINT(1) NOT NULL, id_voiture INT NOT NULL, type_offre VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservation_offer (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) DEFAULT 'confirmed' NOT NULL, created_at DATETIME NOT NULL, offre_id INT NOT NULL, passenger_id INT NOT NULL, INDEX IDX_6AA957994CC8505A (offre_id), INDEX IDX_6AA957994502E565 (passenger_id), UNIQUE INDEX UNIQ_RESERVATION_OFFRE_PASSENGER (offre_id, passenger_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL, expires_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, user_name VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_blocked TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D64924A232CF (user_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_transport_reservations (user_id INT NOT NULL, moyen_de_transport_id INT NOT NULL, INDEX IDX_29C2010FA76ED395 (user_id), INDEX IDX_29C2010F4B31287 (moyen_de_transport_id), PRIMARY KEY(user_id, moyen_de_transport_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_commentaire ADD CONSTRAINT FK_61C4EB1EF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_commentaire ADD CONSTRAINT FK_61C4EB1E4B89032C FOREIGN KEY (post_id) REFERENCES forum_post (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post ADD CONSTRAINT FK_996BCC5AF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE moyen_de_transport ADD CONSTRAINT FK_1E6E5727FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre ADD CONSTRAINT FK_AF86866FF16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre ADD CONSTRAINT FK_AF86866FC96A3276 FOREIGN KEY (id_parcours) REFERENCES parcours (id_parcours)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE package ADD CONSTRAINT FK_DE6867958E54FB25 FOREIGN KEY (livraison_id) REFERENCES livraison (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reductions ADD CONSTRAINT FK_58D6C135A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC72D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_offer ADD CONSTRAINT FK_6AA957994CC8505A FOREIGN KEY (offre_id) REFERENCES offre (id_offre)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_offer ADD CONSTRAINT FK_6AA957994502E565 FOREIGN KEY (passenger_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_transport_reservations ADD CONSTRAINT FK_29C2010FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_transport_reservations ADD CONSTRAINT FK_29C2010F4B31287 FOREIGN KEY (moyen_de_transport_id) REFERENCES moyen_de_transport (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_commentaire DROP FOREIGN KEY FK_61C4EB1EF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_commentaire DROP FOREIGN KEY FK_61C4EB1E4B89032C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE forum_post DROP FOREIGN KEY FK_996BCC5AF675F31B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE moyen_de_transport DROP FOREIGN KEY FK_1E6E5727FD02F13
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre DROP FOREIGN KEY FK_AF86866FF16F4AC6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre DROP FOREIGN KEY FK_AF86866FC96A3276
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE package DROP FOREIGN KEY FK_DE6867958E54FB25
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reductions DROP FOREIGN KEY FK_58D6C135A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC72D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_offer DROP FOREIGN KEY FK_6AA957994CC8505A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservation_offer DROP FOREIGN KEY FK_6AA957994502E565
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_transport_reservations DROP FOREIGN KEY FK_29C2010FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_transport_reservations DROP FOREIGN KEY FK_29C2010F4B31287
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE evenement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE forum_commentaire
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE forum_post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE livraison
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE moyen_de_transport
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE moyen_de_transport_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE offre
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE package
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE parcours
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reclamation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reductions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reponse
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservation_offer
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reset_password_request
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE `user`
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_transport_reservations
        SQL);
    }
}
