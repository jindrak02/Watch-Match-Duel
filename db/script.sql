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
  `imdb_url` varchar(255),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `genres` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `genre_name` varchar(255)
);

CREATE TABLE `content_genres` (
  `content_id` integer,
  `genre_id` integer
);

CREATE TABLE `sessions` (
  `session_id` CHAR(36) PRIMARY KEY DEFAULT (UUID()),
  `code_to_connect` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `session_users` (
  `id` integer PRIMARY KEY AUTO_INCREMENT,
  `session_id` CHAR(36),
  `user_id` CHAR(36)
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
