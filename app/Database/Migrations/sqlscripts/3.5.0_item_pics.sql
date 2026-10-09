CREATE TABLE IF NOT EXISTS `ospos_item_pics` (
  `item_pic_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`item_pic_id`),
  KEY `ospos_item_pics_item_id` (`item_id`)
) ENGINE=InnoDB;