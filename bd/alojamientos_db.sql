-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: alojamientos_db
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
-- Current Database: `alojamientos_db`
--

/*!40000 DROP DATABASE IF EXISTS `alojamientos_db`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `alojamientos_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `alojamientos_db`;

--
-- Table structure for table `alojamientos`
--

DROP TABLE IF EXISTS `alojamientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alojamientos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tipo_alojamiento_id` bigint(20) unsigned NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `descripcion` text NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `pais_id` bigint(20) unsigned DEFAULT NULL,
  `empresa_id` bigint(20) unsigned DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `estrellas` tinyint(3) unsigned DEFAULT NULL,
  `valoracion_huesped` decimal(3,1) NOT NULL DEFAULT 9.0,
  `numero_resenas` int(10) unsigned NOT NULL DEFAULT 0,
  `hora_entrada` varchar(10) NOT NULL DEFAULT '15:00',
  `hora_salida` varchar(10) NOT NULL DEFAULT '11:00',
  `amenidades` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenidades`)),
  `politicas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`politicas`)),
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alojamientos_slug_unique` (`slug`),
  KEY `alojamientos_ciudad_index` (`ciudad`),
  KEY `idx_alojamientos_valoracion` (`valoracion_huesped`),
  KEY `alojamientos_tipo_alojamiento_id_foreign` (`tipo_alojamiento_id`),
  KEY `alojamientos_pais_id_foreign` (`pais_id`),
  KEY `alojamientos_empresa_id_foreign` (`empresa_id`),
  KEY `idx_alojamientos_busqueda` (`ciudad`,`tipo_alojamiento_id`,`estado`),
  CONSTRAINT `alojamientos_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `alojamientos_pais_id_foreign` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE SET NULL,
  CONSTRAINT `alojamientos_tipo_alojamiento_id_foreign` FOREIGN KEY (`tipo_alojamiento_id`) REFERENCES `tipos_alojamiento` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alojamientos`
--

LOCK TABLES `alojamientos` WRITE;
/*!40000 ALTER TABLE `alojamientos` DISABLE KEYS */;
INSERT INTO `alojamientos` VALUES (1,1,'Grand Palace Luxury Hotel & Spa','grand-palace-luxury-hotel-spa','Elegancia clásica y spa de clase mundial en el corazón de Madrid','Ubicado a pasos de los principales monumentos, el Grand Palace ofrece una experiencia cinco estrellas inolvidable. Disfrute de nuestra piscina climatizada en la azotea, spa galardonado y restaurante con estrellas Michelin.','Paseo de la Castellana 45','Madrid','Madrid',1,NULL,40.4283000,-3.6892000,5,9.6,342,'15:00','12:00','[\"wifi\",\"pool\",\"spa\",\"breakfast\",\"gym\",\"parking\",\"ac\",\"restaurant\",\"bar\"]','{\"cancellation\":\"Cancelaci\\u00f3n gratuita hasta 48 horas antes de la llegada.\",\"pets\":\"No se admiten mascotas.\",\"smoking\":\"Espacio 100% libre de humo.\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(2,1,'Riviera Sunset Beach Resort & Spa','riviera-sunset-beach-resort','Paraíso frente al mar turquesa con albercas infinitas y todo incluido','Ubicado directamente en la playa más hermosa de Cancún, este resort combina lujo relajado con espectaculares atardeceres caribeños, deportes acuáticos y alta gastronomía.','Blvd. Kukulcan Km 16.5, Zona Hotelera','Cancún','Quintana Roo',2,NULL,21.0921000,-86.7645000,5,9.7,512,'15:00','11:00','[\"wifi\",\"pool\",\"spa\",\"breakfast\",\"beachfront\",\"gym\",\"parking\",\"bar\",\"ac\"]','{\"cancellation\":\"Cancelaci\\u00f3n gratuita hasta 7 d\\u00edas antes de la llegada.\",\"pets\":\"Pet friendly (perros peque\\u00f1os hasta 10kg con costo adicional).\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(3,2,'Wanderlust Backpackers Social Hostel','wanderlust-backpackers-social-hostel','El mejor punto de encuentro para viajeros internacionales en Barcelona','Un ambiente vibrante, seguro y lleno de energía a pasos de Las Ramblas y el Barrio Gótico. Ofrecemos tours a pie gratuitos, eventos nocturnos, cocina común completamente equipada y espacios de coworking.','Carrer dels Tallers 18','Barcelona','Cataluña',1,NULL,41.3851000,2.1685000,NULL,9.3,840,'14:00','11:00','[\"wifi\",\"kitchen\",\"bar\",\"coworking\",\"laundry\",\"ac\",\"lockers\"]','{\"cancellation\":\"Cancelaci\\u00f3n gratuita hasta 24 horas antes del check-in.\",\"age\":\"Mayores de 18 a\\u00f1os.\",\"quiet_hours\":\"Silencio en dormitorios a partir de las 23:00.\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(4,3,'Cabañas Bosque Andino & Vistas al Lago','cabanas-bosque-andino-lago','Auténtica calidez patagónica con chimenea a leña y vistas panorámicas','Rodeada de coihues y cipreses milenarios frente al lago Nahuel Huapi. Nuestras cabañas de piedra y ciprés brindan el refugio perfecto para desconectar, disfrutar de un asado patagónico y relajarse al calor del fuego.','Av. Bustillo Km 12.4','Bariloche','Río Negro',3,NULL,-41.1335000,-71.3103000,NULL,9.8,218,'15:00','10:30','[\"wifi\",\"kitchen\",\"parking\",\"pets\",\"fireplace\",\"bbq\",\"heating\"]','{\"cancellation\":\"Cancelaci\\u00f3n flexible hasta 5 d\\u00edas antes.\",\"pets\":\"\\u00a1Admitimos mascotas con alegr\\u00eda y sin costo extra!\",\"wood\":\"Le\\u00f1a para chimenea incluida durante la estancia.\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(5,4,'Skyline View Modern Loft en El Poblado','skyline-view-modern-loft-poblado','Diseño vanguardista, WiFi ultra rápido y terraza con vista a la ciudad','Apartamento turístico moderno ubicado en el corazón del exclusivo barrio El Poblado. Cerca de los mejores cafés y restaurantes, con cerradura digital para entrada autónoma 24/7 y estación de trabajo ideal para nómadas digitales.','Cra 35 #7-12','Medellín','Antioquia',4,NULL,6.2088000,-75.5672000,NULL,9.5,176,'15:00','11:00','[\"wifi\",\"kitchen\",\"pool\",\"gym\",\"ac\",\"workspace\",\"laundry\",\"parking\"]','{\"cancellation\":\"Cancelaci\\u00f3n moderada: reembolso total hasta 5 d\\u00edas antes.\",\"self_check_in\":\"Self check-in con cerradura inteligente 24 horas.\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(6,5,'Eco-Dome Valle de las Estrellas','eco-dome-valle-de-las-estrellas','Domo geodésico de lujo bajo el cielo estrellado con jacuzzi privado','Dormir contemplando la vía láctea desde la comodidad de una cama king size. Nuestros domos geodésicos integran arquitectura bioclimática con el confort supremo: telescopio privado, fogatero exterior y desayuno gourmet artesanal.','Camino a los Álamos S/N','Valle de Bravo','Estado de México',2,NULL,19.1931000,-100.1345000,NULL,9.9,129,'16:00','12:00','[\"wifi\",\"breakfast\",\"parking\",\"hottub\",\"fireplace\",\"mountain_view\"]','{\"cancellation\":\"Cancelaci\\u00f3n estricta hasta 7 d\\u00edas antes con 50% de reembolso.\",\"breakfast\":\"Desayuno campestre incluido en la tarifa.\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(7,1,'Casa San Martín Suites','casa-san-martin-suites','Suites de servicio en el centro de Cochabamba para viajes de negocios','Pensado para el viajero profesional que visita Cochabamba por trabajo: check-in ágil, espacios de trabajo en cada suite y conexión de alta velocidad. Está a pasos de la Plaza 14 de Septiembre y del Mercado de las Brujas, y cuenta con servicio de lavandería y desayuno para empezar la jornada.','Calle Blanco Galindo entre Junín y Lope de Mendoza, Casco Viejo','Cochabamba','Cochabamba',5,NULL,-17.3895000,-66.1568000,5,9.4,312,'14:00','12:00','[\"wifi\",\"breakfast\",\"gym\",\"parking\",\"ac\",\"workspace\",\"laundry\"]','{\"cancellation\":\"Cancelaci\\u00f3n gratuita hasta 48 horas antes de la llegada.\",\"pets\":\"No se admiten mascotas.\",\"smoking\":\"Habitaciones 100% libres de humo.\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(8,1,'Hotel Boutique La Casa de Margarita','hotel-boutique-la-casa-de-margarita','Un jardín privado en el corazón de Cochabamba, a minutos del Palacio Portales','Casa de Margarita es una pequeña boutique con alma: solo 14 habitaciones, un jardín cercado de buganvilias y una terraza donde tomar el té. Se ubica en Queru Queru, el barrio más tranquilo de la ciudad, a pocos minutos del Palacio Portales y de la Avenida Blanco Galindo.','Pasaje Juan José Quezada, barrio Queru Queru','Cochabamba','Cochabamba',5,NULL,-17.3712000,-66.1834000,5,9.2,189,'15:00','11:00','[\"wifi\",\"breakfast\",\"parking\",\"restaurant\",\"bar\",\"garden\",\"ac\"]','{\"cancellation\":\"Cancelaci\\u00f3n gratuita hasta 72 horas antes de la llegada.\",\"pets\":\"Se admiten mascotas de peque\\u00f1o tama\\u00f1o con aprobaci\\u00f3n previa.\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(9,1,'Hotel Regina Resort & Convenciones','hotel-regina-resort-convenciones','El centro de convenciones más completo de Cochabamba, con salones para 800 personas','Diseñado para eventos: siete salones de convenciones equipados, piscina cubierta climatizada y espacios de trabajo. Es la opción habitual para los congresos y ferias de la ciudad, y también para quien viaja por trabajo y quiere cerrar el día con un rato largo de piscina.','Avenida Blanco Galindo s/n, barrio La Canota','Cochabamba','Cochabamba',5,NULL,-17.3856000,-66.1652000,5,8.8,204,'15:00','12:00','[\"wifi\",\"pool\",\"spa\",\"gym\",\"parking\",\"restaurant\",\"bar\",\"ac\",\"workspace\"]','{\"cancellation\":\"Cancelaci\\u00f3n gratuita hasta 7 d\\u00edas antes de la llegada.\",\"events\":\"Salones de convenciones disponibles para eventos y congresos.\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(10,1,'Gran Hotel Cochabamba','gran-hotel-cochabamba','Un refugio de diseño en plena Plaza Ubaldo Anze, entre la ciudad y la naturaleza','El Gran Hotel combina un edificio de finales de los cincuenta, restaurado con mucho criterio, con un spa de nueva generación y un jardín interior de agua. Está en la Plaza Ubaldo Anze, el corazón del Casco Viejo, a diez minutos del mercado y a veinte del Palacio Portales.','Plaza Ubaldo Anze E-415, Cercado','Cochabamba','Cochabamba',5,NULL,-17.3935000,-66.1591000,5,8.7,276,'15:00','12:00','[\"wifi\",\"spa\",\"breakfast\",\"gym\",\"parking\",\"restaurant\",\"bar\",\"ac\"]','{\"cancellation\":\"Cancelaci\\u00f3n gratuita hasta 48 horas antes de la llegada.\",\"smoking\":\"Habitaciones 100% libres de humo.\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(11,1,'Hotel Boutique Casamagna','hotel-boutique-casamagna','Piscina exterior, gimnasio y jardín en una zona residencial tranquila de Cochabamba','Casamagna ocupa una esquina tranquila del barrio de Cala Cala, una de las zonas más arboladas de la ciudad. Tiene piscina exterior, un gimnasio completo, jardín y un desayuno buffet que se ha ganado laaprecio de sus huéspedes. Ideal para quien viaja con alguien y quiere pasar un par de días sin salir de la ciudad.','Calle Obispo Anaya 260, Cala Cala','Cochabamba','Cochabamba',5,NULL,-17.3985000,-66.1706000,5,8.5,421,'14:00','12:00','[\"wifi\",\"pool\",\"gym\",\"breakfast\",\"parking\",\"ac\",\"restaurant\",\"pets\"]','{\"cancellation\":\"Cancelaci\\u00f3n gratuita hasta 48 horas antes de la llegada.\",\"pets\":\"Se admiten mascotas de peque\\u00f1o tama\\u00f1o.\"}',1,'2026-10-13 19:33:52','2026-10-13 19:33:52');
/*!40000 ALTER TABLE `alojamientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-catalogo-paises-es','a:245:{i:0;a:4:{s:4:\"iso2\";s:2:\"AF\";s:4:\"name\";s:11:\"Afganistán\";s:9:\"dial_code\";s:3:\"+93\";s:13:\"phone_example\";s:12:\"070 123 4567\";}i:1;a:4:{s:4:\"iso2\";s:2:\"AL\";s:4:\"name\";s:7:\"Albania\";s:9:\"dial_code\";s:4:\"+355\";s:13:\"phone_example\";s:12:\"067 212 3456\";}i:2;a:4:{s:4:\"iso2\";s:2:\"DE\";s:4:\"name\";s:8:\"Alemania\";s:9:\"dial_code\";s:3:\"+49\";s:13:\"phone_example\";s:13:\"01512 3456789\";}i:3;a:4:{s:4:\"iso2\";s:2:\"AD\";s:4:\"name\";s:7:\"Andorra\";s:9:\"dial_code\";s:4:\"+376\";s:13:\"phone_example\";s:7:\"312 345\";}i:4;a:4:{s:4:\"iso2\";s:2:\"AO\";s:4:\"name\";s:6:\"Angola\";s:9:\"dial_code\";s:4:\"+244\";s:13:\"phone_example\";s:11:\"923 123 456\";}i:5;a:4:{s:4:\"iso2\";s:2:\"AI\";s:4:\"name\";s:7:\"Anguila\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(264) 235-1234\";}i:6;a:4:{s:4:\"iso2\";s:2:\"AG\";s:4:\"name\";s:17:\"Antigua y Barbuda\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(268) 464-1234\";}i:7;a:4:{s:4:\"iso2\";s:2:\"SA\";s:4:\"name\";s:13:\"Arabia Saudí\";s:9:\"dial_code\";s:4:\"+966\";s:13:\"phone_example\";s:12:\"051 234 5678\";}i:8;a:4:{s:4:\"iso2\";s:2:\"DZ\";s:4:\"name\";s:7:\"Argelia\";s:9:\"dial_code\";s:4:\"+213\";s:13:\"phone_example\";s:13:\"0551 23 45 67\";}i:9;a:4:{s:4:\"iso2\";s:2:\"AR\";s:4:\"name\";s:9:\"Argentina\";s:9:\"dial_code\";s:3:\"+54\";s:13:\"phone_example\";s:16:\"011 15-2345-6789\";}i:10;a:4:{s:4:\"iso2\";s:2:\"AM\";s:4:\"name\";s:7:\"Armenia\";s:9:\"dial_code\";s:4:\"+374\";s:13:\"phone_example\";s:10:\"077 123456\";}i:11;a:4:{s:4:\"iso2\";s:2:\"AW\";s:4:\"name\";s:5:\"Aruba\";s:9:\"dial_code\";s:4:\"+297\";s:13:\"phone_example\";s:8:\"560 1234\";}i:12;a:4:{s:4:\"iso2\";s:2:\"AU\";s:4:\"name\";s:9:\"Australia\";s:9:\"dial_code\";s:3:\"+61\";s:13:\"phone_example\";s:12:\"0412 345 678\";}i:13;a:4:{s:4:\"iso2\";s:2:\"AT\";s:4:\"name\";s:7:\"Austria\";s:9:\"dial_code\";s:3:\"+43\";s:13:\"phone_example\";s:11:\"0664 123456\";}i:14;a:4:{s:4:\"iso2\";s:2:\"AZ\";s:4:\"name\";s:11:\"Azerbaiyán\";s:9:\"dial_code\";s:4:\"+994\";s:13:\"phone_example\";s:13:\"040 123 45 67\";}i:15;a:4:{s:4:\"iso2\";s:2:\"BS\";s:4:\"name\";s:7:\"Bahamas\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(242) 359-1234\";}i:16;a:4:{s:4:\"iso2\";s:2:\"BD\";s:4:\"name\";s:10:\"Bangladés\";s:9:\"dial_code\";s:4:\"+880\";s:13:\"phone_example\";s:12:\"01812-345678\";}i:17;a:4:{s:4:\"iso2\";s:2:\"BB\";s:4:\"name\";s:8:\"Barbados\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(246) 250-1234\";}i:18;a:4:{s:4:\"iso2\";s:2:\"BH\";s:4:\"name\";s:7:\"Baréin\";s:9:\"dial_code\";s:4:\"+973\";s:13:\"phone_example\";s:9:\"3600 1234\";}i:19;a:4:{s:4:\"iso2\";s:2:\"BE\";s:4:\"name\";s:8:\"Bélgica\";s:9:\"dial_code\";s:3:\"+32\";s:13:\"phone_example\";s:13:\"0450 00 12 34\";}i:20;a:4:{s:4:\"iso2\";s:2:\"BZ\";s:4:\"name\";s:6:\"Belice\";s:9:\"dial_code\";s:4:\"+501\";s:13:\"phone_example\";s:8:\"622-1234\";}i:21;a:4:{s:4:\"iso2\";s:2:\"BJ\";s:4:\"name\";s:6:\"Benín\";s:9:\"dial_code\";s:4:\"+229\";s:13:\"phone_example\";s:14:\"01 95 12 34 56\";}i:22;a:4:{s:4:\"iso2\";s:2:\"BM\";s:4:\"name\";s:8:\"Bermudas\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(441) 370-1234\";}i:23;a:4:{s:4:\"iso2\";s:2:\"BY\";s:4:\"name\";s:11:\"Bielorrusia\";s:9:\"dial_code\";s:4:\"+375\";s:13:\"phone_example\";s:15:\"8 029 491-19-11\";}i:24;a:4:{s:4:\"iso2\";s:2:\"BO\";s:4:\"name\";s:7:\"Bolivia\";s:9:\"dial_code\";s:4:\"+591\";s:13:\"phone_example\";s:8:\"71234567\";}i:25;a:4:{s:4:\"iso2\";s:2:\"BA\";s:4:\"name\";s:20:\"Bosnia y Herzegovina\";s:9:\"dial_code\";s:4:\"+387\";s:13:\"phone_example\";s:11:\"061 123 456\";}i:26;a:4:{s:4:\"iso2\";s:2:\"BW\";s:4:\"name\";s:8:\"Botsuana\";s:9:\"dial_code\";s:4:\"+267\";s:13:\"phone_example\";s:10:\"71 123 456\";}i:27;a:4:{s:4:\"iso2\";s:2:\"BR\";s:4:\"name\";s:6:\"Brasil\";s:9:\"dial_code\";s:3:\"+55\";s:13:\"phone_example\";s:15:\"(11) 96123-4567\";}i:28;a:4:{s:4:\"iso2\";s:2:\"BN\";s:4:\"name\";s:7:\"Brunéi\";s:9:\"dial_code\";s:4:\"+673\";s:13:\"phone_example\";s:8:\"712 3456\";}i:29;a:4:{s:4:\"iso2\";s:2:\"BG\";s:4:\"name\";s:8:\"Bulgaria\";s:9:\"dial_code\";s:4:\"+359\";s:13:\"phone_example\";s:11:\"043 012 345\";}i:30;a:4:{s:4:\"iso2\";s:2:\"BF\";s:4:\"name\";s:12:\"Burkina Faso\";s:9:\"dial_code\";s:4:\"+226\";s:13:\"phone_example\";s:11:\"70 12 34 56\";}i:31;a:4:{s:4:\"iso2\";s:2:\"BI\";s:4:\"name\";s:7:\"Burundi\";s:9:\"dial_code\";s:4:\"+257\";s:13:\"phone_example\";s:11:\"79 56 12 34\";}i:32;a:4:{s:4:\"iso2\";s:2:\"BT\";s:4:\"name\";s:6:\"Bután\";s:9:\"dial_code\";s:4:\"+975\";s:13:\"phone_example\";s:11:\"17 12 34 56\";}i:33;a:4:{s:4:\"iso2\";s:2:\"CV\";s:4:\"name\";s:10:\"Cabo Verde\";s:9:\"dial_code\";s:4:\"+238\";s:13:\"phone_example\";s:9:\"991 12 34\";}i:34;a:4:{s:4:\"iso2\";s:2:\"KH\";s:4:\"name\";s:7:\"Camboya\";s:9:\"dial_code\";s:4:\"+855\";s:13:\"phone_example\";s:11:\"091 234 567\";}i:35;a:4:{s:4:\"iso2\";s:2:\"CM\";s:4:\"name\";s:8:\"Camerún\";s:9:\"dial_code\";s:4:\"+237\";s:13:\"phone_example\";s:13:\"6 71 23 45 67\";}i:36;a:4:{s:4:\"iso2\";s:2:\"CA\";s:4:\"name\";s:7:\"Canadá\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(506) 234-5678\";}i:37;a:4:{s:4:\"iso2\";s:2:\"BQ\";s:4:\"name\";s:18:\"Caribe neerlandés\";s:9:\"dial_code\";s:4:\"+599\";s:13:\"phone_example\";s:8:\"318 1234\";}i:38;a:4:{s:4:\"iso2\";s:2:\"QA\";s:4:\"name\";s:5:\"Catar\";s:9:\"dial_code\";s:4:\"+974\";s:13:\"phone_example\";s:9:\"3312 3456\";}i:39;a:4:{s:4:\"iso2\";s:2:\"TD\";s:4:\"name\";s:4:\"Chad\";s:9:\"dial_code\";s:4:\"+235\";s:13:\"phone_example\";s:11:\"63 01 23 45\";}i:40;a:4:{s:4:\"iso2\";s:2:\"CZ\";s:4:\"name\";s:7:\"Chequia\";s:9:\"dial_code\";s:4:\"+420\";s:13:\"phone_example\";s:11:\"601 123 456\";}i:41;a:4:{s:4:\"iso2\";s:2:\"CL\";s:4:\"name\";s:5:\"Chile\";s:9:\"dial_code\";s:3:\"+56\";s:13:\"phone_example\";s:13:\"(2) 2123 4567\";}i:42;a:4:{s:4:\"iso2\";s:2:\"CN\";s:4:\"name\";s:5:\"China\";s:9:\"dial_code\";s:3:\"+86\";s:13:\"phone_example\";s:13:\"131 2345 6789\";}i:43;a:4:{s:4:\"iso2\";s:2:\"CY\";s:4:\"name\";s:6:\"Chipre\";s:9:\"dial_code\";s:4:\"+357\";s:13:\"phone_example\";s:9:\"96 123456\";}i:44;a:4:{s:4:\"iso2\";s:2:\"VA\";s:4:\"name\";s:19:\"Ciudad del Vaticano\";s:9:\"dial_code\";s:3:\"+39\";s:13:\"phone_example\";s:12:\"312 345 6789\";}i:45;a:4:{s:4:\"iso2\";s:2:\"CO\";s:4:\"name\";s:8:\"Colombia\";s:9:\"dial_code\";s:3:\"+57\";s:13:\"phone_example\";s:11:\"321 1234567\";}i:46;a:4:{s:4:\"iso2\";s:2:\"KM\";s:4:\"name\";s:7:\"Comoras\";s:9:\"dial_code\";s:4:\"+269\";s:13:\"phone_example\";s:9:\"321 23 45\";}i:47;a:4:{s:4:\"iso2\";s:2:\"CG\";s:4:\"name\";s:5:\"Congo\";s:9:\"dial_code\";s:4:\"+242\";s:13:\"phone_example\";s:11:\"06 123 4567\";}i:48;a:4:{s:4:\"iso2\";s:2:\"KP\";s:4:\"name\";s:15:\"Corea del Norte\";s:9:\"dial_code\";s:4:\"+850\";s:13:\"phone_example\";s:13:\"0192 123 4567\";}i:49;a:4:{s:4:\"iso2\";s:2:\"KR\";s:4:\"name\";s:13:\"Corea del Sur\";s:9:\"dial_code\";s:3:\"+82\";s:13:\"phone_example\";s:13:\"010-2000-0000\";}i:50;a:4:{s:4:\"iso2\";s:2:\"CR\";s:4:\"name\";s:10:\"Costa Rica\";s:9:\"dial_code\";s:4:\"+506\";s:13:\"phone_example\";s:9:\"8312 3456\";}i:51;a:4:{s:4:\"iso2\";s:2:\"CI\";s:4:\"name\";s:16:\"Côte d’Ivoire\";s:9:\"dial_code\";s:4:\"+225\";s:13:\"phone_example\";s:13:\"01 23 45 6789\";}i:52;a:4:{s:4:\"iso2\";s:2:\"HR\";s:4:\"name\";s:7:\"Croacia\";s:9:\"dial_code\";s:4:\"+385\";s:13:\"phone_example\";s:12:\"092 123 4567\";}i:53;a:4:{s:4:\"iso2\";s:2:\"CU\";s:4:\"name\";s:4:\"Cuba\";s:9:\"dial_code\";s:3:\"+53\";s:13:\"phone_example\";s:10:\"05 1234567\";}i:54;a:4:{s:4:\"iso2\";s:2:\"CW\";s:4:\"name\";s:7:\"Curazao\";s:9:\"dial_code\";s:4:\"+599\";s:13:\"phone_example\";s:10:\"9 518 1234\";}i:55;a:4:{s:4:\"iso2\";s:2:\"DK\";s:4:\"name\";s:9:\"Dinamarca\";s:9:\"dial_code\";s:3:\"+45\";s:13:\"phone_example\";s:11:\"34 41 23 45\";}i:56;a:4:{s:4:\"iso2\";s:2:\"DM\";s:4:\"name\";s:8:\"Dominica\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(767) 225-1234\";}i:57;a:4:{s:4:\"iso2\";s:2:\"EC\";s:4:\"name\";s:7:\"Ecuador\";s:9:\"dial_code\";s:4:\"+593\";s:13:\"phone_example\";s:12:\"099 123 4567\";}i:58;a:4:{s:4:\"iso2\";s:2:\"EG\";s:4:\"name\";s:6:\"Egipto\";s:9:\"dial_code\";s:3:\"+20\";s:13:\"phone_example\";s:12:\"010 01234567\";}i:59;a:4:{s:4:\"iso2\";s:2:\"SV\";s:4:\"name\";s:11:\"El Salvador\";s:9:\"dial_code\";s:4:\"+503\";s:13:\"phone_example\";s:9:\"7012 3456\";}i:60;a:4:{s:4:\"iso2\";s:2:\"AE\";s:4:\"name\";s:23:\"Emiratos Árabes Unidos\";s:9:\"dial_code\";s:4:\"+971\";s:13:\"phone_example\";s:12:\"050 123 4567\";}i:61;a:4:{s:4:\"iso2\";s:2:\"ER\";s:4:\"name\";s:7:\"Eritrea\";s:9:\"dial_code\";s:4:\"+291\";s:13:\"phone_example\";s:10:\"07 123 456\";}i:62;a:4:{s:4:\"iso2\";s:2:\"SK\";s:4:\"name\";s:10:\"Eslovaquia\";s:9:\"dial_code\";s:4:\"+421\";s:13:\"phone_example\";s:12:\"0912 123 456\";}i:63;a:4:{s:4:\"iso2\";s:2:\"SI\";s:4:\"name\";s:9:\"Eslovenia\";s:9:\"dial_code\";s:4:\"+386\";s:13:\"phone_example\";s:11:\"031 234 567\";}i:64;a:4:{s:4:\"iso2\";s:2:\"ES\";s:4:\"name\";s:7:\"España\";s:9:\"dial_code\";s:3:\"+34\";s:13:\"phone_example\";s:12:\"612 34 56 78\";}i:65;a:4:{s:4:\"iso2\";s:2:\"US\";s:4:\"name\";s:14:\"Estados Unidos\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(201) 555-0123\";}i:66;a:4:{s:4:\"iso2\";s:2:\"EE\";s:4:\"name\";s:7:\"Estonia\";s:9:\"dial_code\";s:4:\"+372\";s:13:\"phone_example\";s:9:\"5123 4567\";}i:67;a:4:{s:4:\"iso2\";s:2:\"SZ\";s:4:\"name\";s:8:\"Esuatini\";s:9:\"dial_code\";s:4:\"+268\";s:13:\"phone_example\";s:9:\"7612 3456\";}i:68;a:4:{s:4:\"iso2\";s:2:\"ET\";s:4:\"name\";s:8:\"Etiopía\";s:9:\"dial_code\";s:4:\"+251\";s:13:\"phone_example\";s:12:\"091 123 4567\";}i:69;a:4:{s:4:\"iso2\";s:2:\"PH\";s:4:\"name\";s:9:\"Filipinas\";s:9:\"dial_code\";s:3:\"+63\";s:13:\"phone_example\";s:13:\"0905 123 4567\";}i:70;a:4:{s:4:\"iso2\";s:2:\"FI\";s:4:\"name\";s:9:\"Finlandia\";s:9:\"dial_code\";s:4:\"+358\";s:13:\"phone_example\";s:11:\"041 2345678\";}i:71;a:4:{s:4:\"iso2\";s:2:\"FJ\";s:4:\"name\";s:4:\"Fiyi\";s:9:\"dial_code\";s:4:\"+679\";s:13:\"phone_example\";s:8:\"701 2345\";}i:72;a:4:{s:4:\"iso2\";s:2:\"FR\";s:4:\"name\";s:7:\"Francia\";s:9:\"dial_code\";s:3:\"+33\";s:13:\"phone_example\";s:14:\"06 12 34 56 78\";}i:73;a:4:{s:4:\"iso2\";s:2:\"GA\";s:4:\"name\";s:6:\"Gabón\";s:9:\"dial_code\";s:4:\"+241\";s:13:\"phone_example\";s:11:\"06 03 12 34\";}i:74;a:4:{s:4:\"iso2\";s:2:\"GM\";s:4:\"name\";s:6:\"Gambia\";s:9:\"dial_code\";s:4:\"+220\";s:13:\"phone_example\";s:8:\"301 2345\";}i:75;a:4:{s:4:\"iso2\";s:2:\"GE\";s:4:\"name\";s:7:\"Georgia\";s:9:\"dial_code\";s:4:\"+995\";s:13:\"phone_example\";s:12:\"555 12 34 56\";}i:76;a:4:{s:4:\"iso2\";s:2:\"GH\";s:4:\"name\";s:5:\"Ghana\";s:9:\"dial_code\";s:4:\"+233\";s:13:\"phone_example\";s:12:\"023 123 4567\";}i:77;a:4:{s:4:\"iso2\";s:2:\"GI\";s:4:\"name\";s:9:\"Gibraltar\";s:9:\"dial_code\";s:4:\"+350\";s:13:\"phone_example\";s:8:\"57123456\";}i:78;a:4:{s:4:\"iso2\";s:2:\"GD\";s:4:\"name\";s:7:\"Granada\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(473) 403-1234\";}i:79;a:4:{s:4:\"iso2\";s:2:\"GR\";s:4:\"name\";s:6:\"Grecia\";s:9:\"dial_code\";s:3:\"+30\";s:13:\"phone_example\";s:12:\"691 234 5678\";}i:80;a:4:{s:4:\"iso2\";s:2:\"GL\";s:4:\"name\";s:11:\"Groenlandia\";s:9:\"dial_code\";s:4:\"+299\";s:13:\"phone_example\";s:8:\"22 12 34\";}i:81;a:4:{s:4:\"iso2\";s:2:\"GP\";s:4:\"name\";s:9:\"Guadalupe\";s:9:\"dial_code\";s:4:\"+590\";s:13:\"phone_example\";s:13:\"0690 00 12 34\";}i:82;a:4:{s:4:\"iso2\";s:2:\"GU\";s:4:\"name\";s:4:\"Guam\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(671) 300-1234\";}i:83;a:4:{s:4:\"iso2\";s:2:\"GT\";s:4:\"name\";s:9:\"Guatemala\";s:9:\"dial_code\";s:4:\"+502\";s:13:\"phone_example\";s:9:\"5123 4567\";}i:84;a:4:{s:4:\"iso2\";s:2:\"GF\";s:4:\"name\";s:16:\"Guayana Francesa\";s:9:\"dial_code\";s:4:\"+594\";s:13:\"phone_example\";s:13:\"0694 20 12 34\";}i:85;a:4:{s:4:\"iso2\";s:2:\"GG\";s:4:\"name\";s:9:\"Guernesey\";s:9:\"dial_code\";s:3:\"+44\";s:13:\"phone_example\";s:12:\"07781 123456\";}i:86;a:4:{s:4:\"iso2\";s:2:\"GN\";s:4:\"name\";s:6:\"Guinea\";s:9:\"dial_code\";s:4:\"+224\";s:13:\"phone_example\";s:12:\"601 12 34 56\";}i:87;a:4:{s:4:\"iso2\";s:2:\"GQ\";s:4:\"name\";s:17:\"Guinea Ecuatorial\";s:9:\"dial_code\";s:4:\"+240\";s:13:\"phone_example\";s:11:\"222 123 456\";}i:88;a:4:{s:4:\"iso2\";s:2:\"GW\";s:4:\"name\";s:13:\"Guinea-Bisáu\";s:9:\"dial_code\";s:4:\"+245\";s:13:\"phone_example\";s:11:\"955 012 345\";}i:89;a:4:{s:4:\"iso2\";s:2:\"GY\";s:4:\"name\";s:6:\"Guyana\";s:9:\"dial_code\";s:4:\"+592\";s:13:\"phone_example\";s:8:\"609 1234\";}i:90;a:4:{s:4:\"iso2\";s:2:\"HT\";s:4:\"name\";s:6:\"Haití\";s:9:\"dial_code\";s:4:\"+509\";s:13:\"phone_example\";s:10:\"34 10 1234\";}i:91;a:4:{s:4:\"iso2\";s:2:\"HN\";s:4:\"name\";s:8:\"Honduras\";s:9:\"dial_code\";s:4:\"+504\";s:13:\"phone_example\";s:9:\"9123-4567\";}i:92;a:4:{s:4:\"iso2\";s:2:\"HU\";s:4:\"name\";s:8:\"Hungría\";s:9:\"dial_code\";s:3:\"+36\";s:13:\"phone_example\";s:14:\"06 20 123 4567\";}i:93;a:4:{s:4:\"iso2\";s:2:\"IN\";s:4:\"name\";s:5:\"India\";s:9:\"dial_code\";s:3:\"+91\";s:13:\"phone_example\";s:12:\"081234 56789\";}i:94;a:4:{s:4:\"iso2\";s:2:\"ID\";s:4:\"name\";s:9:\"Indonesia\";s:9:\"dial_code\";s:3:\"+62\";s:13:\"phone_example\";s:12:\"0812-345-678\";}i:95;a:4:{s:4:\"iso2\";s:2:\"IQ\";s:4:\"name\";s:4:\"Irak\";s:9:\"dial_code\";s:4:\"+964\";s:13:\"phone_example\";s:13:\"0791 234 5678\";}i:96;a:4:{s:4:\"iso2\";s:2:\"IR\";s:4:\"name\";s:5:\"Irán\";s:9:\"dial_code\";s:3:\"+98\";s:13:\"phone_example\";s:13:\"0912 345 6789\";}i:97;a:4:{s:4:\"iso2\";s:2:\"IE\";s:4:\"name\";s:7:\"Irlanda\";s:9:\"dial_code\";s:4:\"+353\";s:13:\"phone_example\";s:12:\"085 012 3456\";}i:98;a:4:{s:4:\"iso2\";s:2:\"AC\";s:4:\"name\";s:21:\"Isla de la Ascensión\";s:9:\"dial_code\";s:4:\"+247\";s:13:\"phone_example\";s:5:\"40123\";}i:99;a:4:{s:4:\"iso2\";s:2:\"IM\";s:4:\"name\";s:11:\"Isla de Man\";s:9:\"dial_code\";s:3:\"+44\";s:13:\"phone_example\";s:12:\"07924 123456\";}i:100;a:4:{s:4:\"iso2\";s:2:\"CX\";s:4:\"name\";s:15:\"Isla de Navidad\";s:9:\"dial_code\";s:3:\"+61\";s:13:\"phone_example\";s:12:\"0412 345 678\";}i:101;a:4:{s:4:\"iso2\";s:2:\"NF\";s:4:\"name\";s:12:\"Isla Norfolk\";s:9:\"dial_code\";s:4:\"+672\";s:13:\"phone_example\";s:7:\"3 81234\";}i:102;a:4:{s:4:\"iso2\";s:2:\"IS\";s:4:\"name\";s:8:\"Islandia\";s:9:\"dial_code\";s:4:\"+354\";s:13:\"phone_example\";s:8:\"611 1234\";}i:103;a:4:{s:4:\"iso2\";s:2:\"AX\";s:4:\"name\";s:11:\"Islas Aland\";s:9:\"dial_code\";s:4:\"+358\";s:13:\"phone_example\";s:11:\"041 2345678\";}i:104;a:4:{s:4:\"iso2\";s:2:\"KY\";s:4:\"name\";s:13:\"Islas Caimán\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(345) 323-1234\";}i:105;a:4:{s:4:\"iso2\";s:2:\"CC\";s:4:\"name\";s:11:\"Islas Cocos\";s:9:\"dial_code\";s:3:\"+61\";s:13:\"phone_example\";s:12:\"0412 345 678\";}i:106;a:4:{s:4:\"iso2\";s:2:\"CK\";s:4:\"name\";s:10:\"Islas Cook\";s:9:\"dial_code\";s:4:\"+682\";s:13:\"phone_example\";s:6:\"71 234\";}i:107;a:4:{s:4:\"iso2\";s:2:\"FO\";s:4:\"name\";s:11:\"Islas Feroe\";s:9:\"dial_code\";s:4:\"+298\";s:13:\"phone_example\";s:8:\"21 12 34\";}i:108;a:4:{s:4:\"iso2\";s:2:\"FK\";s:4:\"name\";s:14:\"Islas Malvinas\";s:9:\"dial_code\";s:4:\"+500\";s:13:\"phone_example\";s:5:\"51234\";}i:109;a:4:{s:4:\"iso2\";s:2:\"MP\";s:4:\"name\";s:24:\"Islas Marianas del Norte\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(670) 234-5678\";}i:110;a:4:{s:4:\"iso2\";s:2:\"MH\";s:4:\"name\";s:14:\"Islas Marshall\";s:9:\"dial_code\";s:4:\"+692\";s:13:\"phone_example\";s:8:\"235-1234\";}i:111;a:4:{s:4:\"iso2\";s:2:\"SB\";s:4:\"name\";s:14:\"Islas Salomón\";s:9:\"dial_code\";s:4:\"+677\";s:13:\"phone_example\";s:8:\"74 21234\";}i:112;a:4:{s:4:\"iso2\";s:2:\"TC\";s:4:\"name\";s:21:\"Islas Turcas y Caicos\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(649) 231-1234\";}i:113;a:4:{s:4:\"iso2\";s:2:\"VG\";s:4:\"name\";s:27:\"Islas Vírgenes Británicas\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(284) 300-1234\";}i:114;a:4:{s:4:\"iso2\";s:2:\"VI\";s:4:\"name\";s:26:\"Islas Vírgenes de EE. UU.\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(340) 642-1234\";}i:115;a:4:{s:4:\"iso2\";s:2:\"IL\";s:4:\"name\";s:6:\"Israel\";s:9:\"dial_code\";s:4:\"+972\";s:13:\"phone_example\";s:12:\"050-234-5678\";}i:116;a:4:{s:4:\"iso2\";s:2:\"IT\";s:4:\"name\";s:6:\"Italia\";s:9:\"dial_code\";s:3:\"+39\";s:13:\"phone_example\";s:12:\"312 345 6789\";}i:117;a:4:{s:4:\"iso2\";s:2:\"JM\";s:4:\"name\";s:7:\"Jamaica\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(876) 210-1234\";}i:118;a:4:{s:4:\"iso2\";s:2:\"JP\";s:4:\"name\";s:6:\"Japón\";s:9:\"dial_code\";s:3:\"+81\";s:13:\"phone_example\";s:13:\"090-1234-5678\";}i:119;a:4:{s:4:\"iso2\";s:2:\"JE\";s:4:\"name\";s:6:\"Jersey\";s:9:\"dial_code\";s:3:\"+44\";s:13:\"phone_example\";s:12:\"07797 712345\";}i:120;a:4:{s:4:\"iso2\";s:2:\"JO\";s:4:\"name\";s:8:\"Jordania\";s:9:\"dial_code\";s:4:\"+962\";s:13:\"phone_example\";s:12:\"07 9012 3456\";}i:121;a:4:{s:4:\"iso2\";s:2:\"KZ\";s:4:\"name\";s:11:\"Kazajistán\";s:9:\"dial_code\";s:2:\"+7\";s:13:\"phone_example\";s:16:\"8 (771) 000 9998\";}i:122;a:4:{s:4:\"iso2\";s:2:\"KE\";s:4:\"name\";s:5:\"Kenia\";s:9:\"dial_code\";s:4:\"+254\";s:13:\"phone_example\";s:11:\"0712 123456\";}i:123;a:4:{s:4:\"iso2\";s:2:\"KG\";s:4:\"name\";s:11:\"Kirguistán\";s:9:\"dial_code\";s:4:\"+996\";s:13:\"phone_example\";s:12:\"0700 123 456\";}i:124;a:4:{s:4:\"iso2\";s:2:\"KI\";s:4:\"name\";s:8:\"Kiribati\";s:9:\"dial_code\";s:4:\"+686\";s:13:\"phone_example\";s:8:\"72001234\";}i:125;a:4:{s:4:\"iso2\";s:2:\"XK\";s:4:\"name\";s:6:\"Kosovo\";s:9:\"dial_code\";s:4:\"+383\";s:13:\"phone_example\";s:11:\"043 201 234\";}i:126;a:4:{s:4:\"iso2\";s:2:\"KW\";s:4:\"name\";s:6:\"Kuwait\";s:9:\"dial_code\";s:4:\"+965\";s:13:\"phone_example\";s:9:\"500 12345\";}i:127;a:4:{s:4:\"iso2\";s:2:\"LA\";s:4:\"name\";s:4:\"Laos\";s:9:\"dial_code\";s:4:\"+856\";s:13:\"phone_example\";s:14:\"020 23 123 456\";}i:128;a:4:{s:4:\"iso2\";s:2:\"LS\";s:4:\"name\";s:6:\"Lesoto\";s:9:\"dial_code\";s:4:\"+266\";s:13:\"phone_example\";s:9:\"5012 3456\";}i:129;a:4:{s:4:\"iso2\";s:2:\"LV\";s:4:\"name\";s:7:\"Letonia\";s:9:\"dial_code\";s:4:\"+371\";s:13:\"phone_example\";s:10:\"21 234 567\";}i:130;a:4:{s:4:\"iso2\";s:2:\"LB\";s:4:\"name\";s:7:\"Líbano\";s:9:\"dial_code\";s:4:\"+961\";s:13:\"phone_example\";s:10:\"71 123 456\";}i:131;a:4:{s:4:\"iso2\";s:2:\"LR\";s:4:\"name\";s:7:\"Liberia\";s:9:\"dial_code\";s:4:\"+231\";s:13:\"phone_example\";s:12:\"077 012 3456\";}i:132;a:4:{s:4:\"iso2\";s:2:\"LY\";s:4:\"name\";s:5:\"Libia\";s:9:\"dial_code\";s:4:\"+218\";s:13:\"phone_example\";s:11:\"091-2345678\";}i:133;a:4:{s:4:\"iso2\";s:2:\"LI\";s:4:\"name\";s:13:\"Liechtenstein\";s:9:\"dial_code\";s:4:\"+423\";s:13:\"phone_example\";s:11:\"660 234 567\";}i:134;a:4:{s:4:\"iso2\";s:2:\"LT\";s:4:\"name\";s:8:\"Lituania\";s:9:\"dial_code\";s:4:\"+370\";s:13:\"phone_example\";s:13:\"(0-612) 34567\";}i:135;a:4:{s:4:\"iso2\";s:2:\"LU\";s:4:\"name\";s:10:\"Luxemburgo\";s:9:\"dial_code\";s:4:\"+352\";s:13:\"phone_example\";s:11:\"628 123 456\";}i:136;a:4:{s:4:\"iso2\";s:2:\"MK\";s:4:\"name\";s:19:\"Macedonia del Norte\";s:9:\"dial_code\";s:4:\"+389\";s:13:\"phone_example\";s:11:\"072 345 678\";}i:137;a:4:{s:4:\"iso2\";s:2:\"MG\";s:4:\"name\";s:10:\"Madagascar\";s:9:\"dial_code\";s:4:\"+261\";s:13:\"phone_example\";s:13:\"032 12 345 67\";}i:138;a:4:{s:4:\"iso2\";s:2:\"MY\";s:4:\"name\";s:7:\"Malasia\";s:9:\"dial_code\";s:3:\"+60\";s:13:\"phone_example\";s:12:\"012-345 6789\";}i:139;a:4:{s:4:\"iso2\";s:2:\"MW\";s:4:\"name\";s:6:\"Malaui\";s:9:\"dial_code\";s:4:\"+265\";s:13:\"phone_example\";s:13:\"0991 23 45 67\";}i:140;a:4:{s:4:\"iso2\";s:2:\"MV\";s:4:\"name\";s:8:\"Maldivas\";s:9:\"dial_code\";s:4:\"+960\";s:13:\"phone_example\";s:8:\"771-2345\";}i:141;a:4:{s:4:\"iso2\";s:2:\"ML\";s:4:\"name\";s:4:\"Mali\";s:9:\"dial_code\";s:4:\"+223\";s:13:\"phone_example\";s:11:\"65 01 23 45\";}i:142;a:4:{s:4:\"iso2\";s:2:\"MT\";s:4:\"name\";s:5:\"Malta\";s:9:\"dial_code\";s:4:\"+356\";s:13:\"phone_example\";s:9:\"9696 1234\";}i:143;a:4:{s:4:\"iso2\";s:2:\"MA\";s:4:\"name\";s:9:\"Marruecos\";s:9:\"dial_code\";s:4:\"+212\";s:13:\"phone_example\";s:14:\"06 50 12 34 56\";}i:144;a:4:{s:4:\"iso2\";s:2:\"MQ\";s:4:\"name\";s:9:\"Martinica\";s:9:\"dial_code\";s:4:\"+596\";s:13:\"phone_example\";s:13:\"0696 20 12 34\";}i:145;a:4:{s:4:\"iso2\";s:2:\"MU\";s:4:\"name\";s:8:\"Mauricio\";s:9:\"dial_code\";s:4:\"+230\";s:13:\"phone_example\";s:9:\"5251 2345\";}i:146;a:4:{s:4:\"iso2\";s:2:\"MR\";s:4:\"name\";s:10:\"Mauritania\";s:9:\"dial_code\";s:4:\"+222\";s:13:\"phone_example\";s:11:\"22 12 34 56\";}i:147;a:4:{s:4:\"iso2\";s:2:\"YT\";s:4:\"name\";s:7:\"Mayotte\";s:9:\"dial_code\";s:4:\"+262\";s:13:\"phone_example\";s:13:\"0639 01 23 45\";}i:148;a:4:{s:4:\"iso2\";s:2:\"MX\";s:4:\"name\";s:7:\"México\";s:9:\"dial_code\";s:3:\"+52\";s:13:\"phone_example\";s:12:\"222 123 4567\";}i:149;a:4:{s:4:\"iso2\";s:2:\"FM\";s:4:\"name\";s:10:\"Micronesia\";s:9:\"dial_code\";s:4:\"+691\";s:13:\"phone_example\";s:8:\"350 1234\";}i:150;a:4:{s:4:\"iso2\";s:2:\"MD\";s:4:\"name\";s:8:\"Moldavia\";s:9:\"dial_code\";s:4:\"+373\";s:13:\"phone_example\";s:11:\"0621 12 345\";}i:151;a:4:{s:4:\"iso2\";s:2:\"MC\";s:4:\"name\";s:7:\"Mónaco\";s:9:\"dial_code\";s:4:\"+377\";s:13:\"phone_example\";s:14:\"06 12 34 56 78\";}i:152;a:4:{s:4:\"iso2\";s:2:\"MN\";s:4:\"name\";s:8:\"Mongolia\";s:9:\"dial_code\";s:4:\"+976\";s:13:\"phone_example\";s:9:\"8812 3456\";}i:153;a:4:{s:4:\"iso2\";s:2:\"ME\";s:4:\"name\";s:10:\"Montenegro\";s:9:\"dial_code\";s:4:\"+382\";s:13:\"phone_example\";s:11:\"060 123 456\";}i:154;a:4:{s:4:\"iso2\";s:2:\"MS\";s:4:\"name\";s:10:\"Montserrat\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(664) 492-3456\";}i:155;a:4:{s:4:\"iso2\";s:2:\"MZ\";s:4:\"name\";s:10:\"Mozambique\";s:9:\"dial_code\";s:4:\"+258\";s:13:\"phone_example\";s:11:\"82 123 4567\";}i:156;a:4:{s:4:\"iso2\";s:2:\"MM\";s:4:\"name\";s:18:\"Myanmar (Birmania)\";s:9:\"dial_code\";s:3:\"+95\";s:13:\"phone_example\";s:11:\"09 212 3456\";}i:157;a:4:{s:4:\"iso2\";s:2:\"NA\";s:4:\"name\";s:7:\"Namibia\";s:9:\"dial_code\";s:4:\"+264\";s:13:\"phone_example\";s:12:\"081 123 4567\";}i:158;a:4:{s:4:\"iso2\";s:2:\"NR\";s:4:\"name\";s:5:\"Nauru\";s:9:\"dial_code\";s:4:\"+674\";s:13:\"phone_example\";s:8:\"555 1234\";}i:159;a:4:{s:4:\"iso2\";s:2:\"NP\";s:4:\"name\";s:5:\"Nepal\";s:9:\"dial_code\";s:4:\"+977\";s:13:\"phone_example\";s:11:\"984-1234567\";}i:160;a:4:{s:4:\"iso2\";s:2:\"NI\";s:4:\"name\";s:9:\"Nicaragua\";s:9:\"dial_code\";s:4:\"+505\";s:13:\"phone_example\";s:9:\"8123 4567\";}i:161;a:4:{s:4:\"iso2\";s:2:\"NE\";s:4:\"name\";s:6:\"Níger\";s:9:\"dial_code\";s:4:\"+227\";s:13:\"phone_example\";s:11:\"93 12 34 56\";}i:162;a:4:{s:4:\"iso2\";s:2:\"NG\";s:4:\"name\";s:7:\"Nigeria\";s:9:\"dial_code\";s:4:\"+234\";s:13:\"phone_example\";s:13:\"0802 123 4567\";}i:163;a:4:{s:4:\"iso2\";s:2:\"NU\";s:4:\"name\";s:4:\"Niue\";s:9:\"dial_code\";s:4:\"+683\";s:13:\"phone_example\";s:8:\"888 4012\";}i:164;a:4:{s:4:\"iso2\";s:2:\"NO\";s:4:\"name\";s:7:\"Noruega\";s:9:\"dial_code\";s:3:\"+47\";s:13:\"phone_example\";s:11:\"40 61 23 45\";}i:165;a:4:{s:4:\"iso2\";s:2:\"NC\";s:4:\"name\";s:15:\"Nueva Caledonia\";s:9:\"dial_code\";s:4:\"+687\";s:13:\"phone_example\";s:8:\"75.12.34\";}i:166;a:4:{s:4:\"iso2\";s:2:\"NZ\";s:4:\"name\";s:13:\"Nueva Zelanda\";s:9:\"dial_code\";s:3:\"+64\";s:13:\"phone_example\";s:12:\"021 123 4567\";}i:167;a:4:{s:4:\"iso2\";s:2:\"OM\";s:4:\"name\";s:5:\"Omán\";s:9:\"dial_code\";s:4:\"+968\";s:13:\"phone_example\";s:9:\"9212 3456\";}i:168;a:4:{s:4:\"iso2\";s:2:\"NL\";s:4:\"name\";s:13:\"Países Bajos\";s:9:\"dial_code\";s:3:\"+31\";s:13:\"phone_example\";s:11:\"06 12345678\";}i:169;a:4:{s:4:\"iso2\";s:2:\"PK\";s:4:\"name\";s:9:\"Pakistán\";s:9:\"dial_code\";s:3:\"+92\";s:13:\"phone_example\";s:12:\"0301 2345678\";}i:170;a:4:{s:4:\"iso2\";s:2:\"PW\";s:4:\"name\";s:6:\"Palaos\";s:9:\"dial_code\";s:4:\"+680\";s:13:\"phone_example\";s:8:\"620 1234\";}i:171;a:4:{s:4:\"iso2\";s:2:\"PA\";s:4:\"name\";s:7:\"Panamá\";s:9:\"dial_code\";s:4:\"+507\";s:13:\"phone_example\";s:9:\"6123-4567\";}i:172;a:4:{s:4:\"iso2\";s:2:\"PG\";s:4:\"name\";s:19:\"Papúa Nueva Guinea\";s:9:\"dial_code\";s:4:\"+675\";s:13:\"phone_example\";s:9:\"7012 3456\";}i:173;a:4:{s:4:\"iso2\";s:2:\"PY\";s:4:\"name\";s:8:\"Paraguay\";s:9:\"dial_code\";s:4:\"+595\";s:13:\"phone_example\";s:11:\"0961 456789\";}i:174;a:4:{s:4:\"iso2\";s:2:\"PE\";s:4:\"name\";s:5:\"Perú\";s:9:\"dial_code\";s:3:\"+51\";s:13:\"phone_example\";s:11:\"912 345 678\";}i:175;a:4:{s:4:\"iso2\";s:2:\"PF\";s:4:\"name\";s:18:\"Polinesia Francesa\";s:9:\"dial_code\";s:4:\"+689\";s:13:\"phone_example\";s:11:\"87 12 34 56\";}i:176;a:4:{s:4:\"iso2\";s:2:\"PL\";s:4:\"name\";s:7:\"Polonia\";s:9:\"dial_code\";s:3:\"+48\";s:13:\"phone_example\";s:11:\"512 345 678\";}i:177;a:4:{s:4:\"iso2\";s:2:\"PT\";s:4:\"name\";s:8:\"Portugal\";s:9:\"dial_code\";s:4:\"+351\";s:13:\"phone_example\";s:11:\"912 345 678\";}i:178;a:4:{s:4:\"iso2\";s:2:\"PR\";s:4:\"name\";s:11:\"Puerto Rico\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(787) 234-5678\";}i:179;a:4:{s:4:\"iso2\";s:2:\"HK\";s:4:\"name\";s:24:\"RAE de Hong Kong (China)\";s:9:\"dial_code\";s:4:\"+852\";s:13:\"phone_example\";s:9:\"5123 4567\";}i:180;a:4:{s:4:\"iso2\";s:2:\"MO\";s:4:\"name\";s:20:\"RAE de Macao (China)\";s:9:\"dial_code\";s:4:\"+853\";s:13:\"phone_example\";s:9:\"6612 3456\";}i:181;a:4:{s:4:\"iso2\";s:2:\"GB\";s:4:\"name\";s:11:\"Reino Unido\";s:9:\"dial_code\";s:3:\"+44\";s:13:\"phone_example\";s:12:\"07400 123456\";}i:182;a:4:{s:4:\"iso2\";s:2:\"CF\";s:4:\"name\";s:25:\"República Centroafricana\";s:9:\"dial_code\";s:4:\"+236\";s:13:\"phone_example\";s:11:\"70 01 23 45\";}i:183;a:4:{s:4:\"iso2\";s:2:\"CD\";s:4:\"name\";s:33:\"República Democrática del Congo\";s:9:\"dial_code\";s:4:\"+243\";s:13:\"phone_example\";s:12:\"0991 234 567\";}i:184;a:4:{s:4:\"iso2\";s:2:\"DO\";s:4:\"name\";s:21:\"República Dominicana\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(809) 234-5678\";}i:185;a:4:{s:4:\"iso2\";s:2:\"RE\";s:4:\"name\";s:8:\"Reunión\";s:9:\"dial_code\";s:4:\"+262\";s:13:\"phone_example\";s:13:\"0692 12 34 56\";}i:186;a:4:{s:4:\"iso2\";s:2:\"RW\";s:4:\"name\";s:6:\"Ruanda\";s:9:\"dial_code\";s:4:\"+250\";s:13:\"phone_example\";s:12:\"0720 123 456\";}i:187;a:4:{s:4:\"iso2\";s:2:\"RO\";s:4:\"name\";s:8:\"Rumanía\";s:9:\"dial_code\";s:3:\"+40\";s:13:\"phone_example\";s:12:\"0712 034 567\";}i:188;a:4:{s:4:\"iso2\";s:2:\"RU\";s:4:\"name\";s:5:\"Rusia\";s:9:\"dial_code\";s:2:\"+7\";s:13:\"phone_example\";s:17:\"8 (912) 345-67-89\";}i:189;a:4:{s:4:\"iso2\";s:2:\"EH\";s:4:\"name\";s:18:\"Sáhara Occidental\";s:9:\"dial_code\";s:4:\"+212\";s:13:\"phone_example\";s:14:\"06 50 12 34 56\";}i:190;a:4:{s:4:\"iso2\";s:2:\"WS\";s:4:\"name\";s:5:\"Samoa\";s:9:\"dial_code\";s:4:\"+685\";s:13:\"phone_example\";s:8:\"72 12345\";}i:191;a:4:{s:4:\"iso2\";s:2:\"AS\";s:4:\"name\";s:15:\"Samoa Americana\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(684) 733-1234\";}i:192;a:4:{s:4:\"iso2\";s:2:\"BL\";s:4:\"name\";s:14:\"San Bartolomé\";s:9:\"dial_code\";s:4:\"+590\";s:13:\"phone_example\";s:13:\"0690 00 12 34\";}i:193;a:4:{s:4:\"iso2\";s:2:\"KN\";s:4:\"name\";s:23:\"San Cristóbal y Nieves\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(869) 765-2917\";}i:194;a:4:{s:4:\"iso2\";s:2:\"SM\";s:4:\"name\";s:10:\"San Marino\";s:9:\"dial_code\";s:4:\"+378\";s:13:\"phone_example\";s:11:\"66 66 12 12\";}i:195;a:4:{s:4:\"iso2\";s:2:\"MF\";s:4:\"name\";s:11:\"San Martín\";s:9:\"dial_code\";s:4:\"+590\";s:13:\"phone_example\";s:13:\"0690 00 12 34\";}i:196;a:4:{s:4:\"iso2\";s:2:\"PM\";s:4:\"name\";s:21:\"San Pedro y Miquelón\";s:9:\"dial_code\";s:4:\"+508\";s:13:\"phone_example\";s:9:\"055 12 34\";}i:197;a:4:{s:4:\"iso2\";s:2:\"VC\";s:4:\"name\";s:28:\"San Vicente y las Granadinas\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(784) 430-1234\";}i:198;a:4:{s:4:\"iso2\";s:2:\"SH\";s:4:\"name\";s:11:\"Santa Elena\";s:9:\"dial_code\";s:4:\"+290\";s:13:\"phone_example\";s:5:\"51234\";}i:199;a:4:{s:4:\"iso2\";s:2:\"LC\";s:4:\"name\";s:12:\"Santa Lucía\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(758) 284-5678\";}i:200;a:4:{s:4:\"iso2\";s:2:\"ST\";s:4:\"name\";s:23:\"Santo Tomé y Príncipe\";s:9:\"dial_code\";s:4:\"+239\";s:13:\"phone_example\";s:8:\"981 2345\";}i:201;a:4:{s:4:\"iso2\";s:2:\"SN\";s:4:\"name\";s:7:\"Senegal\";s:9:\"dial_code\";s:4:\"+221\";s:13:\"phone_example\";s:12:\"70 123 45 67\";}i:202;a:4:{s:4:\"iso2\";s:2:\"RS\";s:4:\"name\";s:6:\"Serbia\";s:9:\"dial_code\";s:4:\"+381\";s:13:\"phone_example\";s:11:\"060 1234567\";}i:203;a:4:{s:4:\"iso2\";s:2:\"SC\";s:4:\"name\";s:10:\"Seychelles\";s:9:\"dial_code\";s:4:\"+248\";s:13:\"phone_example\";s:9:\"2 510 123\";}i:204;a:4:{s:4:\"iso2\";s:2:\"SL\";s:4:\"name\";s:12:\"Sierra Leona\";s:9:\"dial_code\";s:4:\"+232\";s:13:\"phone_example\";s:12:\"(025) 123456\";}i:205;a:4:{s:4:\"iso2\";s:2:\"SG\";s:4:\"name\";s:8:\"Singapur\";s:9:\"dial_code\";s:3:\"+65\";s:13:\"phone_example\";s:9:\"8123 4567\";}i:206;a:4:{s:4:\"iso2\";s:2:\"SX\";s:4:\"name\";s:12:\"Sint Maarten\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(721) 520-5678\";}i:207;a:4:{s:4:\"iso2\";s:2:\"SY\";s:4:\"name\";s:5:\"Siria\";s:9:\"dial_code\";s:4:\"+963\";s:13:\"phone_example\";s:12:\"0944 567 890\";}i:208;a:4:{s:4:\"iso2\";s:2:\"SO\";s:4:\"name\";s:7:\"Somalia\";s:9:\"dial_code\";s:4:\"+252\";s:13:\"phone_example\";s:9:\"7 1123456\";}i:209;a:4:{s:4:\"iso2\";s:2:\"LK\";s:4:\"name\";s:9:\"Sri Lanka\";s:9:\"dial_code\";s:3:\"+94\";s:13:\"phone_example\";s:12:\"071 234 5678\";}i:210;a:4:{s:4:\"iso2\";s:2:\"ZA\";s:4:\"name\";s:10:\"Sudáfrica\";s:9:\"dial_code\";s:3:\"+27\";s:13:\"phone_example\";s:12:\"071 123 4567\";}i:211;a:4:{s:4:\"iso2\";s:2:\"SD\";s:4:\"name\";s:6:\"Sudán\";s:9:\"dial_code\";s:4:\"+249\";s:13:\"phone_example\";s:12:\"091 123 1234\";}i:212;a:4:{s:4:\"iso2\";s:2:\"SS\";s:4:\"name\";s:14:\"Sudán del Sur\";s:9:\"dial_code\";s:4:\"+211\";s:13:\"phone_example\";s:12:\"0977 123 456\";}i:213;a:4:{s:4:\"iso2\";s:2:\"SE\";s:4:\"name\";s:6:\"Suecia\";s:9:\"dial_code\";s:3:\"+46\";s:13:\"phone_example\";s:13:\"070-123 45 67\";}i:214;a:4:{s:4:\"iso2\";s:2:\"CH\";s:4:\"name\";s:5:\"Suiza\";s:9:\"dial_code\";s:3:\"+41\";s:13:\"phone_example\";s:13:\"078 123 45 67\";}i:215;a:4:{s:4:\"iso2\";s:2:\"SR\";s:4:\"name\";s:7:\"Surinam\";s:9:\"dial_code\";s:4:\"+597\";s:13:\"phone_example\";s:8:\"741-2345\";}i:216;a:4:{s:4:\"iso2\";s:2:\"SJ\";s:4:\"name\";s:20:\"Svalbard y Jan Mayen\";s:9:\"dial_code\";s:3:\"+47\";s:13:\"phone_example\";s:11:\"41 23 45 67\";}i:217;a:4:{s:4:\"iso2\";s:2:\"TH\";s:4:\"name\";s:9:\"Tailandia\";s:9:\"dial_code\";s:3:\"+66\";s:13:\"phone_example\";s:12:\"081 234 5678\";}i:218;a:4:{s:4:\"iso2\";s:2:\"TW\";s:4:\"name\";s:7:\"Taiwán\";s:9:\"dial_code\";s:4:\"+886\";s:13:\"phone_example\";s:12:\"0912 345 678\";}i:219;a:4:{s:4:\"iso2\";s:2:\"TZ\";s:4:\"name\";s:8:\"Tanzania\";s:9:\"dial_code\";s:4:\"+255\";s:13:\"phone_example\";s:12:\"0621 234 567\";}i:220;a:4:{s:4:\"iso2\";s:2:\"TJ\";s:4:\"name\";s:11:\"Tayikistán\";s:9:\"dial_code\";s:4:\"+992\";s:13:\"phone_example\";s:11:\"91 712 3456\";}i:221;a:4:{s:4:\"iso2\";s:2:\"IO\";s:4:\"name\";s:41:\"Territorio Británico del Océano Índico\";s:9:\"dial_code\";s:4:\"+246\";s:13:\"phone_example\";s:8:\"380 1234\";}i:222;a:4:{s:4:\"iso2\";s:2:\"PS\";s:4:\"name\";s:22:\"Territorios Palestinos\";s:9:\"dial_code\";s:4:\"+970\";s:13:\"phone_example\";s:12:\"0599 123 456\";}i:223;a:4:{s:4:\"iso2\";s:2:\"TL\";s:4:\"name\";s:11:\"Timor-Leste\";s:9:\"dial_code\";s:4:\"+670\";s:13:\"phone_example\";s:9:\"7721 2345\";}i:224;a:4:{s:4:\"iso2\";s:2:\"TG\";s:4:\"name\";s:4:\"Togo\";s:9:\"dial_code\";s:4:\"+228\";s:13:\"phone_example\";s:11:\"90 11 23 45\";}i:225;a:4:{s:4:\"iso2\";s:2:\"TK\";s:4:\"name\";s:7:\"Tokelau\";s:9:\"dial_code\";s:4:\"+690\";s:13:\"phone_example\";s:4:\"7290\";}i:226;a:4:{s:4:\"iso2\";s:2:\"TO\";s:4:\"name\";s:5:\"Tonga\";s:9:\"dial_code\";s:4:\"+676\";s:13:\"phone_example\";s:8:\"771 5123\";}i:227;a:4:{s:4:\"iso2\";s:2:\"TT\";s:4:\"name\";s:17:\"Trinidad y Tobago\";s:9:\"dial_code\";s:2:\"+1\";s:13:\"phone_example\";s:14:\"(868) 291-1234\";}i:228;a:4:{s:4:\"iso2\";s:2:\"TA\";s:4:\"name\";s:18:\"Tristán de Acuña\";s:9:\"dial_code\";s:4:\"+290\";s:13:\"phone_example\";s:4:\"8999\";}i:229;a:4:{s:4:\"iso2\";s:2:\"TN\";s:4:\"name\";s:6:\"Túnez\";s:9:\"dial_code\";s:4:\"+216\";s:13:\"phone_example\";s:10:\"20 123 456\";}i:230;a:4:{s:4:\"iso2\";s:2:\"TM\";s:4:\"name\";s:13:\"Turkmenistán\";s:9:\"dial_code\";s:4:\"+993\";s:13:\"phone_example\";s:11:\"8 66 123456\";}i:231;a:4:{s:4:\"iso2\";s:2:\"TR\";s:4:\"name\";s:8:\"Turquía\";s:9:\"dial_code\";s:3:\"+90\";s:13:\"phone_example\";s:14:\"0501 234 56 78\";}i:232;a:4:{s:4:\"iso2\";s:2:\"TV\";s:4:\"name\";s:6:\"Tuvalu\";s:9:\"dial_code\";s:4:\"+688\";s:13:\"phone_example\";s:7:\"90 1234\";}i:233;a:4:{s:4:\"iso2\";s:2:\"UA\";s:4:\"name\";s:7:\"Ucrania\";s:9:\"dial_code\";s:4:\"+380\";s:13:\"phone_example\";s:12:\"050 123 4567\";}i:234;a:4:{s:4:\"iso2\";s:2:\"UG\";s:4:\"name\";s:6:\"Uganda\";s:9:\"dial_code\";s:4:\"+256\";s:13:\"phone_example\";s:11:\"0712 345678\";}i:235;a:4:{s:4:\"iso2\";s:2:\"UY\";s:4:\"name\";s:7:\"Uruguay\";s:9:\"dial_code\";s:4:\"+598\";s:13:\"phone_example\";s:11:\"094 231 234\";}i:236;a:4:{s:4:\"iso2\";s:2:\"UZ\";s:4:\"name\";s:11:\"Uzbekistán\";s:9:\"dial_code\";s:4:\"+998\";s:13:\"phone_example\";s:12:\"91 234 56 78\";}i:237;a:4:{s:4:\"iso2\";s:2:\"VU\";s:4:\"name\";s:7:\"Vanuatu\";s:9:\"dial_code\";s:4:\"+678\";s:13:\"phone_example\";s:8:\"591 2345\";}i:238;a:4:{s:4:\"iso2\";s:2:\"VE\";s:4:\"name\";s:9:\"Venezuela\";s:9:\"dial_code\";s:3:\"+58\";s:13:\"phone_example\";s:12:\"0412-1234567\";}i:239;a:4:{s:4:\"iso2\";s:2:\"VN\";s:4:\"name\";s:7:\"Vietnam\";s:9:\"dial_code\";s:3:\"+84\";s:13:\"phone_example\";s:12:\"0912 345 678\";}i:240;a:4:{s:4:\"iso2\";s:2:\"WF\";s:4:\"name\";s:15:\"Wallis y Futuna\";s:9:\"dial_code\";s:4:\"+681\";s:13:\"phone_example\";s:8:\"82 12 34\";}i:241;a:4:{s:4:\"iso2\";s:2:\"YE\";s:4:\"name\";s:5:\"Yemen\";s:9:\"dial_code\";s:4:\"+967\";s:13:\"phone_example\";s:12:\"0712 345 678\";}i:242;a:4:{s:4:\"iso2\";s:2:\"DJ\";s:4:\"name\";s:6:\"Yibuti\";s:9:\"dial_code\";s:4:\"+253\";s:13:\"phone_example\";s:11:\"77 83 10 01\";}i:243;a:4:{s:4:\"iso2\";s:2:\"ZM\";s:4:\"name\";s:6:\"Zambia\";s:9:\"dial_code\";s:4:\"+260\";s:13:\"phone_example\";s:11:\"095 5123456\";}i:244;a:4:{s:4:\"iso2\";s:2:\"ZW\";s:4:\"name\";s:8:\"Zimbabue\";s:9:\"dial_code\";s:4:\"+263\";s:13:\"phone_example\";s:12:\"071 234 5678\";}}',2107348261);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `destinos`
--

DROP TABLE IF EXISTS `destinos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `destinos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pais_id` bigint(20) unsigned NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `slug` varchar(170) NOT NULL,
  `ciudad` varchar(150) DEFAULT NULL,
  `texto_presentacion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`texto_presentacion`)),
  `imagen` varchar(500) DEFAULT NULL,
  `latitud` decimal(10,7) DEFAULT NULL,
  `longitud` decimal(10,7) DEFAULT NULL,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `orden` smallint(5) unsigned NOT NULL DEFAULT 0,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `destinos_slug_unique` (`slug`),
  KEY `destinos_pais_id_orden_index` (`pais_id`,`orden`),
  CONSTRAINT `destinos_pais_id_foreign` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destinos`
--

LOCK TABLES `destinos` WRITE;
/*!40000 ALTER TABLE `destinos` DISABLE KEYS */;
INSERT INTO `destinos` VALUES (1,5,'Salar de Uyuni','salar-de-uyuni','Uyuni','{\"es\":\"El desierto de sal m\\u00e1s grande del mundo, con 10.582 km\\u00b2 de horizonte blanco que se convierte en espejo durante la \\u00e9poca de lluvias. Alrededor esperan el cementerio de trenes, las lagunas de colores y los geiseres del Siloli.\",\"en\":\"The largest salt flat on Earth, 10,582 km\\u00b2 of white horizon that turns into a mirror during the rainy season. Around it lie the train cemetery, the coloured lagoons and the geysers of Siloli.\"}','https://images.unsplash.com/photo-1499696010180-025ef6e1a8f9?auto=format&fit=crop&w=1600&q=80',-20.1338000,-67.4891000,1,1,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(2,5,'La Paz','la-paz','La Paz','{\"es\":\"La sede de gobierno m\\u00e1s alta del mundo, encajada en un ca\\u00f1\\u00f3n andino. Su telef\\u00e9rico urbano, los mercados de la calle Sag\\u00e1rnaga y el mirador de Killi Killi son la mejor forma de entender la ciudad.\",\"en\":\"The world\'s highest seat of government, wedged into an Andean canyon. Its urban cable car, the markets of Sag\\u00e1rnaga street and the Killi Killi viewpoint are the best way to understand the city.\"}','https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1600&q=80',-16.5000000,-68.1500000,1,2,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(3,5,'Sucre','sucre','Sucre','{\"es\":\"La capital hist\\u00f3rica de Bolivia, declarada Patrimonio de la Humanidad: casas encaladas, balcones de madera y la Plaza 25 de Mayo. Es una ciudad universitaria, tranquila y de clima privilegiado.\",\"en\":\"Bolivia\'s historic capital, a UNESCO World Heritage Site: whitewashed houses, wooden balconies and Plaza 25 de Mayo. A calm, student city with a privileged climate.\"}','https://images.unsplash.com/photo-1529290130-4ca3753253ae?auto=format&fit=crop&w=1600&q=80',-19.0333000,-65.2627000,0,3,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(4,5,'Potosí','potosi','Potosí','{\"es\":\"La ciudad que se construy\\u00f3 sobre la plata del Cerro Rico, a m\\u00e1s de 4.000 m de altitud. Su Casa de la Moneda y el recorrido por las minas cuentan la historia que financi\\u00f3 un imperio.\",\"en\":\"The city built on the silver of Cerro Rico, above 4,000 m of altitude. Its Mint and the mine tours tell the story that financed an empire.\"}','https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=80',-19.5836000,-65.7531000,0,4,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(5,5,'Copacabana y el Titicaca','copacabana-titicaca','Copacabana','{\"es\":\"A orillas del lago navegable m\\u00e1s alto del mundo, Copacabana es la puerta a la Isla del Sol y su red de senderos incas. La bas\\u00edlica y la virgen morena atraen peregrinos de todo el pa\\u00eds.\",\"en\":\"On the shore of the world\'s highest navigable lake, Copacabana is the gateway to the Island of the Sun and its network of Inca trails. The basilica and its dark-skinned Virgin draw pilgrims from across the country.\"}','https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=1600&q=80',-16.1667000,-69.0833000,0,5,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(6,5,'Santa Cruz de la Sierra','santa-cruz-de-la-sierra','Santa Cruz de la Sierra','{\"es\":\"La ciudad m\\u00e1s grande y c\\u00e1lida de Bolivia, motor econ\\u00f3mico del oriente. Es la base para explorar los Valles Cruce\\u00f1os, las misiones jesu\\u00edticas de Chiquitos y el pantanal boliviano.\",\"en\":\"Bolivia\'s largest and warmest city, the economic engine of the east. It is the base for exploring the Cruce\\u00f1o valleys, the Jesuit missions of Chiquitos and the Bolivian Pantanal.\"}','https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=1600&q=80',-17.7833000,-63.1821000,0,6,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(7,5,'Tarija','tarija','Tarija','{\"es\":\"Capital del vino y del singani bolivianos, con un microclima de valle que recuerda al sur de Espa\\u00f1a. Sus bodegas del valle de la Concepci\\u00f3n y los ca\\u00f1ones de la Angostura son parada obligada.\",\"en\":\"The capital of Bolivian wine and singani, with a valley microclimate that recalls southern Spain. Its wineries in the Concepci\\u00f3n valley and the Angostura canyons are a must.\"}','https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1600&q=80',-21.5355000,-64.7296000,0,7,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(8,5,'Cochabamba','cochabamba','Cochabamba','{\"es\":\"El valle templado que llaman el coraz\\u00f3n de Bolivia. Ciudad de mercados y gastronom\\u00eda, con el Cristo de la Concordia en lo alto, y punto de partida hacia los paisajes de la cordillera Tunari.\",\"en\":\"The temperate valley known as the heart of Bolivia. A city of markets and food, crowned by the Cristo de la Concordia, and a starting point for the Tunari mountain range.\"}','https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1600&q=80',-17.3895000,-66.1568000,1,8,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(9,1,'Madrid','madrid','Madrid','{\"es\":\"La capital re\\u00fane el Prado, el Reina Sof\\u00eda y el Thyssen en un paseo, adem\\u00e1s de la vida de sus barrios y su cocina de taberna. Desde aqu\\u00ed se llega en media hora a Toledo y Segovia.\",\"en\":\"The capital brings the Prado, the Reina Sof\\u00eda and the Thyssen together on one boulevard, along with neighbourhood life and tavern food. Toledo and Segovia are half an hour away.\"}','https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1600&q=80',40.4168000,-3.7038000,1,1,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(10,1,'Barcelona','barcelona','Barcelona','{\"es\":\"Entre el mar y el Tibidabo, la ciudad combina la arquitectura de Gaud\\u00ed, el laberinto g\\u00f3tico y las playas urbanas. Su oferta de hostales y hoteles de dise\\u00f1o es de las mejores de Europa.\",\"en\":\"Between the sea and Tibidabo, the city combines Gaud\\u00ed architecture, the Gothic labyrinth and urban beaches. Its hostels and design hotels are among the best in Europe.\"}','https://images.unsplash.com/photo-1529290130-4ca3753253ae?auto=format&fit=crop&w=1600&q=80',41.3874000,2.1686000,1,2,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(11,1,'Sevilla','sevilla','Sevilla','{\"es\":\"La capital andaluza, con la Giralda, el Alc\\u00e1zar y el barrio de Santa Cruz. En primavera se llena con la Semana Santa y la Feria de Abril, dos de las fiestas m\\u00e1s grandes de Espa\\u00f1a.\",\"en\":\"The Andalusian capital, with the Giralda, the Alc\\u00e1zar and the Santa Cruz quarter. In spring it fills up for Holy Week and the April Fair, two of the biggest festivals in Spain.\"}','https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1600&q=80',37.3891000,-5.9845000,0,3,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(12,1,'Valencia','valencia','Valencia','{\"es\":\"Cuna de la paella, con la Ciudad de las Artes y las Ciencias como icono contempor\\u00e1neo. Su casco hist\\u00f3rico y el mercado central conviven con kil\\u00f3metros de playa a pocos minutos.\",\"en\":\"The birthplace of paella, with the City of Arts and Sciences as its contemporary icon. Its old town and central market sit within minutes of kilometres of beach.\"}','https://images.unsplash.com/photo-1499696010180-025ef6e1a8f9?auto=format&fit=crop&w=1600&q=80',39.4699000,-0.3763000,0,4,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(13,1,'Granada','granada','Granada','{\"es\":\"La Alhambra y el Generalife hacen de Granada una visita obligada, con el Albaic\\u00edn y el Sacromonte como tel\\u00f3n de fondo. La tapa gratuita sigue siendo una instituci\\u00f3n local.\",\"en\":\"The Alhambra and the Generalife make Granada unmissable, with the Albaic\\u00edn and Sacromonte as a backdrop. The free tapa is still a local institution.\"}','https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=80',37.1773000,-3.5986000,0,5,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(14,1,'San Sebastián','san-sebastian','San Sebastián','{\"es\":\"La bah\\u00eda de La Concha y la playa de Zurriola enmarcan la capital gastron\\u00f3mica del Pa\\u00eds Vasco. Sus pintxos y sus restaurantes con estrellas la han hecho famosa en todo el mundo.\",\"en\":\"La Concha bay and Zurriola beach frame the culinary capital of the Basque Country. Its pintxos and starred restaurants have made it famous around the world.\"}','https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=1600&q=80',43.3183000,-1.9812000,0,6,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(15,1,'Santiago de Compostela','santiago-de-compostela','Santiago de Compostela','{\"es\":\"El final del Camino, con la catedral del Ap\\u00f3stol como meta de peregrinos de todo el mundo. Su casco de piedra, declarado Patrimonio de la Humanidad, se recorre bajo la lluvia y a gusto.\",\"en\":\"The end of the Way, with the Apostle\'s cathedral as the goal for pilgrims worldwide. Its stone old town, a World Heritage Site, is best explored in the rain and with pleasure.\"}','https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1600&q=80',42.8782000,-8.5448000,0,7,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(16,1,'Toledo','toledo','Toledo','{\"es\":\"La ciudad de las tres culturas, encaramada sobre el Tajo, conserva sinagogas, mezquitas e iglesias a pocos metros. Su casco medieval es uno de los mejor conservados de Europa.\",\"en\":\"The city of three cultures, perched above the Tagus, keeps synagogues, mosques and churches within metres of one another. Its medieval old town is one of the best preserved in Europe.\"}','https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=1600&q=80',39.8628000,-4.0273000,0,8,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(17,2,'Ciudad de México','ciudad-de-mexico','Ciudad de México','{\"es\":\"La capital del pa\\u00eds re\\u00fane el Z\\u00f3calo, Teotihuac\\u00e1n a las afueras y los murales de Diego Rivera. Sus barrios como Coyoac\\u00e1n y Roma condensan gastronom\\u00eda, arte y noche.\",\"en\":\"The country\'s capital brings together the Z\\u00f3calo, Teotihuac\\u00e1n on its outskirts and Diego Rivera\'s murals. Neighbourhoods such as Coyoac\\u00e1n and Roma pack in food, art and nightlife.\"}','https://images.unsplash.com/photo-1529290130-4ca3753253ae?auto=format&fit=crop&w=1600&q=80',19.4326000,-99.1332000,1,1,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(18,2,'Cancún','cancun','Cancún','{\"es\":\"La puerta del Caribe mexicano: playas de arena blanca, arrecifes frente a la costa y cenotes en el interior. De aqu\\u00ed parte la ruta hacia Isla Mujeres y la Riviera Maya.\",\"en\":\"The gateway to the Mexican Caribbean: white-sand beaches, offshore reefs and cenotes inland. The route to Isla Mujeres and the Riviera Maya starts here.\"}','https://images.unsplash.com/photo-1499696010180-025ef6e1a8f9?auto=format&fit=crop&w=1600&q=80',21.1619000,-86.8515000,1,2,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(19,2,'Tulum','tulum','Tulum','{\"es\":\"Las ruinas mayas frente al mar son el emblema de la Riviera Maya. Hoy combina hoteles de dise\\u00f1o, cenotes y una reserva de biosfera que se explora en bicicleta.\",\"en\":\"The Maya ruins facing the sea are the emblem of the Riviera Maya. Today it combines design hotels, cenotes and a biosphere reserve best explored by bicycle.\"}','https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=1600&q=80',20.2114000,-87.4654000,0,3,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(20,2,'Oaxaca','oaxaca','Oaxaca','{\"es\":\"Capital gastron\\u00f3mica de M\\u00e9xico, con el mole, el mezcal y el mercado Benito Ju\\u00e1rez como protagonistas. Monte Alb\\u00e1n y los talleres de artesanos completan la visita.\",\"en\":\"Mexico\'s culinary capital, with mole, mezcal and Benito Ju\\u00e1rez market as the stars. Monte Alb\\u00e1n and the artisan workshops complete the visit.\"}','https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1600&q=80',17.0732000,-96.7266000,0,4,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(21,2,'Guanajuato','guanajuato','Guanajuato','{\"es\":\"Ciudad minera Patrimonio de la Humanidad, con callejones imposibles y callejones cubiertos por el que se sumerge la ciudad. El Festival Cervantino la llena cada octubre.\",\"en\":\"A mining city and World Heritage Site, with impossible alleys and underground streets the city dives into. The Cervantino Festival fills it every October.\"}','https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=1600&q=80',21.0190000,-101.2574000,0,5,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(22,2,'San Miguel de Allende','san-miguel-de-allende','San Miguel de Allende','{\"es\":\"Su iglesia rosa de estilo neog\\u00f3tico se ve desde todo el valle. Es refugio de artistas y jubilados extranjeros, con galer\\u00edas, terrazas y una escena gastron\\u00f3mica notable.\",\"en\":\"Its pink neo-Gothic church can be seen from across the valley. A refuge for artists and foreign retirees, with galleries, terraces and a notable food scene.\"}','https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1600&q=80',20.9144000,-100.7433000,0,6,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(23,2,'Puerto Vallarta','puerto-vallarta','Puerto Vallarta','{\"es\":\"Entre la Sierra Madre y el Pac\\u00edfico, su malec\\u00f3n y su Zona Rom\\u00e1ntica son famosos por las puestas de sol. Es la base para ver ballenas jorobadas de diciembre a marzo.\",\"en\":\"Between the Sierra Madre and the Pacific, its boardwalk and Romantic Zone are famous for sunsets. It is the base for spotting humpback whales from December to March.\"}','https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=1600&q=80',20.6534000,-105.2253000,0,7,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(24,2,'Valle de Bravo','valle-de-bravo','Valle de Bravo','{\"es\":\"Pueblo M\\u00e1gico de casas encaladas junto al lago y rodeado de pinos. Es el destino de fin de semana de la Ciudad de M\\u00e9xico y capital del parapente en el pa\\u00eds.\",\"en\":\"A Magical Town of whitewashed houses by the lake and ringed by pines. It is Mexico City\'s weekend getaway and the country\'s paragliding capital.\"}','https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=80',19.1931000,-100.1345000,0,8,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(25,3,'Buenos Aires','buenos-aires','Buenos Aires','{\"es\":\"La capital porte\\u00f1a mezcla el tango de San Telmo con la arquitectura de Recoleta y la vida de Palermo. Sus librer\\u00edas, caf\\u00e9s y parrillas justifican una estancia larga.\",\"en\":\"The porte\\u00f1o capital mixes the tango of San Telmo with Recoleta architecture and the life of Palermo. Its bookshops, caf\\u00e9s and steakhouses justify a long stay.\"}','https://images.unsplash.com/photo-1529290130-4ca3753253ae?auto=format&fit=crop&w=1600&q=80',-34.6037000,-58.3816000,1,1,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(26,3,'Bariloche','bariloche','Bariloche','{\"es\":\"A orillas del Nahuel Huapi, es la capital de los lagos patag\\u00f3nicos: bosques, nieve en invierno y chocolates en el centro. El Circuito Chico es la ruta cl\\u00e1sica.\",\"en\":\"On the shore of Nahuel Huapi, it is the capital of the Patagonian lakes: forests, winter snow and chocolate downtown. The Circuito Chico is the classic drive.\"}','https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1600&q=80',-41.1335000,-71.3103000,1,2,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(27,3,'Mendoza','mendoza','Mendoza','{\"es\":\"La tierra del Malbec, con bodegas al pie de los Andes y el Aconcagua como techo de Am\\u00e9rica. Las rutas del vino de Maip\\u00fa y Luj\\u00e1n de Cuyo son su gran atractivo.\",\"en\":\"The land of Malbec, with wineries at the foot of the Andes and Aconcagua, the highest peak in the Americas. The wine routes of Maip\\u00fa and Luj\\u00e1n de Cuyo are its great draw.\"}','https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1600&q=80',-32.8895000,-68.8458000,0,3,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(28,3,'Salta','salta','Salta','{\"es\":\"La linda, como la llaman, conserva su catedral y su cabildo rosados. Es la base para la Quebrada de Humahuaca y los Valles Calchaqu\\u00edes, de paisajes imposibles.\",\"en\":\"Known as \\\"la linda\\\", it keeps its pink cathedral and cabildo. It is the base for the Quebrada de Humahuaca and the Calchaqu\\u00ed valleys, with impossible scenery.\"}','https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=1600&q=80',-24.7821000,-65.4232000,0,4,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(29,3,'Córdoba','cordoba','Córdoba','{\"es\":\"Segunda ciudad del pa\\u00eds y sede de la primera universidad argentina. Sus sierras, la Manzana Jesu\\u00edtica y los valles cercanos la convierten en escapada de fin de semana.\",\"en\":\"The country\'s second city and home to Argentina\'s first university. Its hills, the Jesuit Block and nearby valleys make it a weekend escape.\"}','https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1600&q=80',-31.4201000,-64.1888000,0,5,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(30,3,'Ushuaia','ushuaia','Ushuaia','{\"es\":\"La ciudad del fin del mundo, puerta del canal Beagle y de los viajes a la Ant\\u00e1rtida. El parque nacional Tierra del Fuego y el tren del fin del mundo la rodean.\",\"en\":\"The city at the end of the world, gateway to the Beagle Channel and to Antarctic voyages. Tierra del Fuego National Park and the End of the World Train surround it.\"}','https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=80',-54.8019000,-68.3030000,0,6,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(31,3,'El Calafate','el-calafate','El Calafate','{\"es\":\"La base para visitar el glaciar Perito Moreno, que se desprende en el lago Argentino. El pueblo vive de la estepa y de los turistas que llegan a ver el hielo.\",\"en\":\"The base for visiting the Perito Moreno glacier, which calves into Lake Argentino. The town lives off the steppe and the travellers who come to see the ice.\"}','https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=1600&q=80',-50.3379000,-72.2648000,0,7,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(32,3,'Puerto Iguazú','puerto-iguazu','Puerto Iguazú','{\"es\":\"La puerta argentina a las Cataratas del Iguaz\\u00fa, una de las grandes maravillas naturales del mundo. La Garganta del Diablo se recorre por pasarelas sobre el agua.\",\"en\":\"The Argentine gateway to Iguaz\\u00fa Falls, one of the world\'s great natural wonders. The Devil\'s Throat is explored on walkways over the water.\"}','https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=1600&q=80',-25.5991000,-54.5736000,0,8,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(33,4,'Cartagena de Indias','cartagena-de-indias','Cartagena','{\"es\":\"La ciudad amurallada, Patrimonio de la Humanidad, con balcones de buganvillas y plazas de piedra. Frente a ella, las islas del Rosario y Bar\\u00fa para el d\\u00eda de playa.\",\"en\":\"The walled city, a World Heritage Site, with bougainvillea balconies and stone squares. Opposite it, the Rosario and Bar\\u00fa islands for a beach day.\"}','https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1600&q=80',10.3910000,-75.4794000,1,1,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(34,4,'Bogotá','bogota','Bogotá','{\"es\":\"A 2.640 m de altitud, la capital combina La Candelaria, el Museo del Oro y la escena gastron\\u00f3mica de Zona G y Usaqu\\u00e9n. Monserrate domina la ciudad desde arriba.\",\"en\":\"At 2,640 m of altitude, the capital combines La Candelaria, the Gold Museum and the food scene of Zona G and Usaqu\\u00e9n. Monserrate towers over the city.\"}','https://images.unsplash.com/photo-1529290130-4ca3753253ae?auto=format&fit=crop&w=1600&q=80',4.7110000,-74.0721000,0,2,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(35,4,'Medellín','medellin','Medellín','{\"es\":\"La ciudad de la eterna primavera, transformada por el metrocable y la cultura paisa. El Poblado y Laureles concentran hoteles, caf\\u00e9s y la mejor vida nocturna del pa\\u00eds.\",\"en\":\"The city of the eternal spring, transformed by the metrocable and paisa culture. El Poblado and Laureles hold the hotels, caf\\u00e9s and the country\'s best nightlife.\"}','https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=1600&q=80',6.2442000,-75.5812000,1,3,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(36,4,'Santa Marta','santa-marta','Santa Marta','{\"es\":\"La ciudad m\\u00e1s antigua de Colombia, entre el mar Caribe y la Sierra Nevada. Es la puerta al Parque Tayrona, a Minca y a la ciudad perdida de los Tayrona.\",\"en\":\"Colombia\'s oldest city, between the Caribbean and the Sierra Nevada. It is the gateway to Tayrona Park, Minca and the lost city of the Tayrona.\"}','https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=1600&q=80',11.2404000,-74.1990000,0,4,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(37,4,'Cali','cali','Cali','{\"es\":\"La capital mundial de la salsa, con escuelas de baile y callejones que suenan a timba. Su gastronom\\u00eda vallecaucana y el barrio San Antonio son parada obligada.\",\"en\":\"The world capital of salsa, with dance schools and alleys that ring with timba. Its Vallecaucana cuisine and the San Antonio quarter are a must.\"}','https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=1600&q=80',3.4516000,-76.5320000,0,5,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(38,4,'San Andrés','san-andres','San Andrés','{\"es\":\"La isla del mar de los siete colores, con arrecifes de coral declarados reserva de biosfera. Johnny Cay y el Hoyo Soplador resumen su mezcla de playa y naturaleza.\",\"en\":\"The island of the seven-colour sea, with coral reefs declared a biosphere reserve. Johnny Cay and the Blowing Hole sum up its mix of beach and nature.\"}','https://images.unsplash.com/photo-1499696010180-025ef6e1a8f9?auto=format&fit=crop&w=1600&q=80',12.5847000,-81.7006000,0,6,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(39,4,'Villa de Leyva','villa-de-leyva','Villa de Leyva','{\"es\":\"Pueblo colonial con la plaza empedrada m\\u00e1s grande de Suram\\u00e9rica. Cerca est\\u00e1n el desierto de la Candelaria y el f\\u00f3sil de un kronosaurio de 120 millones de a\\u00f1os.\",\"en\":\"A colonial town with the largest cobbled square in South America. Nearby are the Candelaria desert and the fossil of a 120-million-year-old kronosaurus.\"}','https://images.unsplash.com/photo-1510312305653-8ed496efae75?auto=format&fit=crop&w=1600&q=80',5.6333000,-73.5333000,0,7,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(40,4,'Salento','salento','Salento','{\"es\":\"El coraz\\u00f3n del Eje Cafetero, con casas de colores y el mirador del Alto de la Cruz. Desde aqu\\u00ed se entra al Valle de Cocora y a sus palmas de cera gigantes.\",\"en\":\"The heart of the Coffee Axis, with colourful houses and the Alto de la Cruz viewpoint. From here you enter the Cocora Valley and its giant wax palms.\"}','https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=1600&q=80',4.6375000,-75.5708000,0,8,1,'2026-10-13 19:33:52','2026-10-13 19:33:52');
/*!40000 ALTER TABLE `destinos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `empresas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) NOT NULL,
  `nombre_empresa` varchar(120) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `codigo_pais` varchar(6) DEFAULT NULL,
  `pais_id` bigint(20) unsigned DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `whatsapp` varchar(20) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `empresas_correo_unique` (`correo`),
  KEY `empresas_pais_id_foreign` (`pais_id`),
  CONSTRAINT `empresas_pais_id_foreign` FOREIGN KEY (`pais_id`) REFERENCES `paises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
INSERT INTO `empresas` VALUES (1,'Bolivia Tours',NULL,'tours-BO@hospedaya.test','BO',5,1,NULL,NULL,'2026-10-20 20:05:48','2026-10-20 20:05:48'),(2,'España Tours',NULL,'tours-ES@hospedaya.test','ES',1,1,NULL,NULL,'2026-10-20 20:05:48','2026-10-20 20:05:48'),(3,'México Tours',NULL,'tours-MX@hospedaya.test','MX',2,1,NULL,NULL,'2026-10-20 20:05:48','2026-10-20 20:05:48'),(4,'Argentina Tours',NULL,'tours-AR@hospedaya.test','AR',3,1,NULL,NULL,'2026-10-20 20:05:48','2026-10-20 20:05:48'),(5,'Colombia Tours',NULL,'tours-CO@hospedaya.test','CO',4,1,NULL,NULL,'2026-10-20 20:05:48','2026-10-20 20:05:48'),(6,'Ana Rios',NULL,'ana@gmail.com','BO',NULL,1,'75144856','deseo registrar una empresa de automóviles por favor para rpublicitar','2026-10-21 17:12:58','2026-10-21 17:12:58'),(7,'Arturo Velasquez','LCD. SRL','robert@gmail.com','BO',5,1,'71451541','Deseo publicar los paquetes de mi empresa','2026-10-22 00:02:13','2026-10-22 00:02:13');
/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imagenes`
--

DROP TABLE IF EXISTS `imagenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imagenes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `recurso_tipo` varchar(40) NOT NULL,
  `recurso_id` bigint(20) unsigned NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `orden` int(10) unsigned NOT NULL DEFAULT 0,
  `texto_alt` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imagenes_recurso_orden` (`recurso_tipo`,`recurso_id`,`orden`)
) ENGINE=InnoDB AUTO_INCREMENT=496 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imagenes`
--

LOCK TABLES `imagenes` WRITE;
/*!40000 ALTER TABLE `imagenes` DISABLE KEYS */;
INSERT INTO `imagenes` VALUES (397,'alojamiento',1,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(398,'alojamiento',1,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(399,'alojamiento',1,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(400,'alojamiento',1,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(401,'alojamiento',1,'05.jpg',4,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(402,'alojamiento',1,'06.jpg',5,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(403,'alojamiento',2,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(404,'alojamiento',2,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(405,'alojamiento',2,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(406,'alojamiento',2,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(407,'alojamiento',3,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(408,'alojamiento',3,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(409,'alojamiento',3,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(410,'alojamiento',3,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(411,'alojamiento',4,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(412,'alojamiento',4,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(413,'alojamiento',4,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(414,'alojamiento',4,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(415,'alojamiento',5,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(416,'alojamiento',5,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(417,'alojamiento',5,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(418,'alojamiento',5,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(419,'alojamiento',6,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(420,'alojamiento',6,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(421,'alojamiento',6,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(422,'alojamiento',6,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(423,'alojamiento',7,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(424,'alojamiento',7,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(425,'alojamiento',7,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(426,'alojamiento',7,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(427,'alojamiento',7,'05.jpg',4,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(428,'alojamiento',7,'06.jpg',5,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(429,'alojamiento',7,'07.jpg',6,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(430,'alojamiento',7,'08.jpg',7,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(431,'alojamiento',7,'09.jpg',8,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(432,'alojamiento',7,'10.jpg',9,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(433,'alojamiento',7,'11.jpg',10,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(434,'alojamiento',7,'12.jpg',11,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(435,'alojamiento',8,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(436,'alojamiento',8,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(437,'alojamiento',8,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(438,'alojamiento',8,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(439,'alojamiento',8,'05.jpg',4,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(440,'alojamiento',8,'06.jpg',5,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(441,'alojamiento',8,'07.jpg',6,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(442,'alojamiento',8,'08.jpg',7,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(443,'alojamiento',8,'09.jpg',8,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(444,'alojamiento',8,'10.jpg',9,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(445,'alojamiento',8,'11.jpg',10,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(446,'alojamiento',8,'12.jpg',11,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(447,'alojamiento',9,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(448,'alojamiento',9,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(449,'alojamiento',9,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(450,'alojamiento',9,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(451,'alojamiento',9,'05.jpg',4,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(452,'alojamiento',9,'06.jpg',5,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(453,'alojamiento',9,'07.jpg',6,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(454,'alojamiento',9,'08.jpg',7,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(455,'alojamiento',9,'09.jpg',8,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(456,'alojamiento',9,'10.jpg',9,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(457,'alojamiento',9,'11.jpg',10,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(458,'alojamiento',9,'12.jpg',11,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(459,'alojamiento',10,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(460,'alojamiento',10,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(461,'alojamiento',10,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(462,'alojamiento',10,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(463,'alojamiento',10,'05.jpg',4,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(464,'alojamiento',10,'06.jpg',5,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(465,'alojamiento',10,'07.jpg',6,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(466,'alojamiento',10,'08.jpg',7,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(467,'alojamiento',10,'09.jpg',8,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(468,'alojamiento',10,'10.jpg',9,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(469,'alojamiento',10,'11.jpg',10,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(470,'alojamiento',10,'12.jpg',11,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(471,'alojamiento',11,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(472,'alojamiento',11,'02.jpg',1,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(473,'alojamiento',11,'03.jpg',2,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(474,'alojamiento',11,'04.jpg',3,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(475,'alojamiento',11,'05.jpg',4,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(476,'alojamiento',11,'06.jpg',5,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(477,'alojamiento',11,'07.jpg',6,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(478,'alojamiento',11,'08.jpg',7,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(479,'alojamiento',11,'09.jpg',8,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(480,'alojamiento',11,'10.jpg',9,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(481,'alojamiento',11,'11.jpg',10,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(482,'alojamiento',11,'12.jpg',11,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(483,'paquete',1,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(484,'paquete',2,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(485,'paquete',3,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(486,'paquete',4,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(487,'paquete',5,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(488,'paquete',6,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(489,'paquete',7,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(490,'paquete',8,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(491,'paquete',9,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(492,'paquete',10,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(493,'paquete',11,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(494,'paquete',12,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58'),(495,'paquete',13,'01.jpg',0,NULL,'2026-10-22 23:00:58','2026-10-22 23:00:58');
/*!40000 ALTER TABLE `imagenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_09_29_201625_create_personal_access_tokens_table',1),(5,'2026_09_29_201714_create_accommodations_and_reservations_tables',1),(6,'2026_09_30_090000_translate_database_schema_to_spanish',1),(7,'2026_09_30_120000_create_paises_table_and_link_accommodations',1),(8,'2026_10_01_090000_add_english_country_presentation',1),(9,'2026_10_02_090000_add_bolivia_country',1),(10,'2026_10_03_090000_create_empresas_table',1),(11,'2026_10_04_090000_add_whatsapp_to_empresas_table',1),(12,'2026_10_05_090000_normalize_empresa_country_to_iso2',1),(13,'2026_10_05_100000_add_moneda_iso_to_paises_and_reservas',1),(14,'2026_10_06_090000_add_codigo_pais_to_reservas_table',1),(15,'2026_10_06_100000_add_empresa_id_to_alojamientos_table',1),(16,'2026_10_07_090000_create_destinos_table',1),(17,'2026_10_13_122142_create_travel_packages_table',1),(18,'2026_10_14_090000_add_travel_package_id_to_reservas_table',2),(19,'2026_10_20_090000_create_imagenes_table',3),(20,'2026_10_21_090000_empresas_con_pais_y_paquetes_con_empresa',4),(21,'2026_10_21_100000_simplificar_tabla_imagenes',5),(22,'2026_10_21_110000_drop_imagenes_from_unidades_habitacion',6),(23,'2026_10_21_120000_add_nombre_empresa_to_empresas_table',7),(24,'2026_10_21_130000_drop_imagenes_from_alojamientos_table',8),(25,'2026_10_21_140000_cambiar_estado_alojamientos_a_entero',9),(26,'2026_10_21_150000_add_estado_to_unidades_habitacion_table',10),(28,'2026_10_21_160000_add_estado_to_empresas_table',11),(29,'2026_10_21_170000_reemplazar_estado_reserva_por_estado_en_reservas',12),(30,'2026_10_21_180000_renombrar_activo_a_estado_en_paises',13),(31,'2026_10_21_190000_renombrar_activo_a_estado_en_destinos',14),(32,'2026_10_21_200000_renombrar_activo_a_estado_en_travel_packages',14),(33,'2026_10_22_100000_renombrar_travel_packages_a_paquetes_turisticos',14),(34,'2026_10_22_110000_drop_imagenes_from_paquetes_turisticos_table',15),(35,'2026_10_22_120000_drop_imagen_from_paquetes_turisticos_table',16);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paises`
--

DROP TABLE IF EXISTS `paises`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paises` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `iso2` varchar(2) NOT NULL,
  `moneda_iso` char(3) NOT NULL,
  `idioma_principal` varchar(10) NOT NULL DEFAULT 'es',
  `texto_presentacion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`texto_presentacion`)),
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paises_slug_unique` (`slug`),
  UNIQUE KEY `paises_iso2_unique` (`iso2`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paises`
--

LOCK TABLES `paises` WRITE;
/*!40000 ALTER TABLE `paises` DISABLE KEYS */;
INSERT INTO `paises` VALUES (1,'España','espana','ES','EUR','es','{\"es\":\"España reúne una oferta de hospedaje muy variada: hoteles históricos en Madrid y Barcelona, hostales en barrios con mucha vida, cabañas rurales en la Sierra y apartamentos turísticos en la costa. Elige la opción que encaje con tu viaje y reserva con confirmación inmediata.\",\"en\":\"Spain offers a wide range of places to stay: historic hotels in Madrid and Barcelona, hostels in lively neighbourhoods, rural cabins in the Sierra and holiday apartments on the coast. Choose the option that fits your trip and book with instant confirmation.\"}',1,'2026-10-13 19:33:51','2026-10-13 19:33:51'),(2,'México','mexico','MX','MXN','es','{\"es\":\"México combina resorts de playa en el Caribe y el Pacífico con escapadas de montaña y pueblos con carácter. Desde Cancún hasta Valle de Bravo, encontrarás alojamientos pensados para cada tipo de viaje.\",\"en\":\"Mexico combines Caribbean and Pacific beach resorts with mountain escapes and characterful towns. From Cancun to Valle de Bravo, you will find stays designed for every kind of trip.\"}',1,'2026-10-13 19:33:51','2026-10-13 19:33:51'),(3,'Argentina','argentina','AR','ARS','es','{\"es\":\"Argentina es el destino ideal para quien busca naturaleza y aventura: cabañas a orillas de los lagos de la Patagonia, refugios de montaña y apartamentos en el centro de Buenos Aires.\",\"en\":\"Argentina runs from the glaciers of Patagonia to the city that never sleeps. Find a boutique hotel in Buenos Aires, a mountain lodge in Bariloche or a cabin in the woods for your next trip.\"}',1,'2026-10-13 19:33:51','2026-10-13 19:33:51'),(4,'Colombia','colombia','CO','COP','es','{\"es\":\"Colombia es tan diversa como su geografía: desde la costa Caribe hasta los valles del café y la zona andina. Medellín es una buena base para descubrir el país.\",\"en\":\"Colombia rewards travellers who look beyond the obvious. Discover boutique hotels in Bogota, colonial houses in Medellin and coastal stays in Cartagena and the Caribbean.\"}',1,'2026-10-13 19:33:51','2026-10-13 19:33:51'),(5,'Bolivia','bolivia','BO','BOB','es','{\"es\":\"Bolivia es una encrucijada de paisajes y culturas: la selva amazónica al norte, el altiplano y el Salar de Uyuni al sur, y valles templados como el de Cochabamba en el centro. En Cochabamba conviven los hoteles de negocios con las casas de campo del valle, todo a pocos kilómetros.\",\"en\":\"Bolivia is a crossroads of landscapes and cultures: the Amazon jungle in the north, the high plains and Salar de Uyuni in the south, and temperate valleys like Cochabamba in the centre. In Cochabamba, business hotels sit alongside countryside retreats in the valley, all within a few kilometres.\"}',1,'2026-10-13 19:33:51','2026-10-13 19:33:51');
/*!40000 ALTER TABLE `paises` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paquetes_turisticos`
--

DROP TABLE IF EXISTS `paquetes_turisticos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paquetes_turisticos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint(20) unsigned NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `ciudad` varchar(255) DEFAULT NULL,
  `texto_presentacion` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`texto_presentacion`)),
  `descripcion` text DEFAULT NULL,
  `incluye` text DEFAULT NULL,
  `no_incluye` text DEFAULT NULL,
  `itinerario` text DEFAULT NULL,
  `duracion` varchar(255) DEFAULT NULL,
  `min_personas` int(11) NOT NULL DEFAULT 1,
  `max_personas` int(11) NOT NULL DEFAULT 20,
  `precio_desde` decimal(10,2) NOT NULL DEFAULT 0.00,
  `moneda_iso` varchar(3) DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `orden` int(11) NOT NULL DEFAULT 0,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `travel_packages_slug_unique` (`slug`),
  KEY `travel_packages_empresa_id_foreign` (`empresa_id`),
  CONSTRAINT `travel_packages_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paquetes_turisticos`
--

LOCK TABLES `paquetes_turisticos` WRITE;
/*!40000 ALTER TABLE `paquetes_turisticos` DISABLE KEYS */;
INSERT INTO `paquetes_turisticos` VALUES (1,1,'Tour Salar de Uyuni 1 Dia','tour-salar-de-uyuni-1-dia','Uyuni','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'BOB',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(2,1,'Tour La Paz 1 Dia','tour-la-paz-1-dia','La Paz','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'BOB',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(3,1,'Tour Cochabamba 1 Dia','tour-cochabamba-1-dia','Cochabamba','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'BOB',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(4,2,'Tour Madrid 1 Dia','tour-madrid-1-dia','Madrid','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'EUR',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(5,2,'Tour Barcelona 1 Dia','tour-barcelona-1-dia','Barcelona','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'EUR',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(6,3,'Tour Ciudad de México 1 Dia','tour-ciudad-de-mexico-1-dia','Ciudad de México','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'MXN',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(7,3,'Tour Cancún 1 Dia','tour-cancun-1-dia','Cancún','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'MXN',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(8,4,'Tour Buenos Aires 1 Dia','tour-buenos-aires-1-dia','Buenos Aires','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'ARS',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(9,4,'Tour Bariloche 1 Dia','tour-bariloche-1-dia','Bariloche','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'ARS',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(10,5,'Tour Cartagena de Indias 1 Dia','tour-cartagena-de-indias-1-dia','Cartagena','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'COP',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(11,5,'Tour Medellín 1 Dia','tour-medellin-1-dia','Medellín','{\"es\":\"Experiencia completa.\",\"en\":\"Full experience.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Comidas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,500.00,'COP',NULL,NULL,1,0,1,'2026-10-13 19:33:52','2026-10-13 19:33:52'),(12,1,'Tour Salar de Uyuni 2 Dias','tour-salar-de-uyuni-2-dias','Uyuni','{\"es\":\"Aventura de 2 dias en el salar.\",\"en\":\"2-day adventure.\"}',NULL,'[\"Guia\",\"Transporte\",\"Hospedaje\"]','[\"Extras\"]','[{\"hora\":\"10:00\",\"actividad\":\"Salida\"},{\"hora\":\"16:00\",\"actividad\":\"Llegada\"}]','2 dias',1,10,650.00,'BOB',NULL,NULL,1,0,1,'2026-10-13 19:33:53','2026-10-13 19:33:53'),(13,1,'City Tour La Paz','tour-la-paz-city-tour','La Paz','{\"es\":\"Recorrido por La Paz.\",\"en\":\"La Paz tour.\"}',NULL,'[\"Guia\",\"Transporte\"]','[\"Entradas\"]','[{\"hora\":\"09:00\",\"actividad\":\"Inicio\"}]','1 dia',1,12,250.00,'BOB',NULL,NULL,1,0,1,'2026-10-13 19:33:53','2026-10-13 19:33:53');
/*!40000 ALTER TABLE `paquetes_turisticos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservas`
--

DROP TABLE IF EXISTS `reservas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_reserva` varchar(20) NOT NULL,
  `unidad_habitacion_id` bigint(20) unsigned DEFAULT NULL,
  `alojamiento_id` bigint(20) unsigned DEFAULT NULL,
  `nombre_huesped` varchar(150) NOT NULL,
  `correo_huesped` varchar(150) NOT NULL,
  `telefono_huesped` varchar(50) NOT NULL,
  `codigo_pais` varchar(6) DEFAULT NULL,
  `documento_huesped` varchar(50) DEFAULT NULL,
  `fecha_entrada` date DEFAULT NULL,
  `fecha_salida` date DEFAULT NULL,
  `adultos` smallint(5) unsigned NOT NULL DEFAULT 1,
  `ninos` smallint(5) unsigned NOT NULL DEFAULT 0,
  `noches` smallint(5) unsigned NOT NULL DEFAULT 1,
  `precio_noche` decimal(10,2) NOT NULL,
  `tarifa_limpieza` decimal(10,2) NOT NULL DEFAULT 0.00,
  `impuestos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_total` decimal(10,2) NOT NULL,
  `moneda_iso` char(3) DEFAULT NULL,
  `metodo_pago` enum('pay_at_property','credit_card','transfer') NOT NULL DEFAULT 'pay_at_property',
  `estado_pago` enum('pending','paid','refunded') NOT NULL DEFAULT 'pending',
  `estado` smallint(6) NOT NULL DEFAULT 1,
  `solicitudes_especiales` text DEFAULT NULL,
  `motivo_cancelacion` varchar(255) DEFAULT NULL,
  `cancelada_en` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `paquete_turistico_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservas_codigo_reserva_unique` (`codigo_reserva`),
  KEY `reservas_correo_huesped_index` (`correo_huesped`),
  KEY `reservas_fecha_entrada_index` (`fecha_entrada`),
  KEY `reservas_fecha_salida_index` (`fecha_salida`),
  KEY `reservas_alojamiento_id_foreign` (`alojamiento_id`),
  KEY `idx_disponibilidad_reserva` (`unidad_habitacion_id`,`estado`,`fecha_entrada`,`fecha_salida`),
  CONSTRAINT `reservas_alojamiento_id_foreign` FOREIGN KEY (`alojamiento_id`) REFERENCES `alojamientos` (`id`),
  CONSTRAINT `reservas_unidad_habitacion_id_foreign` FOREIGN KEY (`unidad_habitacion_id`) REFERENCES `unidades_habitacion` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservas`
--

LOCK TABLES `reservas` WRITE;
/*!40000 ALTER TABLE `reservas` DISABLE KEYS */;
INSERT INTO `reservas` VALUES (2,'BK-8B5FLYD',NULL,NULL,'jorge mendez','jorge@gmail.com','71458415','+591','464646',NULL,NULL,1,0,0,250.00,0.00,0.00,250.00,'BOB','pay_at_property','pending',2,NULL,NULL,NULL,'2026-10-14 23:41:16','2026-10-14 23:41:16',13),(3,'BK-GU5GBM8',19,11,'mario lopez','mario@gmail.com','71454848','+591',NULL,'2026-10-14','2026-10-16',1,0,2,68.00,0.00,13.60,149.60,'BOB','pay_at_property','pending',2,'mañana',NULL,NULL,'2026-10-14 23:43:23','2026-10-14 23:43:23',NULL),(4,'BK-MH2LXTZ',NULL,NULL,'Rolando Rojas Pinto','rolando@hotmail.com','71456524','+591','45715448',NULL,NULL,1,0,0,250.00,0.00,0.00,250.00,'BOB','pay_at_property','pending',2,NULL,NULL,NULL,'2026-10-21 17:08:11','2026-10-21 17:08:11',13);
/*!40000 ALTER TABLE `reservas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tipos_alojamiento`
--

DROP TABLE IF EXISTS `tipos_alojamiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tipos_alojamiento` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icono` varchar(50) NOT NULL DEFAULT 'building',
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tipos_alojamiento_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tipos_alojamiento`
--

LOCK TABLES `tipos_alojamiento` WRITE;
/*!40000 ALTER TABLE `tipos_alojamiento` DISABLE KEYS */;
INSERT INTO `tipos_alojamiento` VALUES (1,'Hoteles','hotel','Building2','Habitaciones privadas con servicios completos, recepción 24/7 y comodidades exclusivas.','2026-10-13 19:33:52','2026-10-13 19:33:52'),(2,'Hostales','hostal','BedSingle','Alojamiento social y accesible con dormitorios compartidos y habitaciones privadas.','2026-10-13 19:33:52','2026-10-13 19:33:52'),(3,'Cabañas','cabana','Home','Casas de campo y cabañas rústicas en contacto con la naturaleza y total privacidad.','2026-10-13 19:33:52','2026-10-13 19:33:52'),(4,'Apartamentos','apartamento','Building','Espacios completos equipados con cocina, ideales para estancias prolongadas o viajes en familia.','2026-10-13 19:33:52','2026-10-13 19:33:52'),(5,'Glamping','glamping','Tent','Experiencia de acampar al aire libre con el lujo, confort y diseño de un hotel de alta gama.','2026-10-13 19:33:52','2026-10-13 19:33:52');
/*!40000 ALTER TABLE `tipos_alojamiento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidades_habitacion`
--

DROP TABLE IF EXISTS `unidades_habitacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unidades_habitacion` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `alojamiento_id` bigint(20) unsigned NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `tipo_unidad` enum('entire_place','private_room','shared_room') NOT NULL DEFAULT 'private_room',
  `capacidad_adultos` smallint(5) unsigned NOT NULL DEFAULT 2,
  `capacidad_ninos` smallint(5) unsigned NOT NULL DEFAULT 0,
  `total_unidades` smallint(5) unsigned NOT NULL DEFAULT 1,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `precio_base_noche` decimal(10,2) NOT NULL,
  `tarifa_limpieza` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tipo_bano` enum('private','shared') NOT NULL DEFAULT 'private',
  `distribucion_camas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`distribucion_camas`)),
  `amenidades` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`amenidades`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_unidades_alojamiento_precio` (`alojamiento_id`,`precio_base_noche`),
  CONSTRAINT `unidades_habitacion_alojamiento_id_foreign` FOREIGN KEY (`alojamiento_id`) REFERENCES `alojamientos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidades_habitacion`
--

LOCK TABLES `unidades_habitacion` WRITE;
/*!40000 ALTER TABLE `unidades_habitacion` DISABLE KEYS */;
INSERT INTO `unidades_habitacion` VALUES (1,1,'Suite Deluxe King con Balcón y Vista','private_room',2,1,4,1,185.00,0.00,'private','[\"1 Cama King Size\"]','[\"Ba\\u00f1o privado de m\\u00e1rmol\",\"Ba\\u00f1era de hidromasaje\",\"Smart TV 55\\\"\",\"Minibar de cortes\\u00eda\",\"Cafetera Nespresso\",\"Balc\\u00f3n privado\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(2,1,'Habitación Doble Superior Clásica','private_room',2,0,8,1,120.00,0.00,'private','[\"1 Cama Queen Size\"]','[\"Ba\\u00f1o privado con ducha de lluvia\",\"Aire acondicionado silencioso\",\"WiFi de alta velocidad\",\"Caja fuerte\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(3,2,'Ocean Front Suite con Terraza Privada','private_room',2,2,5,1,240.00,0.00,'private','[\"1 Cama King\",\"1 Sof\\u00e1 Cama Doble\"]','[\"Vista directa al mar\",\"Acceso directo a la playa\",\"Jacuzzi en terraza\",\"Servicio a la habitaci\\u00f3n 24h\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(4,3,'Cama en Dormitorio Mixto (6 literas tipo cápsula)','shared_room',1,0,18,1,26.00,0.00,'shared','[\"1 Cama en Litera con cortina de privacidad\"]','[\"Ba\\u00f1o compartido impecable\",\"Enchufe y luz de lectura individual\",\"Locker espacioso con cerradura electr\\u00f3nica\",\"Ropa de cama incluida\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(5,3,'Cama en Dormitorio Femenino (4 camas con baño interno)','shared_room',1,0,8,1,31.00,0.00,'private','[\"1 Cama en Litera de Madera\"]','[\"Ba\\u00f1o privado exclusivo para el dormitorio femenino\",\"Espejo de cuerpo entero\",\"Secador de pelo\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(6,3,'Habitación Doble Privada (Económica)','private_room',2,0,3,1,65.00,0.00,'shared','[\"1 Cama Doble\"]','[\"Privacidad total\",\"Ba\\u00f1o compartido a pocos pasos\",\"Ventana exterior\",\"Toallas incluidas\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(7,4,'Cabaña Familiar Panorámica Completa (Hasta 6 personas)','entire_place',5,2,3,1,135.00,25.00,'private','[\"1 Cama King\",\"2 Camas Individuales\",\"1 Cama Marinera\"]','[\"Lugar entero\",\"Chimenea a le\\u00f1a tradicional\",\"Cocina r\\u00fastica completa con horno\",\"Parrilla exterior privada\",\"Deck de madera vista al lago\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(8,4,'Refugio Alpino Romántico para Parejas con Hot Tub','entire_place',2,0,2,1,98.00,15.00,'private','[\"1 Cama Queen\"]','[\"Lugar entero\",\"Tina de hidromasaje exterior de cipr\\u00e9s\",\"Cocina equipada\",\"Calefacci\\u00f3n central\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(9,5,'Apartamento Entero de 2 Habitaciones + Balcón','entire_place',4,1,2,1,78.00,20.00,'private','[\"1 Cama Queen\",\"1 Cama Doble\"]','[\"Lugar entero (78 m\\u00b2)\",\"Cocina de inducci\\u00f3n con lavavajillas\",\"Lavadora y secadora en unidad\",\"Internet 300 Mbps sim\\u00e9trico\",\"Smart TV 65\\\" con Netflix\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(10,6,'Domo Geodésico Celestial con Baño Privado y Jacuzzi','entire_place',2,0,4,1,145.00,18.00,'private','[\"1 Cama King Size\"]','[\"C\\u00fapula transparente panor\\u00e1mica\",\"Telescopio profesional\",\"Jacuzzi privado exterior climatizado\",\"Fogatero con bombones y le\\u00f1a\",\"Ba\\u00f1o ecol\\u00f3gico privado\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(11,7,'Suite Business con Escritorio y Cama King','private_room',2,0,6,1,95.00,0.00,'private','[\"1 Cama King Size\"]','[\"Escritorio ejecutivo\",\"Silla ergon\\u00f3mica\",\"WiFi de alta velocidad\",\"Smart TV 50\\\"\",\"Caja fuerte para port\\u00e1til\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(12,7,'Suite Familiar de Dos Ambientes','private_room',3,2,3,1,140.00,12.00,'private','[\"1 Cama King Size\",\"1 Sof\\u00e1 Cama Doble\"]','[\"Dos ambientes separados\",\"Cocina equipada\",\"Minibar\",\"Balc\\u00f3n a la calle\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(13,8,'Habitación Deluxe con Acceso al Jardín','private_room',2,0,5,1,88.00,0.00,'private','[\"1 Cama Queen Size\"]','[\"Acceso directo al jard\\u00edn\",\"Ba\\u00f1era profunda\",\"Ropa de cama de algod\\u00f3n\",\"Almohadas de plumas\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(14,8,'Suite con Terraza Privada y Vista al Jardín','private_room',2,1,2,1,125.00,0.00,'private','[\"1 Cama King Size\"]','[\"Terraza amueblada\",\"Hamaca de descanso\",\"Desayuno servido en la habitaci\\u00f3n\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(15,9,'Habitación Confort con Vista a la Piscina','private_room',2,1,8,1,92.00,0.00,'private','[\"1 Cama King Size\"]','[\"Acceso a la piscina cubierta\",\"Escritorio ejecutivo\",\"Cafetera\",\"Caja fuerte\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(16,9,'Suite Ejecutiva para Hospedar Viajantes de Negocio','private_room',2,0,4,1,150.00,15.00,'private','[\"1 Cama King Size\"]','[\"Sala de reuniones para 4 personas\",\"Impresora en habitaci\\u00f3n\",\"Acceso al spa\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(17,10,'Habitación Clásica con Vista al Jardín Interior','private_room',2,0,10,1,110.00,0.00,'private','[\"1 Cama Queen Size\"]','[\"Ba\\u00f1o de m\\u00e1rmol\",\"Albornoz y zapatillas\",\"Minibar\",\"Smart TV\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(18,10,'Suite Real con Salón y Bañera Externa','private_room',2,2,3,1,185.00,20.00,'private','[\"1 Cama King Size\",\"1 Sof\\u00e1 Cama\"]','[\"Sal\\u00f3n independiente\",\"Ba\\u00f1era profunda\",\"Vestidor privado\",\"Acceso directo al spa\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(19,11,'Habitación Estándar con Vista al Jardín','private_room',2,1,8,1,68.00,0.00,'private','[\"1 Cama Queen Size\"]','[\"Balc\\u00f3n amueblado\",\"Ducha de lluvia\",\"Escritorio de trabajo\",\"Cafetera de c\\u00e1psulas\"]','2026-10-13 19:33:52','2026-10-13 19:33:52'),(20,11,'Suite Familiar con Acceso a la Piscina','private_room',2,2,4,1,105.00,10.00,'private','[\"1 Cama King Size\",\"2 Camas Individuales\"]','[\"Acceso directo a la piscina\",\"Cocina compacta\",\"Lavadora y secadora\",\"Vajilla completa\"]','2026-10-13 19:33:52','2026-10-13 19:33:52');
/*!40000 ALTER TABLE `unidades_habitacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'alojamientos_db'
--

--
-- Dumping routines for database 'alojamientos_db'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-22 17:12:53
