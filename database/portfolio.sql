CREATE DATABASE IF NOT EXISTS digitadvisory;
USE digitadvisory;

CREATE TABLE IF NOT EXISTS portfolio (
    id_portfolio INT AUTO_INCREMENT PRIMARY KEY,
    titre_portfolio VARCHAR(255) NOT NULL,
    description_portfolio TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS element_portfolio (
    id_element INT AUTO_INCREMENT PRIMARY KEY,
    id_portfolio INT NOT NULL,
    type_element ENUM('projet', 'competence') NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    niveau VARCHAR(50) NULL,
    statut VARCHAR(50) NULL,
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_portfolio FOREIGN KEY (id_portfolio) REFERENCES portfolio(id_portfolio) ON DELETE CASCADE
);
