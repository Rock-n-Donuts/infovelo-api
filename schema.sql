# ************************************************************
# MySQL 8.0.29
# Database: hackqc
# Generation Time: 2022-11-05 19:19:06 +0000
# ************************************************************

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE `boroughs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contribution_replies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contribution_id` int unsigned NOT NULL,
  `message` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint default 0,
  PRIMARY KEY (`id`),
  KEY `contribution_reply` (`contribution_id`),
  KEY `reply_user` (`user_id`),
  CONSTRAINT `contribution_reply` FOREIGN KEY (`contribution_id`) REFERENCES `contributions` (`id`),
  CONSTRAINT `reply_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contribution_votes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `contribution_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `score` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contribution_vote` (`contribution_id`),
  KEY `vote_user` (`user_id`),
  CONSTRAINT `contribution_vote` FOREIGN KEY (`contribution_id`) REFERENCES `contributions` (`id`),
  CONSTRAINT `vote_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `contributions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime DEFAULT NULL,
  `issue_id` int unsigned DEFAULT NULL,
  `comment` text,
  `photo_path` varchar(256) DEFAULT NULL,
  `photo_width` int unsigned DEFAULT NULL,
  `photo_height` int unsigned DEFAULT NULL,
  `location` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `user_id` int unsigned NOT NULL,
  `name` varchar(256) DEFAULT NULL,
  `quality` int DEFAULT NULL,
  `is_deleted` tinyint default 0,
  `borough_name` varchar(255) DEFAULT NULL,
  `is_video` tinyint DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contribution_author` (`user_id`),
  CONSTRAINT `contribution_author` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `troncons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `id_trc` int DEFAULT NULL,
  `id2020` int DEFAULT NULL,
  `borough_id` int DEFAULT NULL,
  `type` tinyint DEFAULT NULL,
  `length` float DEFAULT NULL,
  `id_cycl` int DEFAULT NULL,
  `type2` int DEFAULT NULL,
  `nb_lanes` int DEFAULT NULL,
  `splitter` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `four_seasons` tinyint DEFAULT NULL,
  `protected_four_seasons` tinyint DEFAULT NULL,
  `street_side_one_state` int DEFAULT '0',
  `street_side_two_state` int DEFAULT '0',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `troncon_lines` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `trc_id` (`id_trc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(256) DEFAULT NULL,
  `token` varchar(256) DEFAULT NULL,
  `rq_ip`varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
