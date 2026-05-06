-- ─── SCRIPT DE CREATION DE LA BASE DE DONNEES DIGITADVISORY ───
-- (À exécuter dans phpMyAdmin)

CREATE DATABASE IF NOT EXISTS digitadvisory CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE digitadvisory;

-- 2. Table Certificat (Mise à jour V2 Pro)
CREATE TABLE IF NOT EXISTS certificat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    norme VARCHAR(100) NOT NULL,
    titre VARCHAR(255) NOT NULL,
    version VARCHAR(50) DEFAULT '2022',
    statut VARCHAR(50) DEFAULT 'Actif',
    duree_validite INT DEFAULT 36,
    description TEXT NULL,
    organisme VARCHAR(255) NULL,
    logo_url VARCHAR(255) NULL,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table Critere (Mise à jour V2 Pro - Sans le Poids direct)
CREATE TABLE IF NOT EXISTS critere (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    categorie VARCHAR(255) DEFAULT 'Général',
    description TEXT NULL,
    moyen_preuve TEXT NULL,
    est_obligatoire TINYINT(1) DEFAULT 1,
    difficulte VARCHAR(50) DEFAULT 'Moyen',
    document_template VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table de jointure : certificat_critere (Le Poids est ici)
CREATE TABLE IF NOT EXISTS certificat_critere (
    certificat_id INT NOT NULL,
    critere_id INT NOT NULL,
    poids INT DEFAULT 1,
    PRIMARY KEY (certificat_id, critere_id),
    CONSTRAINT fk_certificat_join FOREIGN KEY (certificat_id) REFERENCES certificat(id) ON DELETE CASCADE,
    CONSTRAINT fk_critere_join FOREIGN KEY (critere_id) REFERENCES critere(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─── INSERTION DE DONNEES DE DEMONSTRATION ───

INSERT INTO certificat (norme, titre, version, statut, duree_validite, description, organisme, logo_url) VALUES
('ISO 27001', 'Management de la Sécurité de l\'Information', '2022', 'Actif', 36, 'Protégez vos données confidentielles.', 'AFNOR', NULL),
('ISO 9001', 'Management de la Qualité', '2015', 'Actif', 36, 'Améliorez la satisfaction de vos clients.', ' Bureau Veritas', NULL);

INSERT INTO critere (nom, categorie, description, moyen_preuve, est_obligatoire, difficulte, document_template) VALUES
('Politique de sécurité validée', 'Organisationnel', 'La politique doit être signée par le PDG.', 'Fichier PDF signé.', 1, 'Moyen', '#modele-politique'),
('Journalisation des serveurs', 'Technique', 'Conserver les logs 6 mois minimum.', 'Extrait des logs de production.', 1, 'Difficile', NULL),
('Enquête de satisfaction', 'Management', 'Recueillir l\'avis des clients.', 'Rapport annuel de satisfaction client.', 0, 'Facile', '#modele-enquete');

-- Liaison et attribution de poids SPÉCIFIQUE
-- Ex: "Politique de sécu" pèse 10 pour l'ISO 27001, mais l'enquête compte 0 (non liée).
INSERT INTO certificat_critere (certificat_id, critere_id, poids) VALUES
(1, 1, 8), -- ISO 27001 + Politique (Poids 8)
(1, 2, 10),-- ISO 27001 + Logs (Poids 10)
(2, 1, 3), -- ISO 9001 + Politique (Poids 3 - Moins vital ici)
(2, 3, 7); -- ISO 9001 + Enquête (Poids 7)
