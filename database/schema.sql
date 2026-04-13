-- Database: digitaladvisory
CREATE DATABASE IF NOT EXISTS digitaladvisory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE digitaladvisory;

-- Table: user
CREATE TABLE IF NOT EXISTS user (
    id_user          INT AUTO_INCREMENT PRIMARY KEY,
    email            VARCHAR(255) NOT NULL UNIQUE,
    password         VARCHAR(255) NOT NULL,
    role             ENUM('expert', 'entreprise') NOT NULL,
    statut_compte    ENUM('actif', 'bloque', 'en_attente') NOT NULL DEFAULT 'actif',

    -- Entreprise fields
    nom_entreprise   VARCHAR(255) DEFAULT NULL,
    secteur_activite VARCHAR(255) DEFAULT NULL,
    adresse          TEXT DEFAULT NULL,
    telephone        VARCHAR(30) DEFAULT NULL,

    -- Expert fields
    nom              VARCHAR(100) DEFAULT NULL,
    prenom           VARCHAR(100) DEFAULT NULL,
    domaine          VARCHAR(255) DEFAULT NULL,
    niveau_experience VARCHAR(100) DEFAULT NULL,
    tarif_journalier  DECIMAL(10,2) DEFAULT NULL,

    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
