-- ═══════════════════════════════════════════════════════════════
-- SEED RICHE — DigitAdvisory
-- Exécuter dans phpMyAdmin après le schema.sql
-- Vide les tables existantes puis insère des données complètes
-- ═══════════════════════════════════════════════════════════════

USE digitadvisory;

-- Nettoyer les données existantes (dans le bon ordre pour les FK)
DELETE FROM certificat_critere;
DELETE FROM critere;
DELETE FROM certificat;

-- Reset des auto-increment
ALTER TABLE certificat AUTO_INCREMENT = 1;
ALTER TABLE critere AUTO_INCREMENT = 1;

-- ═══════════════════════════════════════════════════════════════
-- 1. CERTIFICATIONS (6 normes ISO réalistes)
-- ═══════════════════════════════════════════════════════════════
INSERT INTO certificat (id, norme, titre, version, statut, duree_validite, description, organisme) VALUES
(1, 'ISO 27001', 'Sécurité de l\'Information', '2022', 'Actif', 36,
 'Cadre international pour protéger les actifs informationnels. Couvre la gestion des risques, le contrôle d\'accès, la cryptographie et la continuité d\'activité.',
 'AFNOR Certification'),

(2, 'ISO 9001', 'Management de la Qualité', '2015', 'Actif', 36,
 'Norme de référence mondiale pour garantir la qualité des produits et services. Vise l\'amélioration continue et la satisfaction client.',
 'Bureau Veritas'),

(3, 'ISO 14001', 'Management Environnemental', '2015', 'Actif', 36,
 'Système de management pour maîtriser l\'impact environnemental de l\'entreprise. Réduction des déchets, efficacité énergétique et conformité réglementaire.',
 'SGS Certification'),

(4, 'ISO 45001', 'Santé et Sécurité au Travail', '2018', 'Actif', 36,
 'Prévention des accidents du travail et des maladies professionnelles. Engagement de la direction et participation des salariés.',
 'DEKRA Certification'),

(5, 'ISO 22000', 'Sécurité des Denrées Alimentaires', '2018', 'Actif', 24,
 'Garantir la sécurité alimentaire tout au long de la chaîne de production. Système HACCP intégré et traçabilité complète.',
 'Bureau Veritas'),

(6, 'ISO 50001', 'Management de l\'Énergie', '2018', 'Actif', 36,
 'Optimiser la performance énergétique et réduire les coûts. Contribue aux objectifs de développement durable et à la réduction de l\'empreinte carbone.',
 'TÜV Rheinland');

-- ═══════════════════════════════════════════════════════════════
-- 2. CRITÈRES D'ÉVALUATION (25 critères variés)
-- ═══════════════════════════════════════════════════════════════

-- ─── Critères Organisationnels (1-6) ───
INSERT INTO critere (id, nom, categorie, description, moyen_preuve, est_obligatoire, difficulte) VALUES
(1, 'Politique de sécurité de l\'information',
 'Organisationnel',
 'Document formel définissant les objectifs, le périmètre et les engagements de la direction en matière de sécurité de l\'information.',
 'Document PDF signé par la direction générale, daté et diffusé à l\'ensemble du personnel.',
 1, 'Moyen'),

(2, 'Analyse des risques documentée',
 'Organisationnel',
 'Identification, évaluation et traitement des risques liés aux actifs informationnels selon une méthodologie reconnue (EBIOS, ISO 27005).',
 'Registre des risques avec cotation (probabilité x impact), plan de traitement et acceptation des risques résiduels.',
 1, 'Difficile'),

(3, 'Engagement de la direction',
 'Organisationnel',
 'La direction doit démontrer son leadership et son engagement envers le système de management.',
 'Compte-rendu de revue de direction, politique signée, allocation budgétaire dédiée.',
 1, 'Facile'),

(4, 'Politique qualité documentée',
 'Organisationnel',
 'Déclaration formelle des intentions et orientations de l\'organisme en matière de qualité.',
 'Document de politique qualité affiché et accessible, révisé annuellement.',
 1, 'Facile'),

