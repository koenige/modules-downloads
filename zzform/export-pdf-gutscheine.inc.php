<?php

/**
 * downloads module
 * export vouchers as PDF file
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/downloads
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2017-2019, 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * Export Download-Gutscheine
 *
 * @param array $ops
 */
function export_pdf_gutscheine($ops) {
	global $zz_setting;
	
	// get event_id
	foreach ($ops['output']['head'] as $index => $line) {
		if (empty($line['field_name'])) continue;
		if ($line['field_name'] !== 'event_id') continue;
		$event_id = $ops['output']['rows'][0][$index]['value'];
	}
	if (empty($event_id)) wrap_quit(404);

	$sql = 'SELECT event_id
			, date_end, IFNULL(event_year, YEAR(date_begin)) AS year
			, CONCAT(IFNULL(events.date_begin, ""), IFNULL(CONCAT("/", events.date_end), "")) AS duration
			, identifier
			, categories.category_short AS series_short
	    FROM events
	    LEFT JOIN categories
	    	ON events.series_category_id = categories.category_id
	    WHERE event_id = %d';
	$sql = sprintf($sql, $event_id);
	$event = wrap_db_fetch($sql);

	// Feld-IDs raussuchen
	$nos = export_pdf_gutscheine_nos($ops['output']['head']);

	require_once $zz_setting['modules_dir'].'/default/libraries/tfpdf.inc.php';

	$pdf = new TFPDF('P', 'pt', 'A4');		// panorama = p, DIN A4, 595 x 842
	$pdf->setCompression(true);
	// Fira Sans!
	$pdf->AddFont('FiraSans-Regular', '', 'FiraSans-Regular.ttf', true);
	$pdf->AddFont('FiraSans-SemiBold', '', 'FiraSans-SemiBold.ttf', true);
	$pdf->SetTextColor(0, 0, 0);
	$pdf->setFont('FiraSans-Regular', '', 12);
	$pdf->SetAutoPageBreak(false);

	// remove used or inactive vouchers
	foreach ($ops['output']['rows'] as $index => $line) {
		if ($line[$nos['active']]['text'] !== 'ja'
			OR $line[$nos['activation_date']]['text']) {
			unset($ops['output']['rows'][$index]);
			continue;
		}
	}
	$i = 1;
	$pdf->addPage();
	foreach ($ops['output']['rows'] as $line) {
		$top = ($i - 1) % 4;

		// Links: Bild
		$pdf->image($zz_setting['media_folder'].'/chessy/188-Chessy-konzentriert.600.png', 20, 20 + 8 + ($top * 210.5), 257, 138.5);

		// Rechts: Code
		$pdf->SetXY(317.5, 20 + ($top * 210.5) + 40);
		$pdf->Cell(257, 18, 'Code', 0, 2, 'L');
		$pdf->setFont('FiraSans-SemiBold', '', 36);
		$pdf->Cell(257, 48, $line[$nos['hash']]['text'], 0, 2, 'L');
		$pdf->setFont('FiraSans-Regular', '', 12);
		$pdf->Cell(257, 18, 'Einlösbar ab '.wrap_date($event['date_end']).' bis 31.12.'.$event['year'].' unter', 0, 2, 'L');
		$pdf->Cell(257, 18, 'http://www.dem'.$event['year'].'.de/download', 0, 2, 'L');
		$i++;
		
		$newpage = false;
		if ($i > count($ops['output']['rows'])) $newpage = true;
		if (!(($i - 1) % 4)) $newpage = true;
		if (!$newpage) continue;

		$pdf->addPage();
		for ($j = 0; $j < 4; $j++) {
			$pdf->setY($j * 210.5 + 20);
			$pdf->setFont('FiraSans-Regular', '', 12);
			$pdf->MultiCell(257, 16, 'Diese Karte enthält einen Code, mit dem du ab So '.wrap_date($event['date_end']).' 22 Uhr Photos, Videos, die Zeitung und die Partien der DEM '.$event['year'].' herunterladen kannst. Der Code ist ab der ersten Benutzung 48 Stunden gültig.', 0, 'L');
			$pdf->setFont('FiraSans-Regular', '', 9);
			$pdf->setXY($pdf->getX(), $pdf->getY() + 10 + 32); 
			$pdf->Cell(257 - 82, 12, 'Deutsche Schachjugend', 0, 2, 'L');
//			$pdf->Cell(257 - 82, 12, 'im Deutschen Schachbund e. V.', 0, 2, 'L');
			$pdf->Cell(257 - 82, 12, 'https://www.deutsche-schachjugend.de/', 0, 2, 'L');
			$pdf->image($zz_setting['media_folder'].'/urkunden-grafiken/DSJ-Logo.jpg', 317.5 - 20 - 82, ($j + 1) * 210.5 - 20 - 76 - 10, 82, 76);

			$pdf->image($zz_setting['media_folder'].'/chessy/195-Chessy-vor-Kronentor.600.png', 317.5, $j * 210.5 + 30, 132, 150.5);
			$pdf->setXY(317.5 + 10 + 149, $j * 210.5 + 20);
			$pdf->setFont('FiraSans-SemiBold', '', 28);
			$pdf->Cell(100, 24, '#DEM'.mb_substr($event['year'].'', 2, 2), 0, 2, 'L');
			$pdf->setFont('FiraSans-Regular', '', 12);
			$pdf->Cell(100, 24, str_replace(html_entity_decode('&#8239;'), '', wrap_date($event['duration'])), 0, 2, 'L');
			$pdf->setFont('FiraSans-SemiBold', '', 24);
			$pdf->MultiCell(100, 28, 'Photos Videos Zeitung Partien', 0, 'L');
		}
		if ($i > count($ops['output']['rows'])) continue;
		$pdf->addPage();
	}

	$folder = $zz_setting['tmp_dir'].'/gutscheine/'.$event['identifier'];
	wrap_mkdir($folder);
	if (file_exists($folder.'/gutscheine.pdf')) {
		unlink($folder.'/gutscheine.pdf');
	}
	$file['name'] = $folder.'/gutscheine.pdf';
	$file['send_as'] = $event['year'].' '.$event['series_short'].' Gutscheine.pdf';
	$file['etag_generate_md5'] = true;

	$pdf->output('F', $file['name'], true);
	wrap_file_send($file);
	exit;
}	

/**
 * Suche Feld-IDs aus Daten
 * IDs sind nicht vorherbestimmbar
 *
 * @param array $head = $ops['output']['head']
 * @return array $nos
 */
function export_pdf_gutscheine_nos($head) {
	$fields = [
		'event_id', 'hash', 'active', 'activation_date', 'activation_ip',
		'creation_date'
	];
	$nos = [];
	foreach ($head as $index => $field) {
		if (!in_array('field_name', array_keys($field))) continue;
		if (!in_array($field['field_name'], $fields)) continue;
		$nos[$field['field_name']] = $index;
	}
	return $nos;
}
