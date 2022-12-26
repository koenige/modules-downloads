<?php

/**
 * downloads module
 * form for access codes for downloads
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/downloads
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2017, 2019-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz = zzform_include_table('access-codes');

$zz['where']['event_id'] = $brick['data']['event_id'];

$zz_conf['referer'] = '../';

$zz['explanation'] = wrap_template('access-codes-form');

if (!empty($_POST['no_of_codes'])) {
	$no_of_codes = intval($_POST['no_of_codes']);
	for ($i = 0; $i < $no_of_codes; $i++) {
		$values = [];
		$values['action'] = 'insert';
		$values['POST']['event_id'] = $brick['data']['event_id'];
		$values['POST']['active'] = 'yes';
		$ops = zzform_multi('access-codes', $values);
	}
}

$zz['fields'][2]['key_field_name'] = 'event_id';

$zz['subtitle']['event_id']['sql'] = 'SELECT event
	, CONCAT(events.date_begin, IFNULL(CONCAT("/", events.date_end), "")) AS duration
	FROM events';
$zz['subtitle']['event_id']['var'] = ['event', 'duration'];
$zz['subtitle']['event_id']['format'][1] = 'wrap_date';
$zz['subtitle']['event_id']['link'] = '../';
$zz['subtitle']['event_id']['link_no_append'] = true;
