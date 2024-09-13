CREATE TABLE IF NOT EXISTS `cs_product_list` (
  `pl_id` int NOT NULL AUTO_INCREMENT,
  `pl_title` varchar(255) NOT NULL,
  `pl_body` text NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci