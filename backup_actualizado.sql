-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: db_gamerzone_pro
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `fabricantes`
--

DROP TABLE IF EXISTS `fabricantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fabricantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_empresa` varchar(50) NOT NULL,
  `pais_origen` varchar(50) DEFAULT NULL,
  `sitio_web` varchar(100) DEFAULT NULL,
  `contacto_email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fabricantes`
--

LOCK TABLES `fabricantes` WRITE;
/*!40000 ALTER TABLE `fabricantes` DISABLE KEYS */;
INSERT INTO `fabricantes` VALUES (1,'Asus','Taiwan','www.asus.com','contact@asus.com'),(2,'MSI','Taiwan','www.msi.com','support@msi.com'),(3,'Razer','USA','www.razer.com','sales@razer.com'),(4,'Logitech','Suiza','www.logitech.com','info@logitech.com'),(5,'Corsair','USA','www.corsair.com','help@corsair.com'),(6,'HyperX','USA','www.hyperx.com','support@hyperx.com'),(7,'Lenovo','China','www.lenovo.com','biz@lenovo.com'),(8,'Gigabyte','Taiwan','www.gigabyte.com','sales@gigabyte.com'),(9,'SteelSeries','Dinamarca','www.steelseries.com','info@steelseries.com'),(10,'Nvidia','USA','www.nvidia.com','partners@nvidia.com'),(11,'HyperDrive Corp','China','www.hyperdrive.com','support@hdrive.com');
/*!40000 ALTER TABLE `fabricantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modelo` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `garantia_meses` int(11) DEFAULT NULL,
  `id_serie` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_serie` (`id_serie`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_serie`) REFERENCES `series` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (1,'Laptop ROG Strix Scar 18',3200.00,55,24,1),(2,'Monitor TUF VG27AQ',350.00,70,36,2),(3,'Laptop MSI Katana 15',1200.00,60,12,3),(4,'Mouse DeathAdder V3',70.00,100,24,4),(5,'Mouse G-Pro Superlight',150.00,80,24,5),(6,'RAM Vengeance RGB 32GB',180.00,150,120,6),(7,'Headset Cloud Alpha Wireless',199.00,65,24,7),(8,'Laptop Legion Slim 7i',1600.00,58,12,8),(9,'Placa Madre Z790 Aorus Master',450.00,62,36,9),(10,'Headset Arctis Nova Pro',349.00,60,24,10),(12,'Laptop ROG Zephyrus G14 2024',1850.00,10,24,1),(13,'ROG Flow Z13 Tablet Gamer',1760.00,5,24,1),(14,'ROG Strix Scar 16 (RTX 4080)',3080.00,3,24,1),(15,'Teclado ROG Azoth 75% OLED',250.00,15,12,1),(16,'Mouse ROG Harpe Ace Aim Lab',140.00,30,12,1),(17,'Cargador Original ROG 240W',90.00,20,6,1),(18,'Batería Repuesto Zephyrus M16',85.00,10,6,1),(19,'ROG Ally Z1 Extreme',700.00,25,12,1),(20,'Monitor ROG Swift OLED 27\"',1098.90,8,36,1),(21,'Mochila ROG Ranger BP2701',60.00,40,12,1),(22,'Cooler ROG Ryujin III 360',352.00,5,36,1),(23,'Fuente ROG Thor 1000W Platinum',330.00,6,60,1),(24,'Case ROG Hyperion GR701',495.00,4,24,1),(25,'Router ROG Rapture GT-AX11000',440.00,5,24,1),(26,'Keycaps ROG PBT Doubleshot Set',30.00,50,0,1),(27,'Laptop TUF Gaming A15 (Ryzen 7)',1100.00,20,24,2),(28,'Laptop TUF Dash F15 (i7 12th)',1050.00,15,24,2),(29,'Monitor TUF VG249Q1A 165Hz',180.00,50,36,2),(30,'Placa Madre TUF Gaming B650-PLUS',210.00,12,36,2),(31,'Tarjeta Video TUF RTX 3060 12GB',340.00,10,36,2),(32,'Case TUF Gaming GT301',90.00,15,24,2),(33,'Fuente TUF Gaming 750W Bronze',85.00,25,60,2),(34,'Teclado TUF Gaming K1',55.00,40,12,2),(35,'Mouse TUF Gaming M3 Gen II',25.00,60,12,2),(36,'Headset TUF Gaming H3 Silver',40.00,35,12,2),(37,'Ventilador Repuesto TUF Laptop',15.00,20,3,2),(38,'Pasta Térmica TUF Grade',8.00,100,0,2),(39,'Cable HDMI TUF Certificado',12.00,80,12,2),(40,'Mochila TUF VP4700',45.00,30,12,2),(41,'Webcam TUF C3 1080p',50.00,20,12,2),(42,'Laptop MSI Raider GE78 HX',2860.00,4,24,3),(43,'Laptop MSI Thin GF63',850.00,30,12,3),(44,'Tarjeta Video MSI RTX 4070 Ventus',715.00,8,36,3),(45,'Tarjeta Video MSI GT 730 (Legacy)',60.00,10,12,3),(46,'Placa Madre MSI MAG Z790 Tomahawk',280.00,10,36,3),(47,'Monitor MSI Optix G27C4 Curvo',220.00,15,36,3),(48,'Mouse MSI Clutch GM41 Lightweight',45.00,25,12,3),(49,'Teclado MSI Vigor GK30 Combo',60.00,20,12,3),(50,'Fuente MSI MPG A850G Gold',140.00,12,60,3),(51,'Cooler MSI MAG Coreliquid 240R',110.00,10,36,3),(52,'SSD MSI Spatium M480 2TB',150.00,15,60,3),(53,'Control MSI Force GC30 V2',35.00,40,12,3),(54,'Mochila MSI Essential',35.00,50,12,3),(55,'Kit Bisagras MSI GF63',25.00,15,3,3),(56,'Cargador MSI 180W Original',65.00,20,6,3),(57,'Laptop Razer Blade 16 OLED',3520.00,3,24,4),(58,'Teclado BlackWidow V4 Pro',230.00,10,24,4),(59,'Teclado Huntsman Mini Analog',120.00,20,24,4),(60,'Mouse Viper Mini Signature',308.00,2,24,4),(61,'Mouse Naga V2 Pro MMO',198.00,8,24,4),(62,'Headset Kraken Kitty V2 Pro',160.00,12,24,4),(63,'Webcam Razer Kiyo Pro Ultra',330.00,5,12,4),(64,'Microfono Razer Seiren V2 X',80.00,20,12,4),(65,'Control Razer Wolverine V2 Chroma',150.00,10,12,4),(66,'Mousepad Goliathus Chroma 3XL',90.00,15,12,4),(67,'Key Light Razer Chroma',220.00,6,12,4),(68,'Silla Razer Iskur V2',660.00,2,36,4),(69,'Audífonos Hammerhead Pro Hyperspeed',200.00,10,24,4),(70,'Almohadillas Repuesto Kraken',15.00,50,0,4),(71,'Cable USB-C Razer Speedflex',25.00,30,12,4),(72,'Mouse G Pro X Superlight 2',160.00,40,24,5),(73,'Teclado G Pro X TKL Lightspeed',200.00,20,24,5),(74,'Headset G Pro X 2 Lightspeed',250.00,15,24,5),(75,'Volante G923 Trueforce',385.00,5,24,5),(76,'Palanca Cambios Driving Force',60.00,10,24,5),(77,'Mouse G502 X Plus Millennium',160.00,25,24,5),(78,'Mouse G203 Lightsync',30.00,100,24,5),(79,'Teclado G213 Prodigy',50.00,60,24,5),(80,'Audífonos G435 Wireless',70.00,45,24,5),(81,'Webcam C920s Pro HD',70.00,30,24,5),(82,'Webcam Brio 500 4K',130.00,10,24,5),(83,'Mousepad G640 Large',35.00,50,12,5),(84,'Adaptador USB Lightspeed Repuesto',20.00,40,6,5),(85,'Pies de Teflón G Pro (Skates)',10.00,100,0,5),(86,'Batería Repuesto G903',25.00,15,6,5),(87,'RAM Vengeance RGB DDR5 64GB',280.00,10,120,6),(88,'RAM Vengeance LPX DDR4 16GB',45.00,80,120,6),(89,'Case 5000D Airflow Teak',198.00,8,24,6),(90,'Fuente RM1000e Gold ATX 3.0',180.00,12,84,6),(91,'Cooler iCUE H150i Elite LCD XT',319.00,6,60,6),(92,'Ventilador iCUE Link QX120',50.00,40,24,6),(93,'SSD MP700 Pro Gen5 2TB',330.00,5,60,6),(94,'Teclado K70 MAX Magnetic',230.00,10,24,6),(95,'Mouse Scimitar Elite Wireless',130.00,15,24,6),(96,'Headset Virtuoso XT',297.00,8,24,6),(97,'Silla TC100 Relaxed',275.00,4,24,6),(98,'Elgato Stream Deck MK.2',150.00,20,24,6),(99,'Elgato Wave:3 Mic',150.00,15,24,6),(100,'Kit Cables Fuente Premium',80.00,10,24,6),(101,'Pasta Térmica TM30',15.00,60,0,6),(102,'Headset Cloud III Wireless',170.00,20,24,7),(103,'Headset Cloud Alpha Wireless (300h)',200.00,15,24,7),(104,'Headset Cloud Stinger 2 Core',40.00,50,24,7),(105,'Micrófono QuadCast S White',160.00,25,24,7),(106,'Micrófono DuoCast',100.00,15,24,7),(107,'Teclado Alloy Origins 60',100.00,20,24,7),(108,'Teclado Alloy Rise',220.00,5,24,7),(109,'Mouse Pulsefire Haste 2',60.00,30,24,7),(110,'Mousepad Pulsefire Mat XXL',30.00,40,12,7),(111,'Earcups Repuesto Cloud II (Cuero)',15.00,100,0,7),(112,'Earcups Repuesto Cloud II (Velour)',15.00,50,0,7),(113,'Cable Auxiliar Cloud con Control',20.00,40,6,7),(114,'Micrófono Boom Repuesto Cloud',15.00,60,6,7),(115,'Filtro Pop para Quadcast',10.00,30,0,7),(116,'Mochila HyperX Delta',40.00,15,12,7),(117,'Laptop Legion 9i (Liquid Cooling)',4950.00,2,24,8),(118,'Laptop Legion Pro 7i Gen 8',3080.00,5,24,8),(119,'Laptop Legion Slim 5 OLED',1400.00,10,24,8),(120,'Legion Go 1TB Handheld',750.00,15,12,8),(121,'Monitor Legion Y25g-30 360Hz',550.00,6,36,8),(122,'Monitor Legion R45w-30 Ultrawide',990.00,3,36,8),(123,'Mouse Legion M600s Wireless',70.00,20,12,8),(124,'Teclado Legion K500 RGB',90.00,15,12,8),(125,'Headset Legion H600 Wireless',100.00,10,12,8),(126,'Estación de Carga Legion S600',88.00,8,12,8),(127,'Mochila Blindada Legion',90.00,20,12,8),(128,'Cargador Legion 330W GaN',120.00,10,12,8),(129,'Funda Legion Go',30.00,25,6,8),(130,'Protector Pantalla Legion Go',15.00,50,0,8),(131,'Dock USB-C Legion Travel',60.00,30,12,8),(132,'Laptop Aorus 17X (i9 13th)',3300.00,3,24,9),(133,'Monitor Aorus FO48U OLED 48\"',1320.00,2,36,9),(134,'Placa Madre Z790 Aorus Master X',605.00,8,36,9),(135,'Tarjeta Video Aorus RTX 4090 Master',2200.00,2,48,9),(136,'SSD Aorus Gen5 12000 2TB',350.00,10,60,9),(137,'Cooler Aorus Waterforce X II 360',330.00,5,60,9),(138,'Fuente Aorus P1200W Platinum',352.00,5,120,9),(139,'Gabinete Aorus C700 Glass',440.00,2,24,9),(140,'Monitor Aorus FI32U 4K 144Hz',880.00,4,36,9),(141,'Teclado Aorus K1 Mecánico',100.00,15,24,9),(142,'Mouse Aorus M5 Optico',60.00,20,24,9),(143,'Antena Wifi Aorus Repuesto',25.00,30,0,9),(144,'Pasta Térmica Aorus',12.00,50,0,9),(145,'Silla Aorus AGC310',440.00,3,24,9),(146,'Robot Aorus Chibi Figure',40.00,10,0,9),(147,'Headset Arctis Nova Pro Wireless',350.00,20,24,10),(148,'Headset Arctis Nova 7P',180.00,30,24,10),(149,'Headset Arctis 1 Wireless',100.00,50,24,10),(150,'Teclado Apex Pro TKL (2023)',190.00,15,24,10),(151,'Teclado Apex 9 Mini',130.00,20,24,10),(152,'Mouse Aerox 3 Wireless Ghost',100.00,25,24,10),(153,'Mouse Rival 3',30.00,60,24,10),(154,'Mousepad QcK Prism XL Neo Noir',60.00,30,12,10),(155,'Mousepad QcK Heavy XXL',35.00,40,12,10),(156,'Parlantes Arena 7 2.1',330.00,5,24,10),(157,'Parlantes Arena 3 2.0',130.00,10,24,10),(158,'Microfono Alias Pro XLR',363.00,5,24,10),(159,'Batería Extra Nova Pro',30.00,40,6,10),(160,'Diadema Repuesto Ski Goggle',20.00,50,0,10),(161,'Cable ChatMix Dial Repuesto',25.00,20,6,10),(162,'HyperDrive Pro V1 Mouse',99.99,150,36,11),(164,'Laptop HyperDrive Extreme',3500.00,50,24,11);
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `series`
--

DROP TABLE IF EXISTS `series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `series` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_serie` varchar(50) NOT NULL,
  `publico_objetivo` varchar(50) DEFAULT NULL,
  `anio_lanzamiento` int(11) DEFAULT NULL,
  `id_fabricante` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_fabricante` (`id_fabricante`),
  CONSTRAINT `series_ibfk_1` FOREIGN KEY (`id_fabricante`) REFERENCES `fabricantes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `series`
--

LOCK TABLES `series` WRITE;
/*!40000 ALTER TABLE `series` DISABLE KEYS */;
INSERT INTO `series` VALUES (1,'Republic of Gamers (ROG)','Hardcore Gamer',2006,1),(2,'TUF Gaming','Durabilidad Militar',2010,1),(3,'MSI Dragon','Gaming General',2012,2),(4,'Razer Chroma','RGB Enthusiast',2015,3),(5,'Logitech G-Pro','E-Sports Profesional',2018,4),(6,'Vengeance','High Performance',2013,5),(7,'Cloud Series','Audio Comfort',2014,6),(8,'Legion','Gaming/Work Hybrid',2017,7),(9,'Aorus','Premium Gaming',2014,8),(10,'Arctis Nova','High Fidelity Audio',2022,9),(11,'HyperDrive Pro Gaming','Competitive Gaming',2025,11);
/*!40000 ALTER TABLE `series` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-21 15:14:14
