-- 3.4.2_cash_flow.sql
-- Módulo Caixa (sangria / suprimento) — tabela + seeds de módulo/permissão/grant

CREATE TABLE IF NOT EXISTS `ospos_cash_flow` (
    `cash_flow_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `employee_id` int(10) NOT NULL,
    `type` varchar(20) NOT NULL DEFAULT 'sangria',
    `amount` decimal(15,2) NOT NULL,
    `note` text DEFAULT NULL,
    `deleted` tinyint(1) NOT NULL DEFAULT '0',
    `created_at` datetime DEFAULT NULL,
    PRIMARY KEY (`cash_flow_id`),
    KEY `ospos_cash_flow_ibfk_1` (`employee_id`),
    CONSTRAINT `ospos_cash_flow_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `ospos_people` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `ospos_modules` (`name_lang_key`, `desc_lang_key`, `sort`, `module_id`)
VALUES ('module_cash', 'module_cash_desc', 111, 'cash');

INSERT IGNORE INTO `ospos_permissions` (`permission_id`, `module_id`, `location_id`)
VALUES ('cash', 'cash', NULL);

INSERT IGNORE INTO `ospos_grants` (`permission_id`, `person_id`, `menu_group`)
SELECT 'cash', `person_id`, 'home'
FROM `ospos_grants`
WHERE `permission_id` = 'expenses'
ON DUPLICATE KEY UPDATE `menu_group` = VALUES(`menu_group`);
