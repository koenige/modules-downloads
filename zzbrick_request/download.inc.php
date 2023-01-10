<?php 

/**
 * downloads module
 * download of files with valid access code
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/downloads
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2017-2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_downloads_download($vars, $settings, $event) {
	global $zz_setting;
	wrap_https_redirect();
	$zz_setting['cache'] = false;
	$data = [];
	$data['event'] = $event['event'];
	$data['year'] = $event['year'];
	$data['duration'] = $event['duration'];
	$data['download_until'] = sprintf('%d-12-31', substr($event['date_end'], 6) === 1
		? $data['year'] + 1 : $data['year']);

	if (count($vars) === 3)
		$data['access_forbidden'] = urldecode(array_pop($vars));
	if (count($vars) !== 2) return false;

	$sql = 'SELECT COUNT(*)
		FROM access_codes
		WHERE event_id = %d';
	$sql = sprintf($sql, $event['event_id']);
	$codes = wrap_db_fetch($sql);
	if (!$codes) return false;

	$sql = 'SELECT REPLACE(SUBSTRING_INDEX(path, "/", -1), "-", "_") AS path, eventtext
		FROM eventtexts
		LEFT JOIN categories
			ON eventtext_category_id = category_id
		WHERE event_id = %d
		AND published = "yes"';
	$sql = sprintf($sql, $event['event_id']);
	$data += wrap_db_fetch($sql, '_dummy_', 'key/value');

	if (empty($_SESSION['code_id'])) {
		session_save_path($zz_setting['session_save_path']);
		session_start();
	}
	
	if (!empty($_SESSION['code_id'])) {
		$data['show_files'] = true;
	} elseif (!empty($_POST) AND !empty($_POST['code'])) {
		$sql = 'SELECT code_id, activation_date
			FROM access_codes
			WHERE hash = "%s"
			AND event_id = %d
			AND active = "yes"
			AND (ISNULL(activation_date) OR activation_date >= DATE_SUB(NOW(), INTERVAL %d hour))';
		// @todo activation_date
		$sql = sprintf($sql
			, wrap_db_escape(trim($_POST['code']))
			, $event['event_id']
			, wrap_get_setting('downloads_access_codes_validity_in_hours')
		);
		$code = wrap_db_fetch($sql);
		if ($code) {
			if (empty($code['activation_date'])) {
				$values = [];
				$values['action'] = 'update';
				$values['POST']['code_id'] = $code['code_id'];
				$values['POST']['activation_date'] = date('Y-m-d H:i:s');
				$values['POST']['activation_ip'] = $_SERVER['REMOTE_ADDR'];
				$ops = zzform_multi('access-codes', $values);
			}
			$_SESSION['code_id'] = $code['code_id'];
			return wrap_redirect(dirname($zz_setting['request_uri']), 307, false);
		} else {
			$data['code'] = $_POST['code'];
			$data['fehler'] = true;
		}
	}
	if (!empty($data['show_files'])) {
		$dir = mod_downloads_download_folder($event);
		$files = scandir($dir);
		foreach ($files as $file) {
			if (substr($file, 0, 1) === '.') continue;
			$data['files'][] = [
				'filename' => urlencode($file),
				'title' => substr($file, 0, strrpos($file, '.')),
				'filesize' => filesize($dir.'/'.$file),
				'timestamp' => date('Y-m-d H:i', filemtime($dir.'/'.$file))
			];
		}
	}
	
	$page['title'] = 'Downloads <br><a href="../">'.$data['event'].' '.$data['year'].'</a>';
	if (!empty($data['access_forbidden'])) $page['url_ending'] = 'none';
	$page['breadcrumbs'][] = 'Downloads';
	$page['text'] = wrap_template('download', $data);
	return $page;
}

function mod_downloads_download_files($vars, $settings, $event) {
	wrap_https_redirect();

	if (count($vars) !== 3) return false;

	session_save_path(wrap_get_setting('session_save_path'));
	session_start();
	if (empty($_SESSION['code_id'])) {
		session_destroy();
		return mod_downloads_download($vars);
	}
	$dir = mod_downloads_download_folder($event);
	$file = [];
	$file['name'] = $dir.'/'.urldecode($vars[2]);
	session_write_close();
	if (!file_exists($file['name'])) return false;
	return wrap_file_send($file);
	exit;
}

function mod_downloads_download_folder($event) {
	return sprintf('%s/%s/%s'
		, wrap_get_setting('media_folder')
		, wrap_get_setting('downloads_data_folder')
		, $event['identifier']
	);
}