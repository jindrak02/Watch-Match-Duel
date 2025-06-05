CREATE TABLE `users` (
  `user_id` CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  `username` varchar(255),
  `password` varchar(255),
  `email` varchar(255),
  `is_guest` bool,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `movies_and_series` (
  `content_id` integer PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `type` ENUM('movie', 'series'),
  'description' TEXT,
  'poster_url' VARCHAR(255),
  'release_date' DATE,
  'vote_average' DECIMAL(5,3),
  'vote_count' INT UNSIGNED,
  'popularity' DECIMAL(8,4),
  'tmdb_id' INT,
  'original_language' VARCHAR(10),
  `imdb_url` varchar(255),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `genres` (
  `id` integer PRIMARY KEY, -- TMDB genre ID
  `genre_name` varchar(255)
);

CREATE TABLE `content_genres` (
  `content_id` integer,
  `genre_id` integer
);

CREATE TABLE `sessions` (
  `session_id` CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  `code_to_connect` varchar(255) NOT NULL,
  `type` ENUM('movie', 'series', 'both') NOT NULL,
  `items_in_duel_count` INT NOT NULL,
  'expected_user_count' TINYINT DEFAULT 2,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `session_genres` (
  `session_id` CHAR(36),
  `genre_id` INT,
  PRIMARY KEY (`session_id`, `genre_id`),
  FOREIGN KEY (`session_id`) REFERENCES `sessions`(`session_id`) ON DELETE CASCADE,
  FOREIGN KEY (`genre_id`) REFERENCES `genres`(`id`) ON DELETE CASCADE
);

CREATE TABLE `session_users` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `session_id` CHAR(36),
  `user_id` CHAR(36)
);

CREATE TABLE session_content(
	session_id CHAR(36),
  content_id INT,
  FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE,
  FOREIGN KEY (content_id) REFERENCES movies_and_series(content_id) ON DELETE CASCADE
);

CREATE TABLE `ratings` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `session_id` CHAR(36),
  `user_id` CHAR(36),
  `content_id` integer,
  `rating` tinyint NOT NULL
);

ALTER TABLE `content_genres` ADD FOREIGN KEY (`content_id`) REFERENCES `movies_and_series` (`content_id`) ON DELETE CASCADE;

ALTER TABLE `content_genres` ADD FOREIGN KEY (`genre_id`) REFERENCES `genres` (`id`) ON DELETE CASCADE;

ALTER TABLE `session_users` ADD FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE;

ALTER TABLE `session_users` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `ratings` ADD FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE;

ALTER TABLE `ratings` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

ALTER TABLE `ratings` ADD FOREIGN KEY (`content_id`) REFERENCES `movies_and_series` (`content_id`) ON DELETE CASCADE;

-- Event pro mazání guest uživatelů
SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS delete_old_guests
ON SCHEDULE EVERY 1 DAY
DO
  DELETE FROM users
  WHERE is_guest = TRUE
    AND created_at < NOW() - INTERVAL 1 DAY;
