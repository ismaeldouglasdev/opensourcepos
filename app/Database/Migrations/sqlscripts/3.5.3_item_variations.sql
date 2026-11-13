CREATE TABLE IF NOT EXISTS `ospos_item_variations` (
  `item_variation_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL COMMENT 'parent item (scanned, holds EAN)',
  `variation_item_id` int(11) NOT NULL COMMENT 'child item (sold)',
  `attribute_name` varchar(50) NOT NULL DEFAULT 'SIZE',
  `attribute_value` varchar(100) NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`item_variation_id`),
  UNIQUE KEY `uq_item_attr_value` (`item_id`,`attribute_name`,`attribute_value`),
  UNIQUE KEY `uq_variation_item` (`variation_item_id`),
  KEY `idx_parent` (`item_id`),
  CONSTRAINT `ospos_item_variations_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `ospos_items` (`item_id`),
  CONSTRAINT `ospos_item_variations_ibfk_2` FOREIGN KEY (`variation_item_id`) REFERENCES `ospos_items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;