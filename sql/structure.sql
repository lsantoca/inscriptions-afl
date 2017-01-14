-- MySQL dump 10.13  Distrib 5.1.72, for apple-darwin11.4.2 (i386)
--
-- Host: localhost    Database: inscriptions-afl
-- ------------------------------------------------------
-- Server version	5.1.72

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
-- Table structure for table `Administrateur`
--

DROP TABLE IF EXISTS `Administrateur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Administrateur` (
  `ID_administrateur` int(11) NOT NULL AUTO_INCREMENT,
  `admin_login` varchar(10) DEFAULT NULL,
  `coded_admin_passe` varchar(32) DEFAULT NULL,
  `Nom` varchar(20) DEFAULT NULL,
  `Prenom` varchar(20) DEFAULT NULL,
  `courriel` varchar(50) DEFAULT NULL,
  `nonce` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`ID_administrateur`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `COUREUR.DBF`
--

DROP TABLE IF EXISTS `COUREUR.DBF`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `COUREUR.DBF` (
  `NO_LIC` varchar(8) DEFAULT NULL,
  `NO_CLUB` varchar(5) DEFAULT NULL,
  `NOM` varchar(20) DEFAULT NULL,
  `PRENOM` varchar(20) DEFAULT NULL,
  `DAT_NAIS` varchar(8) DEFAULT NULL,
  `AN_NAIS` varchar(20) DEFAULT NULL,
  `SEXE` varchar(1) DEFAULT NULL,
  `CATEGORIE` varchar(1) DEFAULT NULL,
  `DAT_LIC` varchar(8) DEFAULT NULL,
  `ISAF_ID` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Inscrit`
--

DROP TABLE IF EXISTS `Inscrit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Inscrit` (
  `ID_inscrit` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(16) DEFAULT NULL,
  `nom` varchar(20) DEFAULT NULL,
  `prenom` varchar(20) DEFAULT NULL,
  `naissance` date DEFAULT NULL,
  `num_lic` varchar(8) DEFAULT NULL,
  `isaf_no` varchar(10) NOT NULL,
  `num_club` varchar(5) DEFAULT NULL 
        COMMENT 'Une chaine de caracteres pour compatibilité FREG',
  `nom_club` varchar(100) NOT NULL,
  `prefix_voile` varchar(4) DEFAULT NULL,
  `num_voile` int(11) DEFAULT NULL,
  `serie` varchar(5) DEFAULT NULL,
  `adherant` tinyint(1) DEFAULT NULL,
  `sexe` enum('F','M') DEFAULT NULL,
  `taille_polo` enum('XS','S','M','L','XL','XXL') NOT NULL DEFAULT 'M',
  `conf` tinyint(1) DEFAULT NULL,
  `date preinscription` datetime NOT NULL,
  `date confirmation` datetime DEFAULT NULL,
  `mail` varchar(40) DEFAULT NULL,
  `statut` enum('Licencie','Etranger','Autre') NOT NULL,
  `ID_regate` int(11) NOT NULL,
  PRIMARY KEY (`ID_inscrit`),
  KEY `FK_Inscrit_ID_regate` (`ID_regate`),
  CONSTRAINT `FK_Inscrit_ID_regate` FOREIGN KEY (`ID_regate`) REFERENCES `Regate` (`ID_regate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Regate`
--

DROP TABLE IF EXISTS `Regate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Regate` (
  `ID_regate` int(11) NOT NULL AUTO_INCREMENT,
  `nonce` varchar(16) NOT NULL DEFAULT '',
  `org_login` varchar(10) DEFAULT NULL,
  `coded_org_passe` varchar(32) NOT NULL,
  `istest` tinyint(4) NOT NULL DEFAULT '0',
  `titre` varchar(100) DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `informations` varchar(500) DEFAULT NULL
    COMMENT 'Complémentaire à ''decription'' au cas que le club souhaite ajouter plus d''informations',
  `cv_organisateur` varchar(100) DEFAULT NULL,
  `courriel` varchar(50) NOT NULL,
  `lieu` varchar(100) DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `date_limite_preinscriptions` date DEFAULT NULL,
  `series` varchar(500) DEFAULT 'LAS4,LAR,LAS',
  `polo` tinyint(4) NOT NULL DEFAULT '0',
  `droits` int(11) NOT NULL DEFAULT '0',
  `paiement_en_ligne` varchar(50) DEFAULT NULL,
  `resultats` varchar(100) DEFAULT NULL,
  `destruction` date DEFAULT NULL,
  `ID_administrateur` int(11) NOT NULL,
  PRIMARY KEY (`ID_regate`),
  KEY `FK_Regate_ID_administrateur` (`ID_administrateur`),
  CONSTRAINT `FK_Regate_ID_administrateur` FOREIGN KEY (`ID_administrateur`) REFERENCES `Administrateur` (`ID_administrateur`)
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

-- Dump completed on 2016-03-06 20:28:51

