--
-- Table structure for table `ps`
--

DROP TABLE IF EXISTS `ps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ps` (
  `id` varchar(100) NOT NULL,
  `que_num` varchar(10) DEFAULT NULL,
  `start` double DEFAULT NULL,
  `record` double DEFAULT NULL,
  `try` int(11) DEFAULT NULL,
  `nation` varchar(3) DEFAULT 'en',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;