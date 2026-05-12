CREATE DATABASE IF NOT EXISTS consulting_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE consulting_db;

CREATE TABLE IF NOT EXISTS missions (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    titre      VARCHAR(150)                              NOT NULL,
    date_debut DATE                                      NOT NULL,
    date_fin   DATE,
    statut     ENUM('En cours','Terminée','Suspendue')   NOT NULL DEFAULT 'En cours',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS livrables (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    mission_id  INT          NOT NULL,
    nom_fichier VARCHAR(200) NOT NULL,
    date_remise DATE         NOT NULL,
    etat        ENUM('En attente','Validé','Rejeté') NOT NULL DEFAULT 'En attente',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mission_id) REFERENCES missions(id) ON DELETE CASCADE
);

INSERT INTO missions (titre, date_debut, date_fin, statut) VALUES
  ('Audit Transformation Digitale – ClientA', '2024-01-10', '2024-06-30', 'Terminée'),
  ('Conseil RH – ClientB',                   '2024-03-01', NULL,          'En cours'),
  ('Migration Cloud – ClientC',              '2024-05-15', '2024-12-31', 'En cours');

INSERT INTO livrables (mission_id, nom_fichier, date_remise, etat) VALUES
  (1, 'rapport_audit_v1.pdf',   '2024-02-28', 'Validé'),
  (1, 'rapport_final.pdf',      '2024-06-25', 'Validé'),
  (2, 'plan_rh_draft.docx',     '2024-04-10', 'En attente'),
  (3, 'architecture_cloud.pdf', '2024-07-01', 'En attente');
