<?PHP
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
ob_end_clean();

$db= new Database();
$db->connect();

$CorsaId=$_REQUEST['CorsaId'];
$CorsaIdA=$CorsaId;
$DataCorsa=$_REQUEST['DataCorsa'];
$Order=$_REQUEST['Order'];

$sql = "SELECT
	date_format(
		`RT_PrenotazionePercorso`.`CorsaDataPartenza`,
		'%d/%m/%Y'
	) AS `DataPartenza`,
	`RT_Corsa`.`CorsaNome` AS `Corsa`,
	concat(
		`RT_Prenotazione`.`ClienteNome`,
		_latin1 ' (',
		`RT_PrenotazioneDettaglio`.`Nome`,
		_latin1 ')'
	) AS `Cliente`,
ClienteCellulare as Cellulare,
	`RT_PrenotazionePercorso`.`ComuneSalita` AS `Partenza`,
`RT_PrenotazionePercorso`.`ComuneDiscesa` AS `Destinazione`,
DATE_FORMAT(`RT_PrenotazionePercorso`.`DataOraSalita`,'%H:%i') AS `Orario Salita`,
	DATE_FORMAT(`RT_PrenotazionePercorso`.`DataOraDiscesa`,'%H:%i') AS `Orario Discesa`,
RT_AppPrenotazioneStato.PrenotazioneStato as 'Stato Prenotazione'

FROM
	(
		(
			(
				(
					(
						(
							(
								(
									(
										(
											`RT_PrenotazionePercorso`
											JOIN `RT_Prenotazione` ON (
												(
													`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`
												)
											)
										)
										JOIN `RT_PrenotazioneDettaglio` ON (
											(
												(
													`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
												)
												AND (
													`RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
												)
												AND (
													`RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`
												)
											)
										)
									)
									JOIN `RT_AppPrenotazioneStato` ON (
										(
											`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`
										)
									)
								)
								LEFT JOIN `RT_PrenotazioneNumero` `p` ON (
									(
										`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`
									)
								)
							)
							LEFT JOIN `RT_TipologiaBiglietto` `tb` ON (
								(
									`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`
								)
							)
						)
						JOIN `RT_Fermata` ON (
							(
								`RT_PrenotazionePercorso`.`FermataSalitaId` = `RT_Fermata`.`FermataId`
							)
						)
					)
					JOIN `RT_Tratta` ON (
						(
							`RT_Fermata`.`TrattaId` = `RT_Tratta`.`TrattaId`
						)
					)
				)
				JOIN `RT_Corsa` ON (
					(
						`RT_PrenotazionePercorso`.`CorsaId` = `RT_Corsa`.`CorsaId`
					)
				)
			)
			JOIN `RT_TipologiaBus` ON (
				(
					`RT_Corsa`.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`
				)
			)
		)
		LEFT JOIN `RT_CorsaPaxTratta` ON (
			(
				(
					`RT_PrenotazionePercorso`.`CorsaDataPartenza` = `RT_CorsaPaxTratta`.`DataPartenza`
				)
				AND (
					`RT_PrenotazionePercorso`.`CorsaId` = `RT_CorsaPaxTratta`.`CorsaId`
				)
				AND (
					`RT_Tratta`.`TrattaId` = `RT_CorsaPaxTratta`.`TrattaId`
				)
			)
		)
	)
WHERE
	(
		(
			`RT_Prenotazione`.`Cancella` = 0
		)
		AND (
			`RT_PrenotazionePercorso`.`Cancella` = 0
		)
		AND (
			`RT_PrenotazionePercorso`.`Stato` = 1
		)
		AND (
			`RT_AppPrenotazioneStato`.`OccupaPosti` = 1
		)
		AND (
			`RT_PrenotazioneDettaglio`.`Escludi` <> 1
		)
		AND (
			`RT_PrenotazioneDettaglio`.`Rimborso` <> 1
		)
		AND (`tb`.`OccupaPosto` = 1)
		AND (
			`RT_PrenotazionePercorso`.`CorsaDataPartenza` = '$DataCorsa'
		)
		AND (
			`RT_PrenotazionePercorso`.`CorsaId` = $CorsaId
		)
	)
GROUP BY `p`.`PrenotazioneNumeroId`
ORDER BY
	`RT_PrenotazionePercorso`.`DataOraSalita`,
	`RT_PrenotazionePercorso`.`DataOraDiscesa`,
	`RT_PrenotazionePercorso`.`ComuneSalita`,
	`RT_PrenotazionePercorso`.`ComuneDiscesa`";

$data = $db->fetch_array($sql);

function cleanData(&$str)
{
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
}

// file name for download
$filename = "Corsa_" . $DataCorsa . "_".$CorsaId.".xls";

header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Type: application/vnd.ms-excel");

$flag = false;
foreach($data as $row) {
    
    if(!$flag) {
        // display field/column names as first row
        echo implode("\t", array_keys($row)) . "\n";
        $flag = true;
    }
    array_walk($row, 'cleanData');
    echo implode("\t", array_values($row)) . "\n";
}

exit;
?>