(5, 'Politique environnementale',
 'Organisationnel',
 'Engagement documenté de l\'entreprise envers la protection de l\'environnement et la prévention de la pollution.',
 'Politique signée par le PDG, communiquée aux parties intéressées.',
 1, 'Facile'),

(6, 'Politique SST (Santé Sécurité Travail)',
 'Organisationnel',
 'Engagement de la direction pour la prévention des blessures et des atteintes à la santé des travailleurs.',
 'Politique SST signée, affichée dans les locaux et présentée aux nouveaux arrivants.',
 1, 'Facile');

-- ─── Critères Techniques (7-13) ───
INSERT INTO critere (id, nom, categorie, description, moyen_preuve, est_obligatoire, difficulte) VALUES
(7, 'Contrôle d\'accès et authentification',
 'Technique',
 'Gestion des droits d\'accès aux systèmes d\'information. Authentification multi-facteur pour les accès sensibles.',
 'Matrice des droits d\'accès, politique de mots de passe, logs d\'authentification MFA.',
 1, 'Difficile'),

(8, 'Journalisation et surveillance',
 'Technique',
 'Enregistrement et conservation des événements de sécurité. Surveillance proactive des anomalies.',
 'Extrait des logs serveurs (6 mois minimum), rapports SIEM, procédure d\'alerte.',
 1, 'Difficile'),

(9, 'Chiffrement des données sensibles',
 'Technique',
 'Protection cryptographique des données au repos et en transit selon les standards actuels (AES-256, TLS 1.3).',
 'Configuration SSL/TLS des serveurs, politique de gestion des clés cryptographiques.',
 1, 'Difficile'),

(10, 'Sauvegardes et plan de reprise',
 'Technique',
 'Stratégie de sauvegarde 3-2-1, tests de restauration réguliers et plan de reprise d\'activité (PRA).',
 'Rapport de test de restauration (trimestriel), procédure PRA documentée et testée.',
 1, 'Moyen'),

(11, 'Gestion des équipements de protection',
 'Technique',
 'Fourniture, entretien et contrôle des EPI (Équipements de Protection Individuelle) adaptés aux risques.',
 'Registre de distribution des EPI, fiches de contrôle périodique, attestations de formation.',
 1, 'Moyen'),

(12, 'Système HACCP opérationnel',
 'Technique',
 'Mise en place des 7 principes HACCP : analyse des dangers, points critiques, limites, surveillance, actions correctives.',
 'Diagramme de flux, arbre de décision CCP, registres de surveillance des points critiques.',
 1, 'Difficile'),

(13, 'Compteurs et monitoring énergétique',
 'Technique',
 'Installation de compteurs intelligents sur les postes de consommation significatifs. Tableau de bord énergétique en temps réel.',
 'Relevés mensuels des compteurs, dashboard de suivi énergétique, factures annotées.',
 1, 'Moyen');

-- ─── Critères Management / Processus (14-19) ───
INSERT INTO critere (id, nom, categorie, description, moyen_preuve, est_obligatoire, difficulte) VALUES
(14, 'Enquête de satisfaction client',
 'Management',
 'Dispositif structuré de recueil et d\'analyse de la satisfaction des clients.',
 'Questionnaire de satisfaction, rapport d\'analyse annuel, plan d\'actions correctives.',
 0, 'Facile'),

(15, 'Audit interne planifié',
 'Management',
 'Programme d\'audit interne couvrant l\'ensemble du périmètre du système de management, au moins une fois par an.',
 'Planning d\'audit, rapports d\'audit interne, fiches de non-conformité et suivi.',
 1, 'Moyen'),

(16, 'Revue de direction annuelle',
 'Management',
 'Réunion annuelle de la direction pour évaluer la performance du système de management et décider des axes d\'amélioration.',
 'Compte-rendu de revue de direction signé, avec indicateurs de performance et décisions.',
 1, 'Moyen'),

