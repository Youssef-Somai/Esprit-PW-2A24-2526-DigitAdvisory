-- Base combinée DigitAdvisory
-- Tables: user, quiz, question + certificat, critere, certificat_critere

CREATE DATABASE IF NOT EXISTS digitadvisory
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE digitadvisory;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS certificat_critere;
DROP TABLE IF EXISTS question;
DROP TABLE IF EXISTS critere;
DROP TABLE IF EXISTS certificat;
DROP TABLE IF EXISTS quiz;
DROP TABLE IF EXISTS user;
SET FOREIGN_KEY_CHECKS = 1;


CREATE TABLE user (
  id_user int(11) NOT NULL AUTO_INCREMENT,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  role enum('expert','entreprise') NOT NULL,
  statut_compte enum('actif','bloque','en_attente') NOT NULL DEFAULT 'actif',
  nom_entreprise varchar(255) DEFAULT NULL,
  secteur_activite varchar(255) DEFAULT NULL,
  adresse text DEFAULT NULL,
  telephone varchar(30) DEFAULT NULL,
  nom varchar(100) DEFAULT NULL,
  prenom varchar(100) DEFAULT NULL,
  domaine varchar(255) DEFAULT NULL,
  niveau_experience varchar(100) DEFAULT NULL,
  tarif_journalier decimal(10,2) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  reset_token varchar(255) DEFAULT NULL,
  reset_token_expires datetime DEFAULT NULL,
  login_attempts int(11) NOT NULL DEFAULT 0,
  locked_until datetime DEFAULT NULL,
  face_descriptor longtext DEFAULT NULL,
  PRIMARY KEY (id_user),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE quiz (
  id_quiz int(11) NOT NULL AUTO_INCREMENT,
  titre varchar(255) NOT NULL,
  description text NOT NULL,
  image varchar(255) NOT NULL,
  date_creation datetime NOT NULL,
  PRIMARY KEY (id_quiz)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE question (
  id_question int(11) NOT NULL AUTO_INCREMENT,
  id_quiz int(11) NOT NULL,
  question text NOT NULL,
  choix1 varchar(255) NOT NULL,
  choix2 varchar(255) NOT NULL,
  choix3 varchar(255) NOT NULL,
  choix4 varchar(255) NOT NULL,
  bonne_reponse int(11) NOT NULL,
  PRIMARY KEY (id_question),
  KEY id_quiz (id_quiz),
  CONSTRAINT question_ibfk_1
    FOREIGN KEY (id_quiz)
    REFERENCES quiz (id_quiz)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Tables certification importées depuis le fichier SQL
CREATE TABLE IF NOT EXISTS `certificat` (
  `id` int(11) NOT NULL,
  `norme` varchar(100) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `version` varchar(50) DEFAULT '2022',
  `statut` varchar(50) DEFAULT 'Actif',
  `duree_validite` int(11) DEFAULT 36,
  `description` text DEFAULT NULL,
  `organisme` varchar(255) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT IGNORE INTO `certificat` (`id`, `norme`, `titre`, `version`, `statut`, `duree_validite`, `description`, `organisme`, `logo_url`, `date_ajout`) VALUES
(1, 'ISO 27001', 'Sécurité de l\'Information', '2022', 'Actif', 36, 'Cadre international pour protéger les actifs informationnels. Couvre la gestion des risques, le contrôle d\'accès, la cryptographie et la continuité d\'activité.', 'AFNOR Certification', NULL, '2026-05-05 04:16:12'),
(2, 'ISO 9001', 'Management de la Qualité', '2015', 'Actif', 36, 'Norme de référence mondiale pour garantir la qualité des produits et services. Vise l\'amélioration continue et la satisfaction client.', 'Bureau Veritas', NULL, '2026-05-05 04:16:12'),
(3, 'ISO 14001', 'Management Environnemental', '2015', 'Actif', 36, 'Système de management pour maîtriser l\'impact environnemental de l\'entreprise. Réduction des déchets, efficacité énergétique et conformité réglementaire.', 'SGS Certification', NULL, '2026-05-05 04:16:12'),
(4, 'ISO 45001', 'Santé et Sécurité au Travail', '2018', 'Actif', 36, 'Prévention des accidents du travail et des maladies professionnelles. Engagement de la direction et participation des salariés.', 'DEKRA Certification', NULL, '2026-05-05 04:16:12'),
(5, 'ISO 22000', 'Sécurité des Denrées Alimentaires', '2018', 'Actif', 24, 'Garantir la sécurité alimentaire tout au long de la chaîne de production. Système HACCP intégré et traçabilité complète.', 'Bureau Veritas', NULL, '2026-05-05 04:16:12'),
(6, 'ISO 50001', 'Management de l\'Énergie', '2018', 'Actif', 36, 'Optimiser la performance énergétique et réduire les coûts. Contribue aux objectifs de développement durable et à la réduction de l\'empreinte carbone.', 'TÜV Rheinland', NULL, '2026-05-05 04:16:12');
CREATE TABLE IF NOT EXISTS `certificat_critere` (
  `certificat_id` int(11) NOT NULL,
  `critere_id` int(11) NOT NULL,
  `poids` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT IGNORE INTO `certificat_critere` (`certificat_id`, `critere_id`, `poids`) VALUES
(1, 1, 10),
(1, 2, 12),
(1, 3, 5),
(1, 7, 10),
(1, 8, 9),
(1, 9, 8),
(1, 10, 7),
(1, 15, 6),
(1, 16, 4),
(1, 20, 4),
(1, 21, 5),
(1, 23, 8),
(2, 3, 7),
(2, 4, 10),
(2, 14, 9),
(2, 15, 8),
(2, 16, 7),
(2, 17, 9),
(2, 18, 6),
(2, 19, 8),
(2, 20, 5),
(2, 24, 4),
(3, 3, 6),
(3, 5, 10),
(3, 13, 7),
(3, 15, 7),
(3, 16, 5),
(3, 17, 6),
(3, 19, 7),
(3, 20, 4),
(3, 24, 8),
(4, 2, 8),
(4, 3, 6),
(4, 6, 10),
(4, 11, 9),
(4, 15, 7),
(4, 16, 5),
(4, 17, 8),
(4, 19, 6),
(4, 20, 5),
(4, 22, 9),
(4, 24, 6),
(5, 3, 5),
(5, 12, 12),
(5, 15, 7),
(5, 16, 5),
(5, 17, 8),
(5, 18, 7),
(5, 19, 6),
(5, 20, 5),
(5, 24, 6),
(5, 25, 11),
(6, 3, 6),
(6, 5, 7),
(6, 13, 12),
(6, 15, 6),
(6, 16, 5),
(6, 19, 10),
(6, 20, 4),
(6, 24, 5);
CREATE TABLE IF NOT EXISTS `critere` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `categorie` varchar(255) DEFAULT 'Général',
  `description` text DEFAULT NULL,
  `moyen_preuve` text DEFAULT NULL,
  `est_obligatoire` tinyint(1) DEFAULT 1,
  `difficulte` varchar(50) DEFAULT 'Moyen',
  `document_template` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT IGNORE INTO `critere` (`id`, `nom`, `categorie`, `description`, `moyen_preuve`, `est_obligatoire`, `difficulte`, `document_template`) VALUES
(1, 'Politique de sécurité de l\'information', 'Organisationnel', 'Document formel définissant les objectifs, le périmètre et les engagements de la direction en matière de sécurité de l\'information.', 'Document PDF signé par la direction générale, daté et diffusé à l\'ensemble du personnel.', 1, 'Moyen', NULL),
(2, 'Analyse des risques documentée', 'Organisationnel', 'Identification, évaluation et traitement des risques liés aux actifs informationnels selon une méthodologie reconnue (EBIOS, ISO 27005).', 'Registre des risques avec cotation (probabilité x impact), plan de traitement et acceptation des risques résiduels.', 1, 'Difficile', NULL),
(3, 'Engagement de la direction', 'Organisationnel', 'La direction doit démontrer son leadership et son engagement envers le système de management.', 'Compte-rendu de revue de direction, politique signée, allocation budgétaire dédiée.', 1, 'Facile', NULL),
(4, 'Politique qualité documentée', 'Organisationnel', 'Déclaration formelle des intentions et orientations de l\'organisme en matière de qualité.', 'Document de politique qualité affiché et accessible, révisé annuellement.', 1, 'Facile', NULL),
(5, 'Politique environnementale', 'Organisationnel', 'Engagement documenté de l\'entreprise envers la protection de l\'environnement et la prévention de la pollution.', 'Politique signée par le PDG, communiquée aux parties intéressées.', 1, 'Facile', NULL),
(6, 'Politique SST (Santé Sécurité Travail)', 'Organisationnel', 'Engagement de la direction pour la prévention des blessures et des atteintes à la santé des travailleurs.', 'Politique SST signée, affichée dans les locaux et présentée aux nouveaux arrivants.', 1, 'Facile', NULL),
(7, 'Contrôle d\'accès et authentification', 'Technique', 'Gestion des droits d\'accès aux systèmes d\'information. Authentification multi-facteur pour les accès sensibles.', 'Matrice des droits d\'accès, politique de mots de passe, logs d\'authentification MFA.', 1, 'Difficile', NULL),
(8, 'Journalisation et surveillance', 'Technique', 'Enregistrement et conservation des événements de sécurité. Surveillance proactive des anomalies.', 'Extrait des logs serveurs (6 mois minimum), rapports SIEM, procédure d\'alerte.', 1, 'Difficile', NULL),
(9, 'Chiffrement des données sensibles', 'Technique', 'Protection cryptographique des données au repos et en transit selon les standards actuels (AES-256, TLS 1.3).', 'Configuration SSL/TLS des serveurs, politique de gestion des clés cryptographiques.', 1, 'Difficile', NULL),
(10, 'Sauvegardes et plan de reprise', 'Technique', 'Stratégie de sauvegarde 3-2-1, tests de restauration réguliers et plan de reprise d\'activité (PRA).', 'Rapport de test de restauration (trimestriel), procédure PRA documentée et testée.', 1, 'Moyen', NULL),
(11, 'Gestion des équipements de protection', 'Technique', 'Fourniture, entretien et contrôle des EPI (Équipements de Protection Individuelle) adaptés aux risques.', 'Registre de distribution des EPI, fiches de contrôle périodique, attestations de formation.', 1, 'Moyen', NULL),
(12, 'Système HACCP opérationnel', 'Technique', 'Mise en place des 7 principes HACCP : analyse des dangers, points critiques, limites, surveillance, actions correctives.', 'Diagramme de flux, arbre de décision CCP, registres de surveillance des points critiques.', 1, 'Difficile', NULL),
(13, 'Compteurs et monitoring énergétique', 'Technique', 'Installation de compteurs intelligents sur les postes de consommation significatifs. Tableau de bord énergétique en temps réel.', 'Relevés mensuels des compteurs, dashboard de suivi énergétique, factures annotées.', 1, 'Moyen', NULL),
(14, 'Enquête de satisfaction client', 'Management', 'Dispositif structuré de recueil et d\'analyse de la satisfaction des clients.', 'Questionnaire de satisfaction, rapport d\'analyse annuel, plan d\'actions correctives.', 0, 'Facile', NULL),
(15, 'Audit interne planifié', 'Management', 'Programme d\'audit interne couvrant l\'ensemble du périmètre du système de management, au moins une fois par an.', 'Planning d\'audit, rapports d\'audit interne, fiches de non-conformité et suivi.', 1, 'Moyen', NULL),
(16, 'Revue de direction annuelle', 'Management', 'Réunion annuelle de la direction pour évaluer la performance du système de management et décider des axes d\'amélioration.', 'Compte-rendu de revue de direction signé, avec indicateurs de performance et décisions.', 1, 'Moyen', NULL),
(17, 'Gestion des non-conformités', 'Management', 'Processus documenté pour identifier, enregistrer, analyser et traiter les non-conformités avec actions correctives.', 'Registre des non-conformités, analyse des causes racines (5 pourquoi, Ishikawa), preuves de clôture.', 1, 'Moyen', NULL),
(18, 'Évaluation des fournisseurs', 'Management', 'Système d\'évaluation et de sélection des fournisseurs critiques basé sur des critères objectifs.', 'Grille d\'évaluation fournisseurs, tableau de suivi, audits fournisseurs si applicable.', 0, 'Facile', NULL),
(19, 'Indicateurs de performance (KPI)', 'Management', 'Tableau de bord avec des indicateurs mesurables alignés sur les objectifs du système de management.', 'Dashboard KPI à jour, revues mensuelles des indicateurs, tendances sur 12 mois.', 1, 'Moyen', NULL),
(20, 'Plan de formation et sensibilisation', 'Formation', 'Programme de formation annuel couvrant les compétences requises pour le système de management.', 'Plan de formation signé, attestations de présence, évaluations post-formation.', 1, 'Facile', NULL),
(21, 'Sensibilisation à la cybersécurité', 'Formation', 'Sessions régulières de sensibilisation aux menaces (phishing, ingénierie sociale, bonnes pratiques).', 'Supports de formation, résultats de tests de phishing simulé, registre de participation.', 0, 'Facile', NULL),
(22, 'Formation aux gestes de premiers secours', 'Formation', 'Au moins un sauveteur secouriste du travail (SST) formé par tranche de 20 salariés.', 'Certificats SST valides, registre des formations, exercices pratiques documentés.', 1, 'Moyen', NULL),
(23, 'Conformité RGPD', 'Conformité', 'Respect du Règlement Général sur la Protection des Données : registre de traitements, DPO, consentement.', 'Registre des traitements, analyse d\'impact (PIA), procédure de notification de violation.', 1, 'Difficile', NULL),
(24, 'Veille réglementaire active', 'Conformité', 'Processus de veille pour identifier et intégrer les nouvelles exigences légales et réglementaires applicables.', 'Tableau de veille réglementaire à jour, preuve de mise en conformité, responsable identifié.', 1, 'Moyen', NULL),
(25, 'Traçabilité complète de la chaîne', 'Organisationnel', 'Capacité à retracer l\'historique, l\'utilisation et la localisation d\'un produit ou lot à chaque étape de la supply chain.', 'Système de traçabilité (code-barres/RFID), registres de réception et d\'expédition, test de recall.', 1, 'Difficile', '../../uploads/templates/1777977488_Modele_ISO_Critere_25.docx');
ALTER TABLE `certificat`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `certificat_critere`
  ADD PRIMARY KEY (`certificat_id`,`critere_id`),
  ADD KEY `fk_critere_join` (`critere_id`);
ALTER TABLE `critere`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `certificat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `critere`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
ALTER TABLE `certificat_critere`
  ADD CONSTRAINT `fk_certificat_join` FOREIGN KEY (`certificat_id`) REFERENCES `certificat` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_critere_join` FOREIGN KEY (`critere_id`) REFERENCES `critere` (`id`) ON DELETE CASCADE;
