-- Messagerie v2 : réactions et statut utilisateur
-- À exécuter APRÈS messagerie.sql

CREATE TABLE IF NOT EXISTS `message_reaction` (
  `id_reaction` INT(11) NOT NULL AUTO_INCREMENT,
  `id_message`  INT(11) NOT NULL,
  `id_user`     INT(11) NOT NULL,
  `emoji`       VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reaction`),
  UNIQUE KEY `unique_reaction` (`id_message`, `id_user`, `emoji`),
  FOREIGN KEY (`id_message`) REFERENCES `message`(`id_message`) ON DELETE CASCADE,
  FOREIGN KEY (`id_user`)    REFERENCES `user`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_status` (
  `id_user`   INT(11) NOT NULL,
  `last_seen` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `typing_in` INT(11) DEFAULT NULL,
  `typing_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  FOREIGN KEY (`id_user`) REFERENCES `user`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
