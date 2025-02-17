/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.6.2-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: camping
-- ------------------------------------------------------
-- Server version	11.6.2-MariaDB-ubu2404

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `availability`
--

DROP TABLE IF EXISTS `availability`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rental_id` int(11) DEFAULT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_3FB7A2BFA7CF2329` (`rental_id`),
  CONSTRAINT `FK_3FB7A2BFA7CF2329` FOREIGN KEY (`rental_id`) REFERENCES `rental` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `availability`
--

LOCK TABLES `availability` WRITE;
/*!40000 ALTER TABLE `availability` DISABLE KEYS */;
INSERT INTO `availability` VALUES
(1,1,'2025-05-01 00:00:00','2025-05-10 23:59:59'),
(2,2,'2025-06-05 00:00:00','2025-06-07 23:59:59'),
(3,6,'2025-06-05 00:00:00','2025-06-07 23:59:59');
/*!40000 ALTER TABLE `availability` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES
('DoctrineMigrations\\Version20250213135425','2025-02-15 17:16:17',595),
('DoctrineMigrations\\Version20250215171552','2025-02-15 17:16:18',119);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `equipment`
--

LOCK TABLES `equipment` WRITE;
/*!40000 ALTER TABLE `equipment` DISABLE KEYS */;
INSERT INTO `equipment` VALUES
(13,'Climatisation / Chauffage',1),
(14,'Cuisine équipée',1),
(15,'Télévision',1),
(16,'Wi-Fi',0),
(17,'Proche Parking',1),
(18,'Proche Piscine',1),
(19,'Animaux acceptés',1),
(20,'Salon de jardin',0),
(21,'Terrasse',1),
(22,'Place ombragée',1),
(23,'Borne électrique',1),
(24,'Borne eau',0);
/*!40000 ALTER TABLE `equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0` (`queue_name`),
  KEY `IDX_75EA56E0E3BD61CE` (`available_at`),
  KEY `IDX_75EA56E016BA31DB` (`delivered_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messenger_messages`
--

LOCK TABLES `messenger_messages` WRITE;
/*!40000 ALTER TABLE `messenger_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messenger_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rental`
--

DROP TABLE IF EXISTS `rental`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rental` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `bedding` int(11) NOT NULL,
  `surface` int(11) NOT NULL,
  `location` int(11) NOT NULL,
  `is_clean` tinyint(1) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_1619C27DC54C8C93` (`type_id`),
  CONSTRAINT `FK_1619C27DC54C8C93` FOREIGN KEY (`type_id`) REFERENCES `type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rental`
--

LOCK TABLES `rental` WRITE;
/*!40000 ALTER TABLE `rental` DISABLE KEYS */;
INSERT INTO `rental` VALUES
(1,1,'Emplacement nu petite taille 4 personnes','Emplacement nu petite taille pour 4 personnes maximum',4,10,1,1,1),
(2,2,'Emplacement nu grande taille 6 personnes','Emplacement nu grande taille pour 6 personnes maximum',6,15,2,1,1),
(3,3,'Tente meublée 2 places','Tente meublée pour 2 personnes',2,20,3,1,1),
(4,4,'Tente meublée 4 places','Tente meublée pour 4 personnes',4,25,4,1,1),
(5,4,'Tente meublée 4 places','Tente meublée pour 4 personnes',4,25,5,1,0),
(6,5,'Mobil-home 4 places','Mobil-home pour 4 personnes',4,30,6,1,1),
(7,6,'Mobil-home 8 places','Mobil-home pour 8 personnes',8,35,7,1,1);
/*!40000 ALTER TABLE `rental` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rental_equipment`
--

DROP TABLE IF EXISTS `rental_equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rental_equipment` (
  `rental_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  PRIMARY KEY (`rental_id`,`equipment_id`),
  KEY `IDX_6CD8D28CA7CF2329` (`rental_id`),
  KEY `IDX_6CD8D28C517FE9FE` (`equipment_id`),
  CONSTRAINT `FK_6CD8D28C517FE9FE` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_6CD8D28CA7CF2329` FOREIGN KEY (`rental_id`) REFERENCES `rental` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rental_equipment`
--

LOCK TABLES `rental_equipment` WRITE;
/*!40000 ALTER TABLE `rental_equipment` DISABLE KEYS */;
INSERT INTO `rental_equipment` VALUES
(1,18),
(1,20),
(1,23),
(2,18),
(2,19),
(2,23),
(2,24),
(3,18),
(3,20),
(3,23),
(3,24),
(4,19),
(4,20),
(4,23),
(4,24),
(5,19),
(5,20),
(5,23),
(5,24),
(6,13),
(6,14),
(6,15),
(6,16),
(6,19),
(6,21),
(7,13),
(7,14),
(7,15),
(7,16),
(7,18),
(7,19),
(7,20),
(7,21);
/*!40000 ALTER TABLE `rental_equipment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `rental_id` int(11) DEFAULT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  `adults_number` int(11) NOT NULL,
  `kids_number` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_42C84955A76ED395` (`user_id`),
  KEY `IDX_42C84955A7CF2329` (`rental_id`),
  CONSTRAINT `FK_42C84955A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `FK_42C84955A7CF2329` FOREIGN KEY (`rental_id`) REFERENCES `rental` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation`
--

LOCK TABLES `reservation` WRITE;
/*!40000 ALTER TABLE `reservation` DISABLE KEYS */;
INSERT INTO `reservation` VALUES
(1,4,1,'2025-04-01 00:00:00','2025-04-30 00:00:00',2,0,24000,0),
(2,4,1,'2025-05-15 00:00:00','2025-05-30 00:00:00',2,0,12800,1),
(3,4,2,'2025-05-15 00:00:00','2025-05-30 00:00:00',2,0,19200,1),
(4,4,2,'2025-05-07 00:00:00','2025-05-15 00:00:00',2,0,19200,1);
/*!40000 ALTER TABLE `reservation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `season`
--

DROP TABLE IF EXISTS `season`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `season` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(30) NOT NULL,
  `is_closed` tinyint(1) NOT NULL,
  `percentage` int(11) NOT NULL,
  `date_start` datetime NOT NULL,
  `date_end` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `season`
--

LOCK TABLES `season` WRITE;
/*!40000 ALTER TABLE `season` DISABLE KEYS */;
INSERT INTO `season` VALUES
(6,'Basse saison',0,80,'2025-04-01 00:00:00','2025-06-30 23:59:59'),
(7,'Basse saison',0,80,'2025-09-01 00:00:00','2025-09-30 23:59:59'),
(8,'Haute saison',0,120,'2025-07-01 00:00:00','2025-08-31 23:59:59'),
(9,'Saison fermée',1,0,'2025-01-01 00:00:00','2025-03-31 23:59:59'),
(10,'Saison fermée',1,0,'2025-10-01 00:00:00','2025-12-31 23:59:59');
/*!40000 ALTER TABLE `season` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `type`
--

DROP TABLE IF EXISTS `type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `price` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `type`
--

LOCK TABLES `type` WRITE;
/*!40000 ALTER TABLE `type` DISABLE KEYS */;
INSERT INTO `type` VALUES
(1,'Emplacement nu petite taille 4 personnes','emplacement-nu-petite-taille.jpg',1000,1),
(2,'Emplacement nu grande taille 6 personnes','emplacement-nu-grande-taille.jpg',1500,1),
(3,'Tente meublée 2 places','tente-meublee-2-places.jpg',2000,1),
(4,'Tente meublée 4 places','tente-meublee-4-places.jpg',2500,1),
(5,'Mobil-home 4 places','mobil-home-4-places.jpg',3000,1),
(6,'Mobil-home 8 places','mobil-home-8-places.jpg',3500,0);
/*!40000 ALTER TABLE `type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '(DC2Type:json)' CHECK (json_valid(`roles`)),
  `password` varchar(255) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `username` varchar(50) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES
(3,'admin@admin.com','[\"ROLE_ADMIN\"]','$2y$13$RMgDU5tfWlG.hyPOf13g9eCDCllBHKIv7AGZd9oHljLDoxMr7XJCK','Admin','Admin','administrateur',1),
(4,'john@doe.com','[\"ROLE_USER\"]','$2y$13$nPY8teH1K7dep6h72HY1EuUMTNFuiIuV3cs7hZaC2eiHpUyi1btji','John','Doe','johndoe',1);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-02-17 13:32:41
