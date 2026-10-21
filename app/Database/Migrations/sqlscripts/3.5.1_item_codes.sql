CREATE TABLE IF NOT EXISTS `ospos_item_codes` (
  `item_code_id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) NOT NULL,
  `code` varchar(255) NOT NULL,
  PRIMARY KEY (`item_code_id`),
  UNIQUE KEY `ospos_item_codes_code` (`code`),
  KEY `ospos_item_codes_item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
