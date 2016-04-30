-- MySQL dump 10.13  Distrib 5.5.47, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: psycho_tests
-- ------------------------------------------------------
-- Server version	5.5.47-0ubuntu0.14.04.1

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
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `date` datetime NOT NULL,
  `user_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_options`
--

DROP TABLE IF EXISTS `test_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) NOT NULL,
  `points` int(11) NOT NULL DEFAULT '0',
  `question_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `test_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `test_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_options`
--

LOCK TABLES `test_options` WRITE;
/*!40000 ALTER TABLE `test_options` DISABLE KEYS */;
INSERT INTO `test_options` VALUES (1,'Да',1,1),(2,'Нет',0,1),(3,'Да',0,2),(4,'Нет',1,2),(5,'Да',1,3),(6,'Нет',0,3),(7,'Да',1,4),(8,'Нет',0,4),(9,'Да',0,5),(10,'Нет',1,5),(11,'Да',1,6),(12,'Нет',0,6),(13,'Да',1,7),(14,'Нет',0,7),(15,'Да',1,8),(16,'Нет',0,8),(17,'Да',0,9),(18,'Нет',1,9),(19,'Да',1,10),(20,'Нет',0,10),(21,'Да',1,11),(22,'Нет',0,11),(23,'Да',1,12),(24,'Нет',0,12),(25,'Да',1,13),(26,'Нет',0,13),(27,'Да',1,14),(28,'Нет',0,14),(29,'Да',1,15),(30,'Нет',0,15),(31,'Да',1,16),(32,'Нет',0,16),(33,'Да',1,17),(34,'Нет',0,17),(35,'Да',1,18),(36,'Нет',0,18),(37,'Да',1,19),(38,'Нет',0,19),(39,'Да',1,20),(40,'Нет',0,20),(41,'Да',0,21),(42,'Нет',1,21),(43,'Да',1,22),(44,'Нет',0,22),(45,'Да',1,23),(46,'Нет',0,23),(47,'Да',0,24),(48,'Нет',1,24),(49,'Да',0,25),(50,'Нет',1,25),(51,'Да',0,26),(52,'Нет',1,26),(53,'Да',0,27),(54,'Нет',1,27),(55,'Да',0,28),(56,'Нет',1,28),(57,'Да',0,29),(58,'Нет',1,29),(59,'Да',0,30),(60,'Нет',1,30),(61,'Да',0,31),(62,'Нет',1,31),(63,'Да',0,32),(64,'Нет',1,32),(65,'Да',0,33),(66,'Нет',1,33),(67,'Да',0,34),(68,'Нет',1,34),(69,'Да',0,35),(70,'Нет',1,35),(71,'Да',0,36),(72,'Нет',1,36),(73,'Да',0,37),(74,'Нет',1,37),(75,'Да',0,38),(76,'Нет',1,38),(77,'Да',0,39),(78,'Нет',1,39),(79,'Да',0,40),(80,'Нет',1,40),(81,'Да',1,41),(82,'Нет',0,41),(83,'Да',0,42),(84,'Нет',1,42),(85,'Да',1,43),(86,'Нет',0,43),(87,'Да',0,44),(88,'Нет',1,44),(89,'Да',1,45),(90,'Нет',0,45),(91,'Афанасий',0,46),(92,'Феликс',1,46),(93,'Люцифер',0,46);
/*!40000 ALTER TABLE `test_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `price` float NOT NULL,
  `order_date` datetime NOT NULL,
  `customer_id` int(10) NOT NULL,
  `test_id` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `test_id` (`test_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_questions`
--

DROP TABLE IF EXISTS `test_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` varchar(255) NOT NULL,
  `test_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  CONSTRAINT `test_questions_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_questions`
--

LOCK TABLES `test_questions` WRITE;
/*!40000 ALTER TABLE `test_questions` DISABLE KEYS */;
INSERT INTO `test_questions` VALUES (1,'Легко ли вы запомните 5 различных телефонных номеров?',1),(2,'Случалось ли вам забыть о деловой встрече?',1),(3,'Можете ли вы вспомнить, что вы ели на завтрак 3 дня назад?',1),(4,'Помните ли вы свой первый поцелуй?',1),(5,'Позабыли ли вы свою первую любовь?',1),(6,'Можете ли вы вспомнить свой первый день в школе?',1),(7,'Сумеете ли прочесть наизусть стихотворение, которое учили еще в детстве?',1),(8,'Можете ли вы припомнить что-нибудь из одежды, которую носили в 10 лет?',1),(9,'Перед тем как позвонить, вы обычно ищете номер в телефонной книжке?',1),(10,'Можете ли вы вспомнить имя вашего первого друга?',1),(11,'Напоминают ли вам некоторые запахи какие-либо знакомые места?',1),(12,'Можете ли вы вспомнить дорогу, по которой ходили когда-то в школу?',1),(13,'Помните ли вы о днях рождения и юбилеях друзей?',1),(14,'Можете ли вы вспомнить, что у вас было на обед в прошлое воскресенье?',1),(15,'Помните ли, как прошел ваш восемнадцатый день рождения?',1),(16,'Можете ли вы вспомнить название первой понравившейся вам книги?',1),(17,'Помните ли вы, на какую тему была последняя прослушанная вами радиопередача?',1),(18,'Как вы были одеты в последний день перед уходом на военную службу? (Как вы были одеты на школьном выпускном балу?)',1),(19,'Помните ли вы, когда в последний раз были в театре?',1),(20,'Помните ли вы, в каком возрасте вы впервые самостоятельно ехали поездом?',1),(21,'Трудно ли вам вспомнить последние темы дня события?',1),(22,'Помните ли вы имя девушки, которой впервые назначили свидание? (Помните ли вы имя молодого человека, который впервые назначил вам свидание?)',1),(23,'Можете ли вы вспомнить, как звали вашего учителя в первом классе?',1),(24,'Трудно ли вам следить за действием фильма, телепередачи, книги, потому что вы забыли, что произошло в начале?',1),(25,'Случается ли вам, входя в комнату, мучительно вспомнить, зачем, собственно, вы пришли?',1),(26,'Бывает ли так, что вы забываете о важных для вас запланированных делах: оплатить счет в банке, прийти на встречу?',1),(27,'Затрудняетесь ли вы вспомнить номера телефонов, которыми постоянно пользуетесь?',1),(28,'Случается ли так, что вы забыли фамилию или имя человека, с которым часто общаетесь?',1),(29,'Способны ли вы заблудиться в знакомом месте?',1),(30,'Вспоминаете ли вы иногда с трудом, куда положили ту или иную ненужную вам вещь?',1),(31,'Бывает ли так, что вы забыли выключить газ, свет, закрыть входную дверь, покидая квартиру?',1),(32,'Случается ли вам несколько раз повторять одно и то же, вызывая недоумение окружающих?',1),(33,'Трудно ли вам вспоминать имена популярных людей или названия известных мест?',1),(34,'Вы вынуждены все записывать, так как не надеетесь на свою память?',1),(35,'Трудно ли вам запоминать новые игры, рецепты?',1),(36,'Теряете ли вы вещи?',1),(37,'Случается ли, что вы моментально забываете то, что вам только что сказали?',1),(38,'Бывает ли, что выбежав из дому, вы с ужасом вспоминаете, выключен ли утюг?',1),(39,'Собираясь в магазин, вы берете список продуктов, которые нужно купить?',1),(40,'Случалось ли вам забыть выполнить чье-то поручение?',1),(41,'Могли бы вы сейчас вспомнить адреса школьных друзей?',1),(42,'Замечаете ли вы, что читая, не вникаете в текст, так как думаете о другом?',1),(43,'Вы уверены, что можно положиться на ваши свидетельские показания?',1),(44,'Познакомившись с человеком, вы сразу забываете его имя?',1),(45,'Вы помните по именам всех своих школьных учителей?',1),(46,'Как зовут твоего кота?',2);
/*!40000 ALTER TABLE `test_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_results`
--

DROP TABLE IF EXISTS `test_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `test_id` int(11) NOT NULL,
  `min_points` int(11) NOT NULL,
  `max_points` int(11) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  CONSTRAINT `test_results_ibfk_1` FOREIGN KEY (`test_id`) REFERENCES `tests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_results`
--

LOCK TABLES `test_results` WRITE;
/*!40000 ALTER TABLE `test_results` DISABLE KEYS */;
INSERT INTO `test_results` VALUES (1,1,40,45,'У вас отличная память. Она вас подводит крайне редко. Вы пользуетесь записной книжкой, но при необходимости можете обойтись и без нее. Окружающие по достоинству оценивают ваши способности, и прежде всего тосность, пунктуальность и обязательность. Такие качества заслуживают уважения.'),(2,1,15,39,'Без записной книжки вам не обойтись. Так что оперативно и эффективно использовать ситуацию вам не всегда удается. Поэтому не лишне было бы потренировать память, хотя бы для того, чтобы на работе чувствовать себя гораздо спокойнее.'),(3,1,0,14,'На вашу память полагаться не стоит. Ваша забывчивость вредит вам не только на работе, но и в личной жизни. Из-за этого у вас появилась неуверенность в себе. Вам поможет только самодисциплина, тщательное и аккуратное фиксирование на бумаге важной информации. Ну и, конечно, не повредят специальные упражнения, тренирующие память.'),(4,2,1,1,'Вы знаете как зовут вашего кота, поздравляем!'),(5,2,0,0,'Вы не знаете имени своего животного! Стыд и позор!');
/*!40000 ALTER TABLE `test_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tests`
--

DROP TABLE IF EXISTS `tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tests` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created` datetime NOT NULL,
  `passed` tinyint(4) DEFAULT '0',
  `price` float NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tests`
--

LOCK TABLES `tests` WRITE;
/*!40000 ALTER TABLE `tests` DISABLE KEYS */;
INSERT INTO `tests` VALUES (1,'Диагностика общего состояния памяти',' С помощью данного теста можно определить состояние вашей памяти. Тест-опросник включает сорок пять утверждений, требующих однозначной реакции (\"да\" или \"нет\").','2015-02-16 15:13:45',8,0),(2,'тестовый тест','описание тестового теста','2016-02-20 16:58:00',64,0);
/*!40000 ALTER TABLE `tests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(64) NOT NULL,
  `wallet` float DEFAULT '0',
  `registered_at` datetime NOT NULL,
  `activation_code` varchar(255) NOT NULL,
  `active` tinyint(4) DEFAULT '0',
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_uindex` (`username`)

) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_collection`
--

DROP TABLE IF EXISTS passed_tests;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_collection` (
  `user_id` int(10) NOT NULL,
  `points` int(10) NOT NULL DEFAULT '0',
  `result_id` int(10) NOT NULL,
  `test_data` blob NOT NULL,
  PRIMARY KEY (`user_id`,`result_id`),
  CONSTRAINT `user_collection_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `user_collection_ibfk_2` FOREIGN KEY (`result_id`) REFERENCES `test_results` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-04-26  0:27:24
