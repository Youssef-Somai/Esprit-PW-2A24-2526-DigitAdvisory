-- ============================================
-- Base de données : digitadvisory
-- Module : Certificat (Certifications ISO)
-- ============================================

CREATE DATABASE IF NOT EXISTS digitadvisory;
USE digitadvisory;

-- Table certificat
CREATE TABLE IF NOT EXISTS certificat (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    norme       VARCHAR(50)   NOT NULL,
    titre       VARCHAR(255)  NOT NULL,
    description TEXT          NULL,
    organisme   VARCHAR(255)  NULL,
    date_ajout  DATE          NOT NULL DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données initiales
INSERT INTO certificat (norme, titre, description, organisme) VALUES
('ISO 27001', 'Sécurité de l\'Information',        'Management de la sécurité de l\'information. Protection des données sensibles et gestion des risques IT.',  'ISO/IEC'),
('ISO 9001',  'Management de la Qualité',           'Système de management de la qualité. Amélioration continue des processus internes.',                        'ISO'),
('ISO 14001', 'Management Environnemental',          'Système de management environnemental pour réduire l\'impact sur l\'environnement.',                        'ISO'),
('ISO 45001', 'Santé et Sécurité au Travail',        'Système de management de la santé et de la sécurité au travail.',                                          'ISO');
