<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$ModuloId = 1;
$aColumns = array(
	'PrenotazioneStato', 'RagioneSociale', 'DataIns', 'CodicePrenotazione', 'ClienteNome',
	'LineaNome', 'CorsaDataPartenza', 'CorsaOrarioPartenza', 'ComuneSalita', 'ComuneDiscesa',
	'TipoTour', 'TotalePostiPrenotati', 'TotalePrenotazione', 'TotaleResiduo',
	'PrenotazioneId', 'CorsaId'
);
$sIndexColumn = "PrenotazioneId";
$sTable = "(SELECT 
	rt_prenotazione.PrenotazioneId AS PrenotazioneId,
	rt_prenotazione.Multi AS Multi,
	rt_prenotazione.CodicePrenotazione AS CodicePrenotazione,
	rt_prenotazione.ClienteNome AS ClienteNome,
	rt_prenotazionepercorso.LineaNome AS LineaNome,
	DATE_FORMAT(rt_prenotazionepercorso.DataOraSalita, _utf8 '%d/%m/%Y') AS CorsaDataPartenza,
	DATE_FORMAT(rt_prenotazionepercorso.DataOraSalita, _utf8 '%H:%i') AS CorsaOrarioPartenza,
	rt_prenotazionepercorso.ComuneSalita AS ComuneSalita,
	rt_prenotazionepercorso.ComuneDiscesa AS ComuneDiscesa,
	(rt_prenotazione.TotalePaxPrenotati - rt_prenotazionepercorso.PasseggeriEsclusi) AS TotalePostiPrenotati,
	operatore.Username AS Username,
	gestore.RagioneSociale AS RagioneSociale,
	rt_prenotazione.OdcIdRef AS OdcIdRef,
	rt_prenotazione.GestoreIdRef AS GestoreIdRef,
	CONCAT(CONVERT(DATE_FORMAT(rt_prenotazione.DataIns, _utf8 '%d/%m/%Y %H:%i:%s') USING LATIN1), _latin1 ' da ', operatore.Username) AS DataIns,
	IF((rt_prenotazione.TipoTour = 1), _utf8 'Privato / Personalizzato', _utf8 'Gruppo') AS TipoTour,
	rt_prenotazionepercorso.CorsaId AS CorsaId,
	rt_prenotazione.PrenotazioneStato AS PrenotazioneStatoId,
	rt_appprenotazionestato.PrenotazioneStato AS PrenotazioneStato,
	rt_prenotazione.TotalePrenotazione AS TotalePrenotazione,
	rt_prenotazione.TotaleResiduo AS TotaleResiduo,
	rt_prenotazione.DataIns AS DataInsPrenotazione,
	rt_prenotazionepercorso.DataOraSalita as CorsaDataPartenzaPrenotazione 
	FROM RT_Prenotazione AS rt_prenotazione 
	JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazione.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId) 
	JOIN RT_PrenotazionePercorso AS rt_prenotazionepercorso ON (rt_prenotazione.PrenotazioneId = rt_prenotazionepercorso.PrenotazioneId) 
	JOIN Operatore AS operatore ON (rt_prenotazione.OpeIns = operatore.OperatoreId)
	JOIN Gestore AS gestore ON (rt_prenotazione.GestoreIdRef = gestore.GestoreId) 
	WHERE rt_prenotazione.Stato = 1 AND rt_prenotazione.Cancella = 0
	GROUP BY rt_prenotazione.PrenotazioneId, rt_prenotazionepercorso.PrenotazionePercorsoId 
	ORDER BY rt_prenotazione.PrenotazioneId DESC
) AS RT_PrenotazioneLista";

$OdcIdRef = $user->OdcId;
$OperatoreTipoId = $user->OperatoreTipoId;
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.Gestore.php");

$gestore = new Gestore();
$gestore->conn = $db;
$gestorefigli = $gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli = implode(",", $gestorefigli);
$dt = new DT();

/* Paging */
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
	$sLimit = "LIMIT " . $db->escape($_GET['iDisplayStart']) . ", " .
		$db->escape($_GET['iDisplayLength']);
}

/* Ordering */
$sOrder = "";
if (isset($_GET['iSortCol_0'])) {
	$sOrder = "ORDER BY  ";
	for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
		if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
			if ($aColumns[intval($_GET['iSortCol_' . $i])] == "DataIns") {
				$sOrder .= " DataInsPrenotazione " . $_GET['sSortDir_' . $i] . ", ";
			} elseif ($aColumns[intval($_GET['iSortCol_' . $i])] == "CorsaDataPartenza") {
				$sOrder .= " CorsaDataPartenzaPrenotazione " . $_GET['sSortDir_' . $i] . ", ";
			} else {
				$sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " "
					. $db->escape($_GET['sSortDir_' . $i]) . ", ";
			}
		}
	}
	$sOrder = substr_replace($sOrder, "", -2);
	if ($sOrder == "ORDER BY") {
		$sOrder = "";
	}
}

