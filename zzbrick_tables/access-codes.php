<?php 

/**
 * downloads module
 * table script: access codes
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/downloads
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2017, 2019-2020, 2022-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Access Codes';
$zz['table'] = '/*_PREFIX_*/access_codes';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'code_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT event_id, event, identifier
	FROM events
	ORDER BY identifier';
$zz['fields'][2]['display_field'] = 'event';
$zz['fields'][2]['id_field_name'] = 'event_id';
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['if']['where']['hide_in_list'] = true;

$zz['fields'][3]['field_name'] = 'hash';
$zz['fields'][3]['type'] = 'identifier';
$zz['fields'][3]['fields'] = ['event_id', 'hash'];
$zz['fields'][3]['conf_identifier']['function'] = 'wrap_random_hash';
$zz['fields'][3]['conf_identifier']['function_parameter'][] = 8;
$zz['fields'][3]['conf_identifier']['function_parameter'][] = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';

$zz['fields'][4]['title_tab'] = 'A?';
$zz['fields'][4]['field_name'] = 'active';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['enum'] = ['yes', 'no'];
$zz['fields'][4]['default'] = 'no';

$zz['fields'][5]['title'] = 'Activation Date';
$zz['fields'][5]['field_name'] = 'activation_date';
$zz['fields'][5]['type'] = 'datetime';

$zz['fields'][6]['title'] = 'Activation IP';
$zz['fields'][6]['field_name'] = 'activation_ip';
$zz['fields'][6]['type'] = 'ip';

$zz['fields'][7]['title'] = 'Creation Date';
$zz['fields'][7]['field_name'] = 'creation_date';
$zz['fields'][7]['type'] = 'datetime';
$zz['fields'][7]['default'] = date('d.m.Y H:i:s');

$zz['sql'] = 'SELECT /*_PREFIX_*/access_codes.*
		, CONCAT(event, " ", IFNULL(event_year, YEAR(date_begin))) AS event
	FROM /*_PREFIX_*/access_codes
	LEFT JOIN /*_PREFIX_*/events
		ON /*_PREFIX_*/access_codes.event_id = /*_PREFIX_*/events.event_id';
$zz['sqlorder'] = ' ORDER BY /*_PREFIX_*/events.identifier, hash';

// @todo translate
$zz_conf['export'][] = 'PDF Gutscheine';


$zz['filter'][1]['title'] = wrap_text('Validity');
$zz['filter'][1]['identifier'] = 'validity';
$zz['filter'][1]['type'] = 'list';
$zz['filter'][1]['where_if'][1] = 'ISNULL(activation_date)';
$zz['filter'][1]['where_if'][2] = sprintf(
	'activation_date >= DATE_SUB(NOW(), INTERVAL %d hour)'
	, wrap_setting('downloads_access_codes_validity_in_hours')
);
$zz['filter'][1]['where_if'][3] = sprintf(
	'activation_date < DATE_SUB(NOW(), INTERVAL %d hour)'
	, wrap_setting('downloads_access_codes_validity_in_hours')
);
$zz['filter'][1]['selection'][1] = wrap_text('unused');
$zz['filter'][1]['selection'][2] = wrap_text('active');
$zz['filter'][1]['selection'][3] = wrap_text('expired');