(17, 'Gestion des non-conformités',
 'Management',
 'Processus documenté pour identifier, enregistrer, analyser et traiter les non-conformités avec actions correctives.',
 'Registre des non-conformités, analyse des causes racines (5 pourquoi, Ishikawa), preuves de clôture.',
 1, 'Moyen'),

(18, 'Évaluation des fournisseurs',
 'Management',
 'Système d\'évaluation et de sélection des fournisseurs critiques basé sur des critères objectifs.',
 'Grille d\'évaluation fournisseurs, tableau de suivi, audits fournisseurs si applicable.',
 0, 'Facile'),

(19, 'Indicateurs de performance (KPI)',
 'Management',
 'Tableau de bord avec des indicateurs mesurables alignés sur les objectifs du système de management.',
 'Dashboard KPI à jour, revues mensuelles des indicateurs, tendances sur 12 mois.',
 1, 'Moyen');

-- ─── Critères Formation / RH (20-22) ───
INSERT INTO critere (id, nom, categorie, description, moyen_preuve, est_obligatoire, difficulte) VALUES
(20, 'Plan de formation et sensibilisation',
 'Formation',
 'Programme de formation annuel couvrant les compétences requises pour le système de management.',
 'Plan de formation signé, attestations de présence, évaluations post-formation.',
 1, 'Facile'),

(21, 'Sensibilisation à la cybersécurité',
 'Formation',
 'Sessions régulières de sensibilisation aux menaces (phishing, ingénierie sociale, bonnes pratiques).',
 'Supports de formation, résultats de tests de phishing simulé, registre de participation.',
 0, 'Facile'),

(22, 'Formation aux gestes de premiers secours',
 'Formation',
 'Au moins un sauveteur secouriste du travail (SST) formé par tranche de 20 salariés.',
 'Certificats SST valides, registre des formations, exercices pratiques documentés.',
 1, 'Moyen');

-- ─── Critères Conformité / Réglementaire (23-25) ───
INSERT INTO critere (id, nom, categorie, description, moyen_preuve, est_obligatoire, difficulte) VALUES
(23, 'Conformité RGPD',
 'Conformité',
 'Respect du Règlement Général sur la Protection des Données : registre de traitements, DPO, consentement.',
 'Registre des traitements, analyse d\'impact (PIA), procédure de notification de violation.',
 1, 'Difficile'),

(24, 'Veille réglementaire active',
 'Conformité',
 'Processus de veille pour identifier et intégrer les nouvelles exigences légales et réglementaires applicables.',
 'Tableau de veille réglementaire à jour, preuve de mise en conformité, responsable identifié.',
 1, 'Moyen'),

(25, 'Traçabilité complète de la chaîne',
 'Conformité',
 'Capacité à retracer l\'historique, l\'utilisation et la localisation d\'un produit ou lot à chaque étape de la supply chain.',
 'Système de traçabilité (code-barres/RFID), registres de réception et d\'expédition, test de recall.',
 1, 'Difficile');


-- ═══════════════════════════════════════════════════════════════
-- 3. LIAISONS CERTIFICAT ↔ CRITÈRE (avec poids réalistes)
-- ═══════════════════════════════════════════════════════════════

-- ─── ISO 27001 : Sécurité de l'Information (ID=1) ───
-- Focus: Sécurité, technique, données
INSERT INTO certificat_critere (certificat_id, critere_id, poids) VALUES
(1, 1, 10),  -- Politique de sécurité (fondamental)
(1, 2, 12),  -- Analyse des risques (pilier central)
(1, 3, 5),   -- Engagement direction
(1, 7, 10),  -- Contrôle d'accès
(1, 8, 9),   -- Journalisation
(1, 9, 8),   -- Chiffrement
(1, 10, 7),  -- Sauvegardes
(1, 15, 6),  -- Audit interne
(1, 16, 4),  -- Revue de direction
(1, 20, 4),  -- Plan de formation
(1, 21, 5),  -- Sensibilisation cyber
(1, 23, 8);  -- Conformité RGPD