/* Filtering */
$sWhere = "";
if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
	$sWhere = "WHERE (";
	for ($i = 0; $i < count($aColumns); $i++) {
		$sWhere .= $aColumns[$i] . " LIKE '%" . $db->escape($_GET['sSearch']) . "%' OR ";
	}
	$sWhere = substr_replace($sWhere, "", -3);
	$sWhere .= ')';
}

/* Individual column filtering */
for ($i = 0; $i < count($aColumns); $i++) {
	$j = $i;
	if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
		if ($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= $aColumns[$j] . " LIKE '%" . $db->escape($_GET['sSearch_' . $i]) . "%' ";
	}
}

/* GestoreId filtering */
if (($user->GestoreId != 1) && ($user->GestoreId != 2)) {
	if ($sWhere == "")
		$sWhere = " where GestoreIdRef IN ($InGestoreFigli) ";
	else
		$sWhere .= " and GestoreIdRef IN ($InGestoreFigli) ";
}

/* SQL queries */
$sQuery = "
	SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
	FROM   $sTable
	$sWhere 
	$sOrder
	$sLimit
";
// echo($sQuery);

$rResult = $db->query($sQuery);

/* Data set length after filtering */
$sQuery = "SELECT FOUND_ROWS()";
$rResultFilterTotal = $db->query($sQuery);
$aResultFilterTotal = $db->fetch_array($rResultFilterTotal);
$iTotal = $iFilteredTotal = $aResultFilterTotal[0];

/* Output */
$output = array(
	"sEcho" => intval($_GET['sEcho']),
	"iTotalRecords" => $iTotal,
	"iTotalDisplayRecords" => $iFilteredTotal,
	"aaData" => array()
);

while ($aRow = $db->fetch($rResult)) {
	$row = array();

	for ($i = 0; $i < count($aColumns); $i++) {
		if ($aColumns[$i] == "PrenotazioneId") {
			$PrenotazioneId = $aRow[$aColumns[$i]];
			$CorsaId = $aRow['CorsaId'];
			$row[] = "<a href=\"#\" onclick=\"loadMainContent('rt_biglietto','biglietto.php?do=edit&amp;CorsaId=" . $CorsaId . "&PrenotazioneId=" . $PrenotazioneId . "',this);\" title=\"edita\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"edita\" title=\"edita\"></i></a>";
		} elseif ($aColumns[$i] == "Multi") {
			$Multi = $aRow[$aColumns[$i]];
			if ($Multi)
				$row[] = "<i class=\"fa fa-check-circle green\" aria-hidden=\"true\"></i>";
			else
				$row[] = "<i class=\"fa fa-times-circle red\" aria-hidden=\"true\">";
		} elseif ($aColumns[$i] == "TotalePrenotazione") {
			$row[] = number_format($aRow['TotalePrenotazione'], 2, ",", ".");
		} elseif ($aColumns[$i] == "TotaleResiduo") {
			$row[] = number_format($aRow['TotaleResiduo'], 2, ",", ".");
		} elseif ($aColumns[$i] == 'CorsaDataPartenza') {
			$sql = "SELECT RitornoAperto FROM RT_Corsa WHERE CorsaId = " . $aRow['CorsaId'];
			$rowTemp = $db->fetch_array($sql);
			if ($rowTemp[0]['RitornoAperto'] == 1) {
				$row[] = 'Open';
			} else {
				$row[] = $aRow[$aColumns[$i]];
			}
		} elseif ($aColumns[$i] == "ClienteNome") {
			// Gestione semplificata per ClienteNome
			$clienteNome = $aRow[$aColumns[$i]];
			$row[] = htmlentities($clienteNome, ENT_QUOTES, 'UTF-8');
			
		} elseif ($aColumns[$i] != '' && $aColumns[$i] != 'CorsaId') {
			// Gestione generale semplificata
			$value = $aRow[$aColumns[$i]];
			if (is_string($value)) {
				$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
			}
			$row[] = $value;
		}
	}
	$output['aaData'][] = array_decode_list($row);
}

// Imposta l'header Content-Type per UTF-8
header('Content-Type: application/json; charset=utf-8');

// Assicurati che il JSON sia codificato correttamente
echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>
