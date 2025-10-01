<?php

/**
 * downloads module
 * form for access codes for downloads
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/downloads
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2017, 2019-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz = zzform_include('access-codes');

$zz['where']['event_id'] = $brick['data']['event_id'];

$zz['page']['referer'] = '../';

$zz['explanation'] = wrap_template('access-codes-form');

if (!empty($_POST['no_of_codes'])) {
	$no_of_codes = intval($_POST['no_of_codes']);
	$line = [
		'event_id' => $brick['data']['event_id'],
		'active' => 'yes'
	];
	for ($i = 0; $i < $no_of_codes; $i++)
		zzform_insert('access-codes', $line)
}

$zz['subtitle']['event_id']['sql'] = 'SELECT event_id, event
	, CONCAT(events.date_begin, IFNULL(CONCAT("/", events.date_end), "")) AS duration
	FROM events';
$zz['subtitle']['event_id']['var'] = ['event', 'duration'];
$zz['subtitle']['event_id']['format'][1] = 'wrap_date';
$zz['subtitle']['event_id']['link'] = '../';
$zz['subtitle']['event_id']['link_no_append'] = true;
