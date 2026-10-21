CREATE TABLE IF NOT EXISTS `ospos_items_price_history` (
  `history_id` int(10) NOT NULL AUTO_INCREMENT,
  `item_id` int(10) NOT NULL,
  `previous_cost_price` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(15,2) NOT NULL,
  `previous_unit_price` decimal(15,2) DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `source` varchar(32) NOT NULL DEFAULT 'sistema',
  `changed_by` int(10) DEFAULT NULL,
  `changed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `idx_item_changed` (`item_id`, `changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TRIGGER IF EXISTS `ospos_items_price_history_au`;

CREATE TRIGGER `ospos_items_price_history_au`
AFTER UPDATE ON `ospos_items`
FOR EACH ROW
INSERT INTO `ospos_items_price_history` (
    `item_id`,
    `previous_cost_price`,
    `cost_price`,
    `previous_unit_price`,
    `unit_price`,
    `source`,
    `changed_by`,
    `changed_at`
)
SELECT
    NEW.item_id,
    OLD.cost_price,
    NEW.cost_price,
    OLD.unit_price,
    NEW.unit_price,
    COALESCE(@ospos_price_change_source, 'sistema'),
    @ospos_price_change_by,
    NOW()
FROM DUAL
WHERE (NEW.cost_price <> OLD.cost_price OR (NEW.cost_price IS NOT NULL AND OLD.cost_price IS NULL) OR (NEW.cost_price IS NULL AND OLD.cost_price IS NOT NULL))
   OR (NEW.unit_price <> OLD.unit_price OR (NEW.unit_price IS NOT NULL AND OLD.unit_price IS NULL) OR (NEW.unit_price IS NULL AND OLD.unit_price IS NOT NULL));

INSERT INTO `ospos_items_price_history` (
    `item_id`,
    `previous_cost_price`,
    `cost_price`,
    `previous_unit_price`,
    `unit_price`,
    `source`,
    `changed_by`,
    `changed_at`
)
SELECT
    i.item_id,
    NULL,
    i.cost_price,
    NULL,
    i.unit_price,
    'baseline',
    NULL,
    COALESCE(i.last_modified, NOW())
FROM `ospos_items` i
WHERE i.deleted = 0
  AND NOT EXISTS (SELECT 1 FROM `ospos_items_price_history` h WHERE h.item_id = i.item_id);