-- ─── ISO 9001 : Management de la Qualité (ID=2) ───
-- Focus: Qualité, satisfaction client, amélioration continue
INSERT INTO certificat_critere (certificat_id, critere_id, poids) VALUES
(2, 3, 7),   -- Engagement direction
(2, 4, 10),  -- Politique qualité
(2, 14, 9),  -- Enquête satisfaction
(2, 15, 8),  -- Audit interne
(2, 16, 7),  -- Revue de direction
(2, 17, 9),  -- Gestion non-conformités
(2, 18, 6),  -- Évaluation fournisseurs
(2, 19, 8),  -- KPI
(2, 20, 5),  -- Plan de formation
(2, 24, 4);  -- Veille réglementaire

-- ─── ISO 14001 : Management Environnemental (ID=3) ───
-- Focus: Environnement, conformité, impact
INSERT INTO certificat_critere (certificat_id, critere_id, poids) VALUES
(3, 3, 6),   -- Engagement direction
(3, 5, 10),  -- Politique environnementale
(3, 13, 7),  -- Monitoring énergétique
(3, 15, 7),  -- Audit interne
(3, 16, 5),  -- Revue de direction
(3, 17, 6),  -- Gestion non-conformités
(3, 19, 7),  -- KPI
(3, 20, 4),  -- Plan de formation
(3, 24, 8);  -- Veille réglementaire

-- ─── ISO 45001 : Santé et Sécurité au Travail (ID=4) ───
-- Focus: Sécurité des personnes, EPI, formation
INSERT INTO certificat_critere (certificat_id, critere_id, poids) VALUES
(4, 2, 8),   -- Analyse des risques (risques pro)
(4, 3, 6),   -- Engagement direction
(4, 6, 10),  -- Politique SST
(4, 11, 9),  -- Gestion EPI
(4, 15, 7),  -- Audit interne
(4, 16, 5),  -- Revue de direction
(4, 17, 8),  -- Gestion non-conformités
(4, 19, 6),  -- KPI
(4, 20, 5),  -- Plan de formation
(4, 22, 9),  -- Premiers secours
(4, 24, 6);  -- Veille réglementaire

-- ─── ISO 22000 : Sécurité Alimentaire (ID=5) ───
-- Focus: HACCP, traçabilité, hygiène
INSERT INTO certificat_critere (certificat_id, critere_id, poids) VALUES
(5, 3, 5),   -- Engagement direction
(5, 12, 12), -- Système HACCP (pilier)
(5, 15, 7),  -- Audit interne
(5, 16, 5),  -- Revue de direction
(5, 17, 8),  -- Gestion non-conformités
(5, 18, 7),  -- Évaluation fournisseurs
(5, 19, 6),  -- KPI
(5, 20, 5),  -- Plan de formation
(5, 24, 6),  -- Veille réglementaire
(5, 25, 11); -- Traçabilité (critique)

-- ─── ISO 50001 : Management de l'Énergie (ID=6) ───
-- Focus: Énergie, performance, monitoring
INSERT INTO certificat_critere (certificat_id, critere_id, poids) VALUES
(6, 3, 6),   -- Engagement direction
(6, 5, 7),   -- Politique environnementale (lien)
(6, 13, 12), -- Monitoring énergétique (pilier)
(6, 15, 6),  -- Audit interne
(6, 16, 5),  -- Revue de direction
(6, 19, 10), -- KPI (performance énergétique)
(6, 20, 4),  -- Plan de formation
(6, 24, 5);  -- Veille réglementaire

-- ═══════════════════════════════════════════════════════════════
-- RÉSUMÉ :
-- • 6 certifications ISO (27001, 9001, 14001, 45001, 22000, 50001)
-- • 25 critères répartis en 5 catégories
-- • 53 liaisons certificat↔critère avec poids réalistes
-- ═══════════════════════════════════════════════════════════════
