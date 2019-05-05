-- Adminer 4.7.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS `tweakers`;
CREATE DATABASE `tweakers` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `tweakers`;

DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `date_created` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `articles` (`id`, `title`, `description`, `date_created`) VALUES
(1,	'Test artikel 1',	'Dit is een testbeschrijving van test artikel 1.',	'2019-04-28 15:21:43'),
(2,	'test artikel 2',	'En dit is dan de beschrijving van test artikel 2.',	'2019-04-28 15:22:09');

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tree_id` int(11) NOT NULL,
  `lft` int(11) NOT NULL,
  `rgt` int(11) NOT NULL,
  `depth` int(11) NOT NULL,
  `article_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `description` text,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tree_id` (`tree_id`),
  KEY `node_left_index` (`lft`),
  KEY `node_right_index` (`rgt`),
  KEY `node_depth_index` (`depth`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`tree_id`) REFERENCES `comments_tree` (`tree_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `comments` (`id`, `tree_id`, `lft`, `rgt`, `depth`, `article_id`, `user_id`, `description`, `date_created`) VALUES
(2,	3,	0,	19,	0,	1,	3,	'Hoofdcommentaar 1 op artikel 1. ',	'2019-05-02 09:32:54'),
(3,	3,	1,	4,	1,	1,	1,	'Commentaar 1 op hoofdcommentaar 1. ',	'2019-05-02 09:39:33'),
(4,	3,	5,	18,	1,	1,	2,	'Commentaar 2 op hoofdcommentaar 1.',	'2019-05-02 08:02:29'),
(5,	3,	2,	3,	2,	1,	2,	'Commentaar 1 op commentaar 1 van hoofdcommentaar 1',	'2019-05-02 10:03:41'),
(6,	4,	0,	5,	0,	1,	4,	'Hoofdcommentaar 2',	'2019-05-02 10:47:36'),
(7,	4,	1,	2,	1,	1,	4,	'Commentaar op hoofdcommentaar 2',	'2019-05-02 10:50:38'),
(8,	3,	6,	13,	2,	1,	2,	'commentaartje op comment id 4',	'2019-05-02 11:11:33'),
(9,	3,	7,	10,	3,	1,	2,	'commentaartje op id 8 ',	'2019-05-02 11:12:04'),
(10,	4,	3,	4,	1,	1,	2,	'Nog een stukje commentaar! ',	'2019-05-02 18:09:45'),
(11,	3,	8,	9,	4,	1,	3,	'Test commentaar ',	'2019-05-02 18:18:02'),
(12,	3,	14,	15,	2,	1,	3,	'Test commentaar ',	'2019-05-02 18:19:06'),
(13,	3,	11,	12,	3,	1,	1,	'Test commentaar ',	'2019-05-05 18:56:12'),
(14,	3,	16,	17,	2,	1,	4,	'Test commentaar ',	'2019-05-05 18:56:32'),
(15,	5,	0,	1,	0,	2,	4,	'Testcommentaar',	'2019-05-05 19:35:20'),
(16,	6,	0,	1,	0,	1,	1,	'Testcommentaar',	'2019-05-05 20:05:25'),
(17,	7,	0,	1,	0,	1,	1,	'&lt;script&gt;alert(\'test\');&lt;/script&gt;',	'2019-05-05 20:07:01');

DROP TABLE IF EXISTS `comments_score`;
CREATE TABLE `comments_score` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `comment_id` (`comment_id`),
  CONSTRAINT `comments_score_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `comments_tree`;
CREATE TABLE `comments_tree` (
  `tree_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`tree_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `comments_tree` (`tree_id`, `name`) VALUES
(3,	'174fa784ae373fc2056826863c050ad95cdf6d75'),
(6,	'2eb29340e575a2e3a8b516c69357764770885f84'),
(4,	'b5befa69683daddc6a7ea5c04e25eddc17a999aa'),
(7,	'be9bf39c4a8ecb9ec630dc584c56b02a4e9fb3e6'),
(5,	'd8b8debd5f0814639807bdfcc126c8c53083f593');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`id`, `name`) VALUES
(1,	'gebruikertje 1'),
(2,	'gebruikertje 2'),
(3,	'gebruikertje 3'),
(4,	'gebruikertje 4');

-- 2019-05-05 20:34:42
