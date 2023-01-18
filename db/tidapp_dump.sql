/*
SQLyog Community
MySQL - 5.7.36 : Database - tidsrapport
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `kategorier` */

CREATE TABLE `kategorier` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Kategorier` varchar(30) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UIX_kategori` (`Kategorier`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

/*Data for the table `kategorier` */

insert  into `kategorier`(`ID`,`Kategorier`) values 
(1,'ett'),
(2,'två');

/*Table structure for table `uppgifter` */

CREATE TABLE `uppgifter` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Tid` time NOT NULL COMMENT 'Min 00:05 max 8:00',
  `Datum` date NOT NULL,
  `KategoriId` int(11) NOT NULL,
  `Beskrivning` varchar(225) COLLATE utf8_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `KategoriId` (`KategoriId`),
  CONSTRAINT `uppgifter_ibfk_1` FOREIGN KEY (`KategoriId`) REFERENCES `kategorier` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci;

/*Data for the table `uppgifter` */

insert  into `uppgifter`(`ID`,`Tid`,`Datum`,`KategoriId`,`Beskrivning`) values 
(1,'12:03:00','2004-10-06',1,'hej'),
(2,'11:11:00','2023-01-26',2,'hejsan'),
(3,'03:03:00','2023-01-19',1,'nej'),
(4,'05:12:00','1601-01-10',2,'nope'),
(5,'02:02:00','2316-07-01',2,'jepp'),
(6,'04:01:00','2023-01-10',1,'yes');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
