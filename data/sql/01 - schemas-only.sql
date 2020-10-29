CREATE DATABASE  IF NOT EXISTS `mangaslib` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `mangaslib`;
-- MySQL dump 10.13  Distrib 5.6.17, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: mangaslib
-- ------------------------------------------------------
-- Server version	5.7.31

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `mangas_scrapper`
--

DROP TABLE IF EXISTS `mangas_scrapper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mangas_scrapper` (
  `id` int(11) NOT NULL,
  `scrapper_id` varchar(10) NOT NULL,
  `genres` mediumtext NOT NULL COMMENT 'ann, anfo, etc',
  `themes` mediumtext NOT NULL,
  `description` mediumtext NOT NULL,
  `comment` mediumtext NOT NULL,
  `rating` float NOT NULL,
  `thumbnail` mediumtext NOT NULL,
  `scrapper_mapping` mediumtext NOT NULL COMMENT 'can be an url or just an ID.',
  PRIMARY KEY (`id`,`scrapper_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mangas_series`
--

DROP TABLE IF EXISTS `mangas_series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mangas_series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(255) NOT NULL COMMENT 'title of the series',
  `library_status` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'status in the library (completed:1, abandoned:2 or in progress:0)',
  `rating` decimal(10,0) DEFAULT NULL,
  `series_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'status of the series oficially',
  `short_name` varchar(20) NOT NULL COMMENT 'short name',
  `volumes` int(11) DEFAULT NULL COMMENT 'total number of volumes this series has',
  `chapters` int(11) DEFAULT NULL COMMENT 'number of chapters',
  `editors` text,
  `authors` text COMMENT 'list of authors, coma separated',
  `genres` text COMMENT 'list of genres, coma separated',
  `synopsis` text,
  `comments` text,
  `cover` text COMMENT 'url of the cover',
  `banner` text COMMENT 'url of the banner',
  `thumbnail` text COMMENT 'url of the thumbnail',
  `alternate_titles` text COMMENT 'json format of the alternate titles',
  `themes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mangas_users`
--

DROP TABLE IF EXISTS `mangas_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mangas_users` (
  `username` varchar(25) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(25) NOT NULL,
  `last_name` varchar(25) NOT NULL,
  `role` int(11) NOT NULL,
  `wishlist` text,
  PRIMARY KEY (`email`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mangas_volume`
--

DROP TABLE IF EXISTS `mangas_volume`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mangas_volume` (
  `isbn` char(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `lang` char(25) CHARACTER SET latin1 DEFAULT NULL,
  `volume` int(11) DEFAULT NULL,
  `title_id` int(11) NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-10-29  8:34:19
