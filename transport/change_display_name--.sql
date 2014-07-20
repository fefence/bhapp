# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.5.38-0ubuntu0.14.04.1)
# Database: bhapp
# Generation Time: 2014-07-20 12:03:49 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table leagueDetails
# ------------------------------------------------------------

DROP TABLE IF EXISTS `leagueDetails`;

CREATE TABLE `leagueDetails` (
  `id` int(200) NOT NULL AUTO_INCREMENT,
  `country` varchar(200) NOT NULL,
  `fullName` varchar(200) NOT NULL,
  `displayName` varchar(200) NOT NULL,
  `alternativeName` varchar(200) NOT NULL,
  `alternativeName2` varchar(100) NOT NULL,
  `ppm` tinyint(2) NOT NULL DEFAULT '0',
  `pps` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `leagueDetails` WRITE;
/*!40000 ALTER TABLE `leagueDetails` DISABLE KEYS */;

INSERT INTO `leagueDetails` (`id`, `country`, `fullName`, `displayName`, `alternativeName`, `alternativeName2`, `ppm`, `pps`)
VALUES
	(1,'poland','ekstraklasa','Ekstraklasa','orange-ekstraklasa','',1,1),
	(2,'poland','division-1','Division 1','','',0,1),
	(3,'poland','division-2-east','D2E','','',0,1),
	(4,'poland','division-2-west','D2W','','',0,1),
	(5,'mexico','primera-division','Primera Division','','',0,1),
	(6,'russia','premier-league','Premier League','','',1,0),
	(7,'england','premier-league','Premier League','','',0,1),
	(8,'england','championship','Championship','','',0,1),
	(9,'england','league-one','League 1','','',0,1),
	(10,'england','league-two','League 2','','',0,1),
	(11,'italy','serie-b','Serie B','','',0,1),
	(12,'italy','serie-a','Serie A','','',0,1),
	(13,'south-korea','k-league-classic','K League Classic','k-league','',0,1),
	(15,'argentina','primera-division','Primera Division','','',0,1),
	(17,'australia','a-league','A League','','',1,0),
	(19,'austria','erste-liga','Erste Liga','adeg-erste-liga','',0,1),
	(24,'brazil','serie-a','Serie A','','',0,1),
	(28,'chile','primera-division','Primera Division','','',0,1),
	(30,'china','super-league','Super League','','',0,1),
	(31,'china','jia-league','Jia League','','',0,1),
	(32,'colombia','liga-postobon','Liga Postobon','','',0,1),
	(35,'croatia','1-hnl','1 HNL','','',1,0),
	(36,'cyprus','first-division','First Division','','',0,1),
	(39,'denmark','superliga','Superliga','','',1,0),
	(41,'ecuador','serie-a','Serie A','','',0,1),
	(42,'egypt','premier-league','Premier League','','',0,1),
	(46,'france','ligue-1','Ligue 1','','',0,1),
	(47,'france','ligue-2','Ligue 2','','',0,1),
	(48,'france','national','National','','',0,1),
	(51,'germany','bundesliga','Bundesliga','','',0,1),
	(52,'germany','2-bundesliga','2 Bundesliga','','',0,1),
	(53,'germany','3-liga','3 Liga','','',0,1),
	(59,'iran','pro-league','Pro League','','',0,1),
	(62,'israel','ligat-ha-al','Ligat Ha Al','','',0,1),
	(69,'lithuania','a-lyga','A Lyga','','',1,0),
	(74,'morocco','botola','Botola','','',0,1),
	(75,'netherlands','eredivisie','Eredivisie','','',0,1),
	(85,'romania','liga-i','Liga I','','',0,1),
	(89,'saudi-arabia','saudi-professional-league','Saudi Professional League','','',0,1),
	(90,'scotland','premiership','Premiership','premier-league','',0,1),
	(91,'scotland','championship','Championship','division-1','',0,1),
	(92,'scotland','league-one','League One','division-2','',0,1),
	(93,'scotland','league-two','League Two','division-3','',0,1),
	(97,'slovakia','2-liga','2 Liga','','',0,1),
	(99,'south-africa','premier-league','Premier League','','',0,1),
	(100,'spain','primera-division','Primera Division','','',1,0),
	(101,'tunisia','ligue-professionnelle-1','Ligue Professionnelle 1','','',0,1),
	(102,'spain','segunda-division','Segunda Division','','',0,1),
	(103,'sweden','allsvenskan','Allsvenskan','','',0,1),
	(104,'sweden','superettan','Superettan','','',0,1),
	(107,'turkey','superliga','Superliga','','',0,1),
	(112,'usa','mls','MLS','','',0,1);

/*!40000 ALTER TABLE `leagueDetails` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
