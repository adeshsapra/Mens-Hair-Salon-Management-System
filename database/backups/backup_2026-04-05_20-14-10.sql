-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: classycut
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
-- Current Database: `classycut`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `classycut` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `classycut`;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_email` varchar(255) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin`
--

LOCK TABLES `admin` WRITE;
/*!40000 ALTER TABLE `admin` DISABLE KEYS */;
INSERT INTO `admin` VALUES (10,'akshay007@gmail.com','1234','akshay');
/*!40000 ALTER TABLE `admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointment_history`
--

DROP TABLE IF EXISTS `appointment_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointment_history` (
  `ah_id` int(11) NOT NULL AUTO_INCREMENT,
  `a_id` int(11) DEFAULT NULL,
  `ah_name` varchar(50) NOT NULL,
  `ah_email` varchar(50) NOT NULL,
  `ah_no` int(10) NOT NULL,
  `ah_date` date NOT NULL,
  `ah_time` time NOT NULL,
  `ah_category` varchar(50) NOT NULL,
  `ah_type` varchar(50) NOT NULL,
  `ah_status` varchar(100) NOT NULL,
  `id` int(11) NOT NULL,
  PRIMARY KEY (`ah_id`),
  KEY `a_id` (`a_id`),
  KEY `id` (`id`),
  CONSTRAINT `appointment_history_ibfk_1` FOREIGN KEY (`a_id`) REFERENCES `appointments` (`a_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `appointment_history_ibfk_2` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_history`
--

LOCK TABLES `appointment_history` WRITE;
/*!40000 ALTER TABLE `appointment_history` DISABLE KEYS */;
INSERT INTO `appointment_history` VALUES (2,2,'akki07','abhi@gmail.com',1234567890,'2022-11-11','02:22:00','beard','fadebeard','Accepted',6),(4,4,'adesh','akshay@gmail.com',1234567890,'2024-11-11','02:22:00','spa','mud_wrap','Pending',5),(5,5,'akki07','abhi@gmail.com',1234567890,'2024-11-11','11:11:00','beard','long_beard','Pending',6),(8,8,'prince','prince@gmail.com',2147483647,'2024-12-10','12:00:00','spa','fullbody','Accepted',13),(9,9,'akshay','akshay@gmail.com',1234567890,'2025-06-12','11:08:00','hair','Hair Color','Pending',16);
/*!40000 ALTER TABLE `appointment_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `a_id` int(11) NOT NULL AUTO_INCREMENT,
  `a_name` varchar(50) NOT NULL,
  `a_email` varchar(50) NOT NULL,
  `a_no` int(10) NOT NULL,
  `a_date` date NOT NULL,
  `a_time` time NOT NULL,
  `a_category` varchar(50) NOT NULL,
  `a_type` varchar(50) NOT NULL,
  `a_status` varchar(100) NOT NULL,
  `id` int(11) NOT NULL,
  PRIMARY KEY (`a_id`),
  KEY `id` (`id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (2,'akki07','abhi@gmail.com',1234567890,'2022-11-11','02:22:00','beard','fadebeard','Accepted',6),(4,'adesh','akshay@gmail.com',1234567890,'2024-11-11','02:22:00','spa','mud_wrap','Pending',5),(5,'akki07','abhi@gmail.com',1234567890,'2024-11-11','11:11:00','beard','long_beard','Pending',6),(8,'prince','prince@gmail.com',2147483647,'2024-12-10','12:00:00','spa','fullbody','Accepted',13),(9,'akshay','akshay@gmail.com',1234567890,'2025-06-12','11:08:00','hair','Hair Color','Pending',16);
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beard_service`
--

DROP TABLE IF EXISTS `beard_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `beard_service` (
  `beard_id` int(100) NOT NULL AUTO_INCREMENT,
  `beard_service` varchar(255) NOT NULL,
  `beard_price` int(100) NOT NULL,
  PRIMARY KEY (`beard_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beard_service`
--

LOCK TABLES `beard_service` WRITE;
/*!40000 ALTER TABLE `beard_service` DISABLE KEYS */;
INSERT INTO `beard_service` VALUES (7,'Short Beard',400),(8,'Trimmed Beard',400),(9,'Fade Beard',100),(10,'Anchor Beard',400),(11,'Thick Bushy Beard',600),(12,'Gotee Beard',350);
/*!40000 ALTER TABLE `beard_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classic_membership`
--

DROP TABLE IF EXISTS `classic_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classic_membership` (
  `classic_id` int(100) NOT NULL AUTO_INCREMENT,
  `classic_plan` varchar(100) NOT NULL,
  `classic_desc` varchar(255) NOT NULL,
  `classic_price` int(100) NOT NULL,
  PRIMARY KEY (`classic_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classic_membership`
--

LOCK TABLES `classic_membership` WRITE;
/*!40000 ALTER TABLE `classic_membership` DISABLE KEYS */;
INSERT INTO `classic_membership` VALUES (1,'yearly','30% off On Spa services',0),(2,'yearly','Unlimited Beards & Skin Services',0),(3,'yearly','1 complimentary Hair Style per month',0),(4,'yearly','1 complimentary Child HairCut Per Month',0),(5,'yearly','Priority booking Preferred Stylists',0),(19,'yearly','Free Product Samples',7999),(20,'monthly','30% off On Spa services',0),(21,'monthly','1 complimentary Hair Style per month',0),(22,'monthly','1 complimentary Child HairCut Per Month',0),(23,'monthly','Priority booking Preferred Stylists',0),(24,'monthly','Free Product Samples',699);
/*!40000 ALTER TABLE `classic_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_details`
--

DROP TABLE IF EXISTS `contact_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_details` (
  `c_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) DEFAULT NULL,
  `c_name` varchar(255) DEFAULT NULL,
  `c_email` varchar(255) DEFAULT NULL,
  `c_phone` varchar(10) DEFAULT NULL,
  `c_message` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`c_id`),
  KEY `id` (`id`),
  CONSTRAINT `contact_details_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_details`
--

LOCK TABLES `contact_details` WRITE;
/*!40000 ALTER TABLE `contact_details` DISABLE KEYS */;
INSERT INTO `contact_details` VALUES (1,NULL,'adesh','classycut007@gmail.com','7575852866','hiii, my name is akshay. your service is mind-blowing..!!'),(2,NULL,'adesh','classycut007@gmail.com','7575852866','hiii, my name is akshay. your service is mind-blowing..!!'),(3,NULL,'adesh','classycut007@gmail.com','7575852866','hiii, my name is akshay. your service is mind-blowing..!!');
/*!40000 ALTER TABLE `contact_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `haircut_service`
--

DROP TABLE IF EXISTS `haircut_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `haircut_service` (
  `hair_id` int(100) NOT NULL AUTO_INCREMENT,
  `hair_category` varchar(255) NOT NULL,
  `hair_service` varchar(255) NOT NULL,
  `hair_price` int(100) NOT NULL,
  PRIMARY KEY (`hair_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `haircut_service`
--

LOCK TABLES `haircut_service` WRITE;
/*!40000 ALTER TABLE `haircut_service` DISABLE KEYS */;
INSERT INTO `haircut_service` VALUES (12,'hairstyle','Buzz Cut',250),(14,'hairstyle','French Cut',350),(15,'hairstyle','Crew Cut',200),(16,'hairstyle','Mohawak Cut',500),(17,'hairdesign','Hair Crop with Wash',350),(18,'hairdesign','Hair Color',500),(19,'hairdesign','Hair Crop Prince (Up to 10 Yrs)',250),(20,'hairdesign','Smooth Hair Shower',150);
/*!40000 ALTER TABLE `haircut_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `membership_payments`
--

DROP TABLE IF EXISTS `membership_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `membership_payments` (
  `m_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `membership_type` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `card_name` varchar(100) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) DEFAULT 'active',
  PRIMARY KEY (`m_id`),
  KEY `id` (`id`),
  CONSTRAINT `membership_payments_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `membership_payments`
--

LOCK TABLES `membership_payments` WRITE;
/*!40000 ALTER TABLE `membership_payments` DISABLE KEYS */;
INSERT INTO `membership_payments` VALUES (2,6,'Yearly Royal Pass',11999.00,'akshay','1234567890','2024-10-09 19:26:00','active'),(4,6,'Yearly Royal Pass',11999.00,'akshay','1234567890','2024-10-09 19:28:39','active'),(5,6,'Monthly Classic Pass',699.00,'akshay','1234567890','2024-10-09 19:31:00','active');
/*!40000 ALTER TABLE `membership_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `membership_plans`
--

DROP TABLE IF EXISTS `membership_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `membership_plans` (
  `mp_id` int(11) NOT NULL,
  `pass_key` varchar(20) NOT NULL,
  `display_name` varchar(150) NOT NULL,
  `billing_plan` varchar(20) NOT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `features_json` longtext NOT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `membership_plans`
--

LOCK TABLES `membership_plans` WRITE;
/*!40000 ALTER TABLE `membership_plans` DISABLE KEYS */;
INSERT INTO `membership_plans` VALUES (1,'royal','Royal Pass','yearly',11999,'[\"2 complimentary Child HairCut Per 3 Month\",\"2 complimentary Hair Style per 3 month\",\"Free Product Gift & Samples\",\"Unlimited Beards & Skin Services\",\"Unlimited Hair Styling 2 Times a Month\"]',1),(2,'royal','Royal Pass','monthly',499,'[\"2 complimentary Child HairCut Per Month\",\"2 complimentary Hair Style per month\",\"50% off On Spa services\",\"Priority booking With Top stylists\"]',0),(3,'classic','Classic Pass','yearly',7999,'[\"1 complimentary Child HairCut Per Month\",\"1 complimentary Hair Style per month\",\"Free Product Samples\",\"Priority booking Preferred Stylists\",\"Unlimited Beards & Skin Services\"]',0),(4,'classic','Classic Pass','monthly',699,'[\"1 complimentary Child HairCut Per Month\",\"1 complimentary Hair Style per month\",\"30% off On Spa services\",\"Free Product Samples\",\"Priority booking Preferred Stylists\"]',1),(5,'standard','Standard Pass','yearly',3999,'[\"1 complimentary HairCut Per 3 Months\",\"10% off On Hair Styling\",\"5% off On Beard services\",\"Priority booking\"]',0),(6,'standard','Standard Pass','monthly',399,'[\"10% off On Hair Styling\",\"20% off On Spa services\",\"5% off On Beard services\",\"Priority booking\"]',0);
/*!40000 ALTER TABLE `membership_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_updates`
--

DROP TABLE IF EXISTS `order_status_updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `s_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `update_date` date NOT NULL,
  `update_time` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_updates`
--

LOCK TABLES `order_status_updates` WRITE;
/*!40000 ALTER TABLE `order_status_updates` DISABLE KEYS */;
INSERT INTO `order_status_updates` VALUES (1,117,'confirmed','2026-03-28','11:02:35'),(2,117,'processing','2026-03-28','11:04:33'),(3,118,'confirmed','2026-03-28','11:41:02'),(4,117,'cancelled','2026-03-28','12:05:11'),(5,119,'pending','2026-03-28','12:55:00'),(6,119,'confirmed','2026-03-28','12:55:39'),(7,119,'cancelled','2026-03-28','12:55:47'),(8,120,'confirmed','2026-03-28','12:56:53'),(9,120,'cancelled','2026-03-28','13:06:04'),(10,120,'refunded','2026-03-28','13:06:17');
/*!40000 ALTER TABLE `order_status_updates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment`
--

DROP TABLE IF EXISTS `payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment` (
  `pay_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) DEFAULT NULL,
  `s_id` int(11) DEFAULT NULL,
  `p_name` varchar(50) NOT NULL,
  `p_phno` varchar(20) DEFAULT NULL,
  `p_address` varchar(500) NOT NULL,
  `p_city` varchar(255) NOT NULL,
  `p_state` varchar(255) NOT NULL,
  `p_pincode` int(6) NOT NULL,
  `p_method` varchar(50) NOT NULL,
  `p_date` date NOT NULL,
  `p_time` time NOT NULL,
  `p_status` varchar(100) NOT NULL,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `stripe_payment_status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`pay_id`),
  KEY `s_id` (`s_id`),
  KEY `id` (`id`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`s_id`) REFERENCES `product_sales` (`s_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment`
--

LOCK TABLES `payment` WRITE;
/*!40000 ALTER TABLE `payment` DISABLE KEYS */;
INSERT INTO `payment` VALUES (68,6,115,'akshay','2147483647','x','savarkundla','gujrat',364515,'Cash On Delivery','2024-10-14','09:19:51','Pending',NULL,NULL),(69,6,116,'Hariyani akshay ','7676876788','testing','rajkot','gujrat',345678,'Stripe','2026-03-28','09:56:18','Success','pi_3TFsfFA7H6HALvmQ1Ofud9A8','succeeded'),(70,6,117,'Hariyani Akshay','7766776677','testing address','rajkot','gujrat',364515,'stripe','2026-03-28','11:02:35','paid','pi_3TFthOA7H6HALvmQ01JltC11','succeeded'),(71,6,118,'testing','6565656565','testing','rajkot','gujrat',556655,'stripe','2026-03-28','11:41:02','paid','pi_3TFuIaA7H6HALvmQ0OEVWtc0','succeeded'),(72,6,119,'testing','112233445566','testing','testing','7766554433',223344,'cod','2026-03-28','12:55:00','cancelled',NULL,NULL),(73,6,120,'testing','4455667788','testing','testing','testing',776655,'stripe','2026-03-28','12:56:53','refunded','pi_3TFvU0A7H6HALvmQ1UntkqmG','succeeded');
/*!40000 ALTER TABLE `payment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_cart`
--

DROP TABLE IF EXISTS `product_cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_cart` (
  `c_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `c_img` varchar(100) NOT NULL,
  `c_name` varchar(50) NOT NULL,
  `c_price` int(11) NOT NULL,
  `c_size` varchar(50) DEFAULT NULL,
  `c_quantity` int(11) DEFAULT 1,
  `c_total` int(11) DEFAULT NULL,
  `c_grand_total` int(11) DEFAULT NULL,
  PRIMARY KEY (`c_id`),
  UNIQUE KEY `id` (`id`,`p_id`),
  KEY `p_id` (`p_id`),
  CONSTRAINT `product_cart_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_cart_ibfk_2` FOREIGN KEY (`p_id`) REFERENCES `products` (`p_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_cart`
--

LOCK TABLES `product_cart` WRITE;
/*!40000 ALTER TABLE `product_cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_cart` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_sales`
--

DROP TABLE IF EXISTS `product_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_sales` (
  `s_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) DEFAULT NULL,
  `s_img` varchar(50) NOT NULL,
  `s_name` varchar(50) NOT NULL,
  `s_price` int(10) NOT NULL,
  `s_size` varchar(50) NOT NULL,
  `s_quantity` int(10) NOT NULL,
  `s_total` int(10) NOT NULL,
  `s_grand_total` int(10) NOT NULL,
  `s_date` date NOT NULL,
  `s_status` varchar(100) NOT NULL,
  `s_time` time(6) NOT NULL,
  PRIMARY KEY (`s_id`),
  KEY `id` (`id`),
  CONSTRAINT `product_sales_ibfk_1` FOREIGN KEY (`id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_sales`
--

LOCK TABLES `product_sales` WRITE;
/*!40000 ALTER TABLE `product_sales` DISABLE KEYS */;
INSERT INTO `product_sales` VALUES (115,6,'hairpowder.jpg','Hair Powder',349,'100ml',1,349,349,'2024-10-14','Cancelled','09:19:51.000000'),(116,6,'homespray.jpg','Hair Spray',499,'100ml',1,499,499,'2026-03-28','Order Placed','09:56:18.000000'),(117,6,'shampoo.png','Hair Shampoo',399,'100ml',1,399,399,'2026-03-28','cancelled','11:02:35.000000'),(118,6,'hairpowder.jpg','Hair Powder',349,'100ml',1,279,279,'2026-03-28','confirmed','11:41:02.000000'),(119,6,'hairpowder.jpg','Hair Powder',349,'100ml',1,279,279,'2026-03-28','cancelled','12:55:00.000000'),(120,6,'hairpowder.jpg','Hair Powder',349,'100ml',1,279,279,'2026-03-28','refunded','12:56:53.000000');
/*!40000 ALTER TABLE `product_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `p_id` int(100) NOT NULL AUTO_INCREMENT,
  `p_name` varchar(255) NOT NULL,
  `p_desc` varchar(255) NOT NULL,
  `p_price` int(100) NOT NULL,
  `p_discount` decimal(5,2) NOT NULL DEFAULT 0.00,
  `p_size` varchar(100) NOT NULL,
  `p_overview` varchar(500) DEFAULT NULL,
  `p_f1` varchar(100) NOT NULL,
  `p_f2` varchar(100) NOT NULL,
  `p_ingred` varchar(100) NOT NULL,
  `p_img` varchar(255) NOT NULL,
  `p_quantity` int(50) NOT NULL,
  PRIMARY KEY (`p_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (18,'Hair Powder','ClassyCuts volumizing powder wax adds instant lift and texture with a lightweight, natural feel.',349,20.00,'100ml','ClassyCut Hair Volumizing Powder is designed to create instant volume and texture. Infused with silica and rice starch, it lifts and adds body while absorbing excess oil. This powder helps you achieve fuller, more voluminous hair effortlessly.','Instant root lift and volume boost.','            Matte finish with a long-lasting hold.','Adds texture and volume to hair.Strengthens and supports hair structure.','hairpowder.jpg',17),(19,'Hair Oil','ClassyCuts Hair Oil nourishes and protects your hair with a luxurious, silky smooth finish.',299,0.00,'100ml','ClassyCut Hair Oil is a luxurious blend designed to nourish and enhance your hair. With argan oil and jojoba oil, it provides deep moisture and shine, while hyaluronic acid tackles frizz.','Nourishes and hydrates for silky, smooth hair.','    Adds shine while reducing frizz and split ends.','Argan Oil: Nourishes and adds shine to hair.\r\nHyaluronic Acid: Hydrates and helps manage frizz.','hairoil.jpg',2),(20,'Hair Spray','ClassyCuts Strong Hold Hair Spray, a fast-drying, non-sticky formula that keeps your look in place all day.',499,0.00,'100ml','ClassyCut Hair Spray delivers a flexible, long-lasting hold while enhancing shine and reducing frizz. Infused with panthenol and hyaluronic acid, it strengthens and moisturizes your hair. Aloe vera adds a soothing touch.','Strong hold with instant volume and shine.','  Humidity-resistant with long-lasting control.','Panthenol (Vitamin B5): Strengthens and adds shine.\r\nHyaluronic Acid: Provides moisture and reduces ','homespray.jpg',4),(21,'Hair Wax','ClassyCuts provides hair wax delivers a strong, lexible hold with, matte texture for all-day style.',699,0.00,'50g','ClassyCut Hair Wax delivers a strong, flexible hold with a natural matte finish. \r\n','Strong hold with flexible styling.','  Matte finish for a natural look.','Coconut Oil: Moisturizes and adds a subtle shine.\r\nShea Butter: Nourishes and softens hair.','wax.jpg',5),(22,'Hair Conditioner','classycuts hair conditioner is smooths, detangles and leaving it soft and shiny.',199,0.00,'100ml','The ClassyCut Hair Conditioner is crafted to nourish and enhance your hair. With moisturizing argan oil and hydrating hyaluronic acid, it provides deep hydration and combats frizz. this conditioner leaves your hair soft, smooth, and revitalized.','Deeply hydrates and detangles for smooth, manageable hair.','  Enhances shine and strengthens strands with every use.','Argan Oil: Moisturizes and smooths hair.\r\nHyaluronic Acid: Provides deep hydration and reduces frizz','conditioner.jpg',5),(23,'Hair Shampoo','ClassyCuts shampoo deeply cleanses and hydrates for soft, healthy, and manageable hair.',399,0.00,'100ml','The ClassyCut Vitamin C Hair Shampoo is designed to revitalize and strengthen your hair from root to tip. Enriched with Vitamin C and biotin, it promotes healthy hair growth, while aloe vera and argan oil deeply moisturize and add a luxurious shine.','Cleanses and nourishes for healthy, balanced hair.','  Gentle formula enhances shine and reduces frizz.','Vitamin C: Promotes healthy scalp and strengthens hair.\r\nAloe Vera: Moisturizes and soothes the scal','shampoo.png',4),(24,'Hair Serum','ClassyCuts Hair Serum  a lightweight, shine, and protects your hair from heat and damage.',499,0.00,'50ml','ClassyCut Hair Serum is a lightweight formula designed to enhance shine and smoothness. With nourishing argan oil and hydrating hyaluronic acid, it provides deep moisture while reducing frizz. ','Nourishes and smooths for sleek, frizz-free hair.','  Adds shine and reduces split ends for a healthy look.','Argan Oil: Nourishes and adds shine.\r\nHyaluronic Acid: Hydrates and smooths.\r\nKeratin: Strengthens a','serum.jpg',5),(25,'Hair gel','ClassyCuts provides hair gel offers firm control and a smooth, residue-free shine for any style.',249,0.00,'50g','ClassyCut Hair Gel is designed to give your hair a strong, long-lasting hold with a natural finish. Enriched with aloe vera and glycerin, it hydrates and adds shine while hydrolyzed proteins strengthen and protect. ','Provides strong hold and defines styles without flaking.','  Adds shine and controls frizz for a polished finish.','Aloe Vera: Soothes and hydrates the scalp.\r\nGlycerin: Provides moisture and adds shine.','hairjel.jpg',5),(26,'Face Wash','ClassyCuts Face Wash gently cleanses and balances your skin, removing impurities for a refreshed and glow.',499,0.00,'100ml','ClassyCut Face Wash is formulated to cleanse and refresh your skin while addressing acne and dullness. With salicylic acid for exfoliation, hyaluronic acid for hydration, and green tea extract for soothing, it cleanses deeply and enhances your complexion.','Gently cleanses and removes impurities for fresh, clear skin.','  Balances and refreshes with a soothing, hydrating formula.','Salicylic Acid: Exfoliates and helps prevent acne.\r\nHyaluronic Acid: Hydrates and maintains moisture','facewash.jpg',5),(27,'Face Cream','ClassyCuts hydrating face cream deeply moisturizes and rejuvenates skin for a radiant, youthful glow.',199,0.00,'100ml','ClassyCut Face Cream is a rich, hydrating formula designed to nourish and rejuvenate your skin. With hyaluronic acid for deep hydration, vitamin E for protection, and niacinamide for brightening, it helps to even out skin tone and reduce the appearance of pores.','Moisturizes and nourishes for soft, radiant skin.','  Reduces fine lines and improves texture with daily use.','Hyaluronic Acid: Deeply hydrates and plumps the skin.\r\nVitamin E: Nourishes and protects against env','facecream.jpg',5),(28,'Beard Oil','ClassyCuts beard oil conditions and softens for a well-groomed, smooth beard with a subtle shine.',499,0.00,'100ml','ClassyCut Beard Oil is designed to hydrate and condition your beard while soothing the skin underneath. With argan and jojoba oils for deep moisture and shine, it keeps your beard soft and manageable. Vitamin E provides essential nutrients and protectio.','Nourishes and hydrates for a softer, more manageable beard.','  Reduces itchiness and adds a natural shine.','Argan Oil: Moisturizes and softens beard hair.\r\nJojoba Oil: Conditions and promotes a healthy shine.','beardoil2.jpg',5),(29,'Beard Cream','ClassyCuts beard cream tames and hydrates your beard, ensuring a smooth, polished look with every use.',799,0.00,'100g','ClassyCut Beard Cream is formulated to condition and tame your beard. With jojoba oil and shea butter for deep moisture and softness, it enhances manageability and reduces dryness. ','Moisturizes and softens beard for a smoother feel.','  Tames unruly hair and adds a subtle shine.','Jojoba Oil: Moisturizes and softens beard hair.\r\nShea Butter: Provides nourishment and improves mana','beardcream.jpg',5),(30,'Golden Face Mask','ClassyCuts Gold Mask delivers a golden touch of luxury, illuminating your skin for a radiant glow.',1999,0.00,'50g','The ClassyCut Gold Face Mask is a luxurious treatment designed to rejuvenate and brighten the skin. Infused with 24K gold and collagen, it helps to reduce fine lines, improve skin firmness, and deliver a glowing complexion.','Revitalizes skin with a radiant glow.','  Nourishes and hydrates for a youthful appearance.','24K Gold: Enhances skin radiance and elasticity.\r\nCollagen: Firms and smooths the skin.','goldmask.jpg',5),(31,'Silver Face Mask','ClassyCuts Silver Mask revitalizes your skin with a premium silver formula for a luminous, sophisticated glow.',1499,0.00,'50g','The ClassyCut Silver Face Mask is crafted to detoxify and clarify the skin. With the purifying power of colloidal silver and activated charcoal, it effectively removes impurities, reduces excess oil, and prevents blemishes.','Revitalizes and brightens with a radiant silver glow.','  Hydrates and smooths skin for a refreshed, youthful appearance.','Colloidal Silver: Helps purify and balance the skin.\r\nActivated Charcoal: Draws out impurities and t','silvermask.jpg',5),(32,'Charcol Face Mask','ClassyCuts Charcoal Facial Mask detoxifies and purifies for a clear and refreshed complexion',999,0.00,'50g','The ClassyCut Charcoal Face Mask is a powerful treatment designed to detoxify and purify the skin. With activated charcoal and kaolin clay, it effectively draws out impurities, controls oil, and exfoliates dead skin cells.','Deeply cleanses and detoxifies pores.','  Removes impurities for a clear, matte complexion.','Activated Charcoal: Deeply cleanses and detoxifies the skin.\r\nKaolin Clay: Absorbs excess oil and mi','charcolmask.jpg',5),(33,'Vitamin-c Face Mask','ClassyCuts Vitamin C Face mask brightens and energizes your skin, revealing a radiant and youthful complexion.',599,0.00,'50g','The ClassyCut Vitamin C Face Mask is formulated to brighten and revitalize the skin. Packed with potent antioxidants like Vitamin C and turmeric, it targets dark spots, evens skin tone, and provides deep hydration.','Brightens skin with a radiant glow.','  Boosts collagen and fights signs of aging.','Vitamin C: Brightens skin and reduces dark spots.\r\nTurmeric Extract: Evens skin tone and fights infl','vitaminmask.jpg',5);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `royal_membership`
--

DROP TABLE IF EXISTS `royal_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `royal_membership` (
  `royal_id` int(100) NOT NULL AUTO_INCREMENT,
  `royal_plan` varchar(100) NOT NULL,
  `royal_desc` varchar(255) NOT NULL,
  `royal_price` int(100) NOT NULL,
  PRIMARY KEY (`royal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `royal_membership`
--

LOCK TABLES `royal_membership` WRITE;
/*!40000 ALTER TABLE `royal_membership` DISABLE KEYS */;
INSERT INTO `royal_membership` VALUES (1,'yearly','50% off On Spa services',0),(2,'yearly','Unlimited Hair Styling 2 Times a Month',0),(3,'yearly','Unlimited Beards & Skin Services',0),(4,'yearly','2 complimentary Hair Style per 3 month',0),(5,'yearly','2 complimentary Child HairCut Per 3 Month',0),(22,'yearly','Free Product Gift & Samples',11999),(23,'monthly','50% off On Spa services',0),(24,'monthly','2 complimentary Hair Style per month',0),(25,'monthly','2 complimentary Child HairCut Per Month',0),(26,'monthly','Priority booking With Top stylists',0),(27,'monthly','Free Product Gift & Samples',1299);
/*!40000 ALTER TABLE `royal_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schema_migrations`
--

DROP TABLE IF EXISTS `schema_migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schema_migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `migration_name` varchar(255) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_migration_name` (`migration_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schema_migrations`
--

LOCK TABLES `schema_migrations` WRITE;
/*!40000 ALTER TABLE `schema_migrations` DISABLE KEYS */;
INSERT INTO `schema_migrations` VALUES (1,'2026_03_28_wallet_refund_checkout_flow','2026-03-28 11:53:14');
/*!40000 ALTER TABLE `schema_migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skin_service`
--

DROP TABLE IF EXISTS `skin_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skin_service` (
  `skin_id` int(100) NOT NULL AUTO_INCREMENT,
  `skin_service` varchar(255) NOT NULL,
  `skin_price` int(100) NOT NULL,
  PRIMARY KEY (`skin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skin_service`
--

LOCK TABLES `skin_service` WRITE;
/*!40000 ALTER TABLE `skin_service` DISABLE KEYS */;
INSERT INTO `skin_service` VALUES (7,'Mens Facial',150),(8,'Brightening Facial',350),(9,'Hydra Facial',250),(10,'Collagen Facial',400),(11,'Chemical Peel',300),(12,'Charcoal Facial',500),(14,'Oxygen Facial',600),(15,'Laser Skin Resurfacing',1000);
/*!40000 ALTER TABLE `skin_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spa_service`
--

DROP TABLE IF EXISTS `spa_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spa_service` (
  `spa_id` int(100) NOT NULL AUTO_INCREMENT,
  `spa_category` varchar(255) NOT NULL,
  `spa_service` varchar(255) NOT NULL,
  `spa_price` int(100) NOT NULL,
  PRIMARY KEY (`spa_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spa_service`
--

LOCK TABLES `spa_service` WRITE;
/*!40000 ALTER TABLE `spa_service` DISABLE KEYS */;
INSERT INTO `spa_service` VALUES (6,'bodytreatment','Body Scrub',400),(7,'bodytreatment','Hydrating Body Treatment',600),(8,'bodytreatment','Detoxifying Mud Wrap',700),(9,'bodytreatment','Cellulite Treatment',350),(10,'bodytreatment','Paraffin Body Treatment',850),(11,'bodymassage','Full Body Exfoliation',2500),(12,'bodymassage','Full Hand Massage',1200),(14,'bodymassage','Massage & Wrap',3500),(15,'bodymassage','Hot Stone Massage',3000),(16,'bodymassage','Deep Tissue Massage',2000),(17,'bodymassage','Ayurvedic Massage',1500);
/*!40000 ALTER TABLE `spa_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `standard_membership`
--

DROP TABLE IF EXISTS `standard_membership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `standard_membership` (
  `standard_id` int(100) NOT NULL AUTO_INCREMENT,
  `standard_plan` varchar(100) NOT NULL,
  `standard_desc` varchar(255) NOT NULL,
  `standard_price` int(100) NOT NULL,
  PRIMARY KEY (`standard_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `standard_membership`
--

LOCK TABLES `standard_membership` WRITE;
/*!40000 ALTER TABLE `standard_membership` DISABLE KEYS */;
INSERT INTO `standard_membership` VALUES (1,'yearly','15% off On Spa services',0),(2,'yearly','10% off On Hair Styling',0),(3,'yearly','5% off On Beard services',0),(4,'yearly','1 complimentary HairCut Per 3 Months',0),(5,'yearly','Priority booking',3999),(18,'monthly','20% off On Spa services',0),(19,'monthly','10% off On Hair Styling',0),(20,'monthly','5% off On Beard services',0),(21,'monthly','Priority booking',399);
/*!40000 ALTER TABLE `standard_membership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_reg`
--

DROP TABLE IF EXISTS `user_reg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_reg` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(8) NOT NULL,
  `profile_img` varchar(255) DEFAULT 'photos/default.jpeg',
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_reg`
--

LOCK TABLES `user_reg` WRITE;
/*!40000 ALTER TABLE `user_reg` DISABLE KEYS */;
INSERT INTO `user_reg` VALUES (5,'adesh','adesh@gmail.com','adesh','123','../upload_img/Snapchat-1330099180.jpg','2024-10-06 20:28:51'),(6,'akshay','akshayhariyani007@gmail.com','akki07','123','WhatsApp Image 2024-01-22 at 10.27.29_1eae723a.jpg','2024-10-14 00:39:00'),(13,'prince','princedodiya2663@gmail.com','prince','123','','2024-10-14 00:02:15'),(14,'ujas gediya','ujas@gmail.com','ujas','123','',NULL),(15,'parth','airwellcompany@gmail.com','parth','1234','',NULL),(16,'akshay','akshay@gmail.com','akshay','akshay20','','2025-06-17 23:22:45');
/*!40000 ALTER TABLE `user_reg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallet_transactions`
--

DROP TABLE IF EXISTS `wallet_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'credit',
  `source` varchar(50) NOT NULL DEFAULT 'refund',
  `order_id` int(11) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_id` int(11) DEFAULT NULL,
  `sale_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  KEY `sale_id` (`sale_id`),
  KEY `idx_wallet_order_source` (`user_id`,`order_id`,`source`),
  CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_reg` (`id`),
  CONSTRAINT `wallet_transactions_ibfk_2` FOREIGN KEY (`sale_id`) REFERENCES `product_sales` (`s_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallet_transactions`
--

LOCK TABLES `wallet_transactions` WRITE;
/*!40000 ALTER TABLE `wallet_transactions` DISABLE KEYS */;
INSERT INTO `wallet_transactions` VALUES (10,6,399.00,'credit','refund',117,'2026-03-28 11:05:11',NULL,117),(11,6,279.00,'credit','refund',120,'2026-03-28 12:06:17',NULL,120);
/*!40000 ALTER TABLE `wallet_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wallets`
--

DROP TABLE IF EXISTS `wallets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wallets` (
  `user_id` int(11) NOT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_wallets_user` FOREIGN KEY (`user_id`) REFERENCES `user_reg` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wallets`
--

LOCK TABLES `wallets` WRITE;
/*!40000 ALTER TABLE `wallets` DISABLE KEYS */;
INSERT INTO `wallets` VALUES (5,0.00,'2026-03-28 11:53:14'),(6,678.00,'2026-03-28 12:06:17'),(13,0.00,'2026-03-28 11:53:14'),(14,0.00,'2026-03-28 11:53:14'),(15,0.00,'2026-03-28 11:53:14'),(16,0.00,'2026-03-28 11:53:14');
/*!40000 ALTER TABLE `wallets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'classycut'
--

--
-- Dumping routines for database 'classycut'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-05 23:44:11
