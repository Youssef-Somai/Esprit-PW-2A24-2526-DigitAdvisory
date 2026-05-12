-- Module 5 : Gestion des Missions & Projets
-- Run this script in your MySQL database

CREATE DATABASE IF NOT EXISTS consulting_db CHARACTER SET utf8 COLLATE utf8_general_ci;
USE consulting_db;

CREATE TABLE IF NOT EXISTS Mission (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    date_debut DATE NOT NULL,
    statut ENUM('En attente', 'En cours', 'Terminée', 'Annulée') NOT NULL DEFAULT 'En attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Livrable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mission_id INT NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    date_remise DATE NOT NULL,
    etat_validation ENUM('En attente', 'Validé', 'Rejeté') NOT NULL DEFAULT 'En attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES Mission(id) ON DELETE CASCADE
);

-- Sample data
INSERT INTO Mission (titre, date_debut, statut) VALUES
('Audit Digital Entreprise ABC', '2024-01-15', 'En cours'),
('Transformation RH - Client XYZ', '2024-02-01', 'En attente'),
('Consulting Finance - Groupe Delta', '2024-03-10', 'Terminée');

INSERT INTO Livrable (mission_id, nom_fichier, date_remise, etat_validation) VALUES
(1, 'rapport_audit_v1.pdf', '2024-02-15', 'Validé'),
(1, 'plan_action.docx', '2024-03-01', 'En attente'),
(3, 'rapport_final_finance.pdf', '2024-04-01', 'Validé');
