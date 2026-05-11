-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 11 mai 2026 à 03:18
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `digitadvisory`
--

-- --------------------------------------------------------

--
-- Structure de la table `element_portfolio`
--

CREATE TABLE `element_portfolio` (
  `id_element` int(11) NOT NULL,
  `id_portfolio` int(11) NOT NULL,
  `type_element` enum('skill','experience','certification') NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `niveau` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `element_portfolio`
--

INSERT INTO `element_portfolio` (`id_element`, `id_portfolio`, `type_element`, `titre`, `description`, `date_debut`, `date_fin`, `niveau`) VALUES
(1, 1, 'skill', 'PHP', NULL, NULL, NULL, 'expert'),
(2, 1, 'skill', 'MySQL', NULL, NULL, NULL, 'senior'),
(3, 1, 'skill', 'Docker', NULL, NULL, NULL, 'intermediate'),
(4, 1, 'experience', 'Tech Lead', 'Google France', NULL, NULL, NULL),
(5, 1, 'experience', 'Dev Senior', 'BNP Paribas', NULL, NULL, NULL),
(6, 1, 'certification', 'AWS Solutions Architect', 'Amazon Web Services', NULL, NULL, NULL),
(7, 1, 'certification', 'PMP', 'PMI', NULL, NULL, NULL),
(8, 3, 'certification', 'ISO 2700', 'BUREAU', NULL, NULL, NULL),
(9, 4, 'experience', 'vgyh', 'vgybh', '2026-05-06', '2026-05-29', NULL),
(10, 4, 'certification', 'iso', 'trccfvgbh', NULL, NULL, NULL),
(11, 5, 'skill', 'PHP', NULL, NULL, NULL, 'expert'),
(12, 5, 'skill', 'React', NULL, NULL, NULL, 'senior'),
(13, 5, 'skill', 'Docker', NULL, NULL, NULL, 'intermediate'),
(14, 5, 'skill', 'MySQL', NULL, NULL, NULL, 'expert'),
(15, 5, 'experience', 'Tech Lead', 'Google France', '2020-01-01', '2024-06-01', NULL),
(16, 5, 'experience', 'Senior Developer', 'BNP Paribas', '2016-03-01', '2019-12-31', NULL),
(17, 5, 'certification', 'AWS Solutions Architect', 'Amazon Web Services', NULL, NULL, NULL),
(18, 5, 'certification', 'PMP', 'PMI', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `portfolio`
--

CREATE TABLE `portfolio` (
  `id_portfolio` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 1,
  `full_name` varchar(255) NOT NULL,
  `professional_title` varchar(255) NOT NULL,
  `experience_level` enum('junior','mid','senior','expert') NOT NULL DEFAULT 'junior',
  `availability` enum('immediate','one_month','three_months','unavailable') NOT NULL DEFAULT 'immediate',
  `preferred_industry` varchar(150) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `portfolio`
--

INSERT INTO `portfolio` (`id_portfolio`, `user_id`, `full_name`, `professional_title`, `experience_level`, `availability`, `preferred_industry`, `location`, `bio`, `created_at`, `updated_at`) VALUES
(1, 1, 'Alice Martin', 'Consultante IT Senior', 'senior', 'immediate', 'IT', 'Paris, France', 'Experte en architecture logicielle et transformation digitale avec 10 ans d\'exp├®rience.', '2026-04-27 23:16:07', '2026-04-27 23:16:07'),
(2, 1, 'HAMDI', 'CONSULTANT', 'mid', 'immediate', 'IT', 'TUNIS', 'SEDRFTGHYNUJ?', '2026-04-27 23:24:40', '2026-04-27 23:24:40'),
(3, 1, 'HAMDI', 'CONSULTANT', 'junior', 'immediate', 'Finance', 'TUNIS', 'DVGBHNJSX?K', '2026-04-28 14:30:41', '2026-04-28 14:30:41'),
(4, 1, 'HAMDI', 'CONSULTANT', 'mid', 'unavailable', 'Energy', 'TUNIS', 'Consultant(e) CONSULTANT avec une expérience confirmée de plusieurs années, je combine expertise technique et vision stratégique pour accompagner les organisations dans leurs projets de transformation. Reconnu(e) pour ma capacité d&#039;adaptation et mon approche orientée résultats.', '2026-05-04 12:44:21', '2026-05-04 12:44:21'),
(5, 1, 'Jean Dupont', 'Architecte Logiciel Senior', 'expert', 'immediate', 'IT', 'Paris, France', 'Expert en architecture logicielle avec 12+ années d&#039;expérience dans des environnements grands comptes.', '2026-05-04 13:27:44', '2026-05-04 13:27:44');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `element_portfolio`
--
ALTER TABLE `element_portfolio`
  ADD PRIMARY KEY (`id_element`),
  ADD KEY `fk_element_portfolio` (`id_portfolio`);

--
-- Index pour la table `portfolio`
--
ALTER TABLE `portfolio`
  ADD PRIMARY KEY (`id_portfolio`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `element_portfolio`
--
ALTER TABLE `element_portfolio`
  MODIFY `id_element` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `portfolio`
--
ALTER TABLE `portfolio`
  MODIFY `id_portfolio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `element_portfolio`
--
ALTER TABLE `element_portfolio`
  ADD CONSTRAINT `fk_element_portfolio` FOREIGN KEY (`id_portfolio`) REFERENCES `portfolio` (`id_portfolio`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
