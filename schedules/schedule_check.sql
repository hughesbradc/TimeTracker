-- MySQL dump 10.13  Distrib 5.5.14, for Linux (x86_64)
--
-- Host: localhost    Database: bradmoodle
-- ------------------------------------------------------
-- Server version	5.5.14

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
-- Table structure for table `mdl_block_timetracker_schedules`
--

DROP TABLE IF EXISTS `mdl_block_timetracker_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_block_timetracker_schedules` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `studentid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `course_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `days` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `begin_time` int(4) NOT NULL,
  `end_time` int(4) NOT NULL,
  `begin_date` bigint(10) unsigned NOT NULL,
  `end_date` bigint(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds student schedule data.';
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `mdl_block_timetracker_holiday`
--

DROP TABLE IF EXISTS `mdl_block_timetracker_holiday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mdl_block_timetracker_holiday` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `start` bigint(10) NOT NULL,
  `end` bigint(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

