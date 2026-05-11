-- Module Messagerie : tables conversation et message
-- À exécuter dans la base de données digitadvisory

CREATE TABLE IF NOT EXISTS `conversation` (
  `id_conversation` INT(11) NOT NULL AUTO_INCREMENT,
  `id_user1`        INT(11) NOT NULL,
  `id_user2`        INT(11) NOT NULL,
  `deleted_by1`     TINYINT(1) NOT NULL DEFAULT 0,
  `deleted_by2`     TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_conversation`),
  UNIQUE KEY `unique_conv` (`id_user1`, `id_user2`),
  FOREIGN KEY (`id_user1`) REFERENCES `user`(`id_user`) ON DELETE CASCADE,
  FOREIGN KEY (`id_user2`) REFERENCES `user`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `message` (
  `id_message`      INT(11) NOT NULL AUTO_INCREMENT,
  `id_conversation` INT(11) NOT NULL,
  `id_sender`       INT(11) NOT NULL,
  `type`            ENUM('text','file','audio') NOT NULL DEFAULT 'text',
  `content`         TEXT DEFAULT NULL,
  `file_path`       VARCHAR(500) DEFAULT NULL,
  `file_name`       VARCHAR(255) DEFAULT NULL,
  `file_size`       INT(11) DEFAULT NULL,
  `is_read`         TINYINT(1) NOT NULL DEFAULT 0,
  `is_edited`       TINYINT(1) NOT NULL DEFAULT 0,
  `is_deleted`      TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_message`),
  KEY `idx_conversation` (`id_conversation`),
  FOREIGN KEY (`id_conversation`) REFERENCES `conversation`(`id_conversation`) ON DELETE CASCADE,
  FOREIGN KEY (`id_sender`)       REFERENCES `user`(`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
