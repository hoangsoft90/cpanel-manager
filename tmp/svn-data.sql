-- MySQL dump 10.13  Distrib 5.6.24, for Win32 (x86)
--
-- Host: localhost    Database: hw_projects
-- ------------------------------------------------------
-- Server version	5.6.24

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
-- Table structure for table `svn_wp_users`
--

DROP TABLE IF EXISTS `svn_wp_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `svn_wp_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `svn_fullname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `svn_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `svn_pass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `svn_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `domain` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `svn_wp_users`
--

LOCK TABLES `svn_wp_users` WRITE;
/*!40000 ALTER TABLE `svn_wp_users` DISABLE KEYS */;
INSERT INTO `svn_wp_users` VALUES (4,'Hoang','hoang123','U2hKdmNQV21lZzNGTkNL','hoang1@yahoo.com','hoangweb.vn'),(11,'Quach+hoang123','user5','NmJrOXJjY3FuQ01WZEVI','quachhoangxx_2005@yahoo.com','hoangweb.vn'),(12,'Nhan+vien+1','nhanvien1','a2JjTkJnU1lSWVJLbTlu','kythuat.hoangweb@gmail.com','hoangweb.vn'),(13,'Thao+88','thao88','M1lOYnkyZ1lxeTdoYkN4','quachhoang_2005@yahoo.com','hoangweb.vn');
/*!40000 ALTER TABLE `svn_wp_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `svn_repositories`
--

DROP TABLE IF EXISTS `svn_repositories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `svn_repositories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `repository_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `svn_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `svn_repositories`
--

LOCK TABLES `svn_repositories` WRITE;
/*!40000 ALTER TABLE `svn_repositories` DISABLE KEYS */;
INSERT INTO `svn_repositories` VALUES (2,'dochoitreem01','hoang123'),(4,'repo3','thao88'),(5,'repo4',''),(6,'repo5',''),(7,'theme_thao','');
/*!40000 ALTER TABLE `svn_repositories` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-09-12 23:56:56
