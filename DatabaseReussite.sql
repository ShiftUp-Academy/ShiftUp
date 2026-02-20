CREATE TABLE `reussites` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `video_link` VARCHAR(255) NOT NULL,
    `type_action` ENUM(
        'lecon_terminee', 
        'etape_passee', 
        'seuil_points', 
        'reservation_evenement', 
        'temoignage_laisse', 
        'offre_achetee',
        'autre'
    ) NOT NULL,
    `valeur_requise` JSON NULL,
    `seuil_points` INT DEFAULT NULL,
    `points_recompense` INT DEFAULT 0,
    `est_actif` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_reussites` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `reussite_id` BIGINT UNSIGNED NOT NULL,
    `date_obtention` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_reussite_unique` (`user_id`, `reussite_id`),
    CONSTRAINT `fk_user_reussite_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_reussite_reussite` FOREIGN KEY (`reussite_id`) REFERENCES `reussites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_reussite_progressions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `reussite_id` BIGINT UNSIGNED NOT NULL,
    `progression_actuelle` INT DEFAULT 0,
    `progression_requise` INT NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_reussite_progression_unique` (`user_id`, `reussite_id`),
    CONSTRAINT `fk_progression_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_progression_reussite` FOREIGN KEY (`reussite_id`) REFERENCES `reussites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
