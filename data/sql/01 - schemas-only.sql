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
-- Table structure for table `mangas_info`
--

DROP TABLE IF EXISTS `mangas_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mangas_info` (
  `isbn` char(50) NOT NULL DEFAULT '',
  `lang` char(25) DEFAULT NULL,
  `volume` int(11) DEFAULT NULL,
  `title_id` int(11) NOT NULL DEFAULT '0',
  `created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mangas_ressources`
--

DROP TABLE IF EXISTS `mangas_ressources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mangas_ressources` (
  `title_id` int(11) NOT NULL DEFAULT '0',
  `short_name` varchar(20) NOT NULL DEFAULT '',
  `is_description` int(11) NOT NULL DEFAULT '0',
  `original_title` varchar(255) DEFAULT NULL,
  `editor` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `nb_volumes` int(11) DEFAULT NULL,
  `genre` varchar(255) DEFAULT NULL,
  `synopsis` text,
  `comments` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mangas_scrapper`
--

DROP TABLE IF EXISTS `mangas_scrapper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mangas_scrapper` (
  `id` int(11) NOT NULL,
  `scrapper_id` varchar(10) CHARACTER SET latin1 NOT NULL,
  `genres` text CHARACTER SET latin1 NOT NULL COMMENT 'ann, anfo, etc',
  `themes` text CHARACTER SET latin1 NOT NULL,
  `description` text CHARACTER SET latin1 NOT NULL,
  `comment` text CHARACTER SET latin1 NOT NULL,
  `rating` float NOT NULL,
  `thumbnail` text CHARACTER SET latin1 NOT NULL,
  `scrapper_mapping` text CHARACTER SET latin1 NOT NULL COMMENT 'can be an url or just an ID.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mangas_title`
--

DROP TABLE IF EXISTS `mangas_title`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mangas_title` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` char(255) DEFAULT NULL,
  `is_complete` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=107 DEFAULT CHARSET=latin1;
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
  `rolw` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-10-13 22:10:32
