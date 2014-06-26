# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.37-0ubuntu0.14.04.1)
# Database: bhapp
# Generation Time: 2014-06-26 12:34:20 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table common_pools
# ------------------------------------------------------------

DROP TABLE IF EXISTS `common_pools`;

CREATE TABLE `common_pools` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `income` decimal(8,2) NOT NULL,
  `profit` decimal(8,2) NOT NULL,
  `in_transit` decimal(8,2) NOT NULL,
  `account` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `common_pools` WRITE;
/*!40000 ALTER TABLE `common_pools` DISABLE KEYS */;

INSERT INTO `common_pools` (`id`, `user_id`, `created_at`, `updated_at`, `income`, `profit`, `in_transit`, `account`)
VALUES
	(1,1,'2014-05-23 16:24:44','2014-06-25 14:36:36',0.00,0.00,0.00,0.00),
	(2,2,'2014-05-23 16:24:44','2014-06-26 11:50:02',0.00,0.00,0.00,0.00),
	(3,3,'2014-05-23 16:24:44','2014-06-25 12:16:24',0.00,0.00,0.00,0.00),
	(4,4,'2014-05-23 16:24:44','2014-06-19 13:39:03',0.00,0.00,0.00,0.00);

/*!40000 ALTER TABLE `common_pools` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table games
# ------------------------------------------------------------

DROP TABLE IF EXISTS `games`;

CREATE TABLE `games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bet` decimal(8,2) NOT NULL,
  `odds` decimal(8,2) NOT NULL DEFAULT '3.00',
  `bsf` decimal(8,2) NOT NULL,
  `income` decimal(8,2) NOT NULL,
  `match_id` varchar(255) NOT NULL DEFAULT '',
  `game_type_id` int(11) NOT NULL DEFAULT '1',
  `bookmaker_id` int(11) NOT NULL DEFAULT '1',
  `special` tinyint(1) NOT NULL,
  `standings_id` int(10) NOT NULL,
  `groups_id` int(10) NOT NULL,
  `confirmed` tinyint(2) NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table pools
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pools`;

CREATE TABLE `pools` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `league_details_id` int(11) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `income` decimal(8,2) NOT NULL,
  `current` decimal(8,2) NOT NULL,
  `ppm` tinyint(4) NOT NULL DEFAULT '0',
  `account` decimal(8,2) NOT NULL,
  `profit` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table ppm
# ------------------------------------------------------------

DROP TABLE IF EXISTS `ppm`;

CREATE TABLE `ppm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `series_id` int(11) NOT NULL,
  `bet` decimal(8,2) NOT NULL,
  `odds` decimal(8,2) NOT NULL,
  `bsf` decimal(8,2) NOT NULL,
  `income` decimal(8,2) NOT NULL,
  `match_id` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `game_type_id` int(11) NOT NULL DEFAULT '1',
  `bookmaker_id` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `country` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `confirmed` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



# Dump of table settings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `league_details_id` int(11) NOT NULL,
  `from` int(11) NOT NULL DEFAULT '2',
  `to` int(11) NOT NULL DEFAULT '6',
  `multiplier` decimal(10,2) DEFAULT NULL,
  `auto` tinyint(2) NOT NULL DEFAULT '1',
  `game_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `remember_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `email`, `name`, `password`, `created_at`, `updated_at`, `remember_token`)
VALUES
	(1,'fefence@gmail.com','fefence','$2y$10$zPc/zB9etQSBQN.8cTbQnOw4Rk8iRWSEoTZqsFvoT65w3xSk7LIaC','2014-05-23 08:21:07','2014-06-26 07:30:54','6QpUTdW4ZfyDiKwBrl3uKvwEAE7wmeSAB5rGwnVcDOpm4kbHQDz9b2aP1Pti'),
	(2,'wpopowa@gmail.com','veseto','$2y$10$yS0s3gT.vbCV6luFx6aUkueQWwyKSN53HRpJ7Ne6osBjTFzECZUY6','2014-05-23 08:22:38','2014-06-26 07:30:12','jRo6pF2F5xlxavK7K7RuWSJsr1qv9uE2ThYoFP7hcv1qbEf8GUdqfi8A5SUR'),
	(3,'dummy@bhapp.eu','stoyko','$2y$10$xZaj5CWYa8MhAZEWmqkbKuthCGZvTnIP1oN1jXS2KDXvMBCWJD4eu','2014-05-23 08:25:06','2014-06-25 08:34:51','O5yIMsrvwXpr7EQ984nJOHNNF0bVdwCAxfSymMe0opv3LOz3Jn2hHR9AGDGZ'),
	(4,'','jivko','$2y$10$NF1WmEyAeWuKewfPnrmrSuUFBCHfjN826sFHag2b08/HXERbutmXy','2014-05-23 08:26:26','2014-05-23 08:26:26',NULL);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
