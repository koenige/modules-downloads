/**
 * downloads module
 * SQL for installation
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/downloads
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


CREATE TABLE `access_codes` (
  `code_id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned NOT NULL,
  `hash` varchar(8) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `active` enum('yes','no') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'no',
  `activation_date` timestamp NULL DEFAULT NULL,
  `activation_ip` varbinary(16) DEFAULT NULL,
  `creation_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`code_id`),
  KEY `date_id` (`event_id`),
  KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events', 'event_id', (SELECT DATABASE()), 'access_codes', 'code_id', 'event_id', 'no-delete');

INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`, `glossary`) VALUES ("Weitere Downloads", NULL, NULL, (SELECT category_id FROM categories c WHERE parameters LIKE "%&alias=event-texts%" OR path = "event-texts"), "event-texts/further-downloads", "&alias=further-downloads&module=downloads", NULL, NOW(), "no");
INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`, `glossary`) VALUES ("Downloads nicht verfügbar", NULL, NULL, (SELECT category_id FROM categories c WHERE parameters LIKE "%&alias=event-texts%" OR path = "event-texts"), "event-texts/downloads-unavailable", "&alias=downloads-unavailable&module=downloads", NULL, NOW(), "no");
INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`, `glossary`) VALUES ("Einführung Downloads", NULL, NULL, (SELECT category_id FROM categories c WHERE parameters LIKE "%&alias=event-texts%" OR path = "event-texts"), "event-texts/downloads-introduction", "&alias=downloads-introduction&module=downloads", NULL, NOW(), "no");
