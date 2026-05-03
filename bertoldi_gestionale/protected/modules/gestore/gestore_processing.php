<?php
// Imposta il percorso base del documento e include un file principale
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
global $dizionario;

// Definizione di variabili per la configurazione della tabella e colonne
$ModuloId = 1;
$aColumns = array('Stato', 'RagioneSociale', 'PartitaIva', 'Indirizzo', 'Comune', 'Telefono', 'CodiceAzienda', 'GestoreId');
$sIndexColumn = "GestoreId";
$sTable = "ViewListaGestori";

// Recupera l'ID del gestore e carica la configurazione
$OdcIdRef = $user->OdcId;
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();

// Include la classe Gestore e ottiene i figli del gestore corrente
include_once($classespath_ . "class.Gestore.php");
$gestore = new Gestore();
$gestore->conn = $db;
$gestorefigli = $gestore->getGestoreFigli($user->GestoreId);
$InGestoreFigli = implode(",", $gestorefigli);

// Gestione della paginazione
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
	$sLimit = "LIMIT " . $db->escape($_GET['iDisplayStart']) . ", " .
		$db->escape($_GET['iDisplayLength']);
}

// Gestione dell'ordinamento
$sOrder = "";
if (isset($_GET['iSortCol_0'])) {
	$sOrder = "ORDER BY ";
	for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
		if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
			$sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " .
				$db->escape($_GET['sSortDir_' . $i]) . ", ";
		}
	}
	$sOrder = substr_replace($sOrder, "", -2);
	if ($sOrder == "ORDER BY") {
		$sOrder = "";
	}
} else {
	$sOrder = "ORDER BY RagioneSociale asc";
}

// Gestione del filtro globale
$sWhere = "";
if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
	$sWhere = "WHERE (";
	for ($i = 0; $i < count($aColumns); $i++) {
		$sWhere .= $aColumns[$i] . " LIKE '" . $db->escape($_GET['sSearch']) . "%' OR ";
	}
	$sWhere = substr_replace($sWhere, "", -3);
	$sWhere .= ')';
}

// Gestione del filtro per colonna
for ($i = 0; $i < count($aColumns); $i++) {
	$j = $i;
	if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
		if ($sWhere == "") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}
		$sWhere .= $aColumns[$j] . " LIKE '" . $db->escape($_GET['sSearch_' . $i]) . "%' ";
	}
}

// Aggiunge una condizione per filtrare i gestori figli
if ($sWhere == "") {
	$sWhere = " where GestoreId IN ($InGestoreFigli) ";
} else {
	$sWhere .= " and GestoreId IN ($InGestoreFigli) ";
}

// Costruisce la query SQL principale
$sQuery = "
	SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
	FROM $sTable
	$sWhere 
	$sOrder
	$sLimit
";
$rResult = $db->query($sQuery);

// Recupera il numero di record filtrati
$sQuery = "SELECT FOUND_ROWS()";
$rResultFilterTotal = $db->query($sQuery);
$aResultFilterTotal = $db->fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];

// Recupera il numero totale di record
$sQuery = "SELECT COUNT($sIndexColumn) FROM $sTable";
$rResultTotal = $db->query($sQuery);
$aResultTotal = $db->fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];

// Prepara l'output per il DataTable
$output = array(
	"sEcho" => intval($_GET['sEcho']),
	"iTotalRecords" => $iTotal,
	"iTotalDisplayRecords" => $iFilteredTotal,
	"aaData" => array()
);

// Popola i dati per ogni riga
while ($aRow = $db->fetch_array($rResult)) {
	$row = array();

	for ($i = 0; $i < count($aColumns); $i++) {
		if ($aColumns[$i] == "RagioneSociale") {
			$row[] = "<strong>" . $aRow[$aColumns[$i]] . "</strong>";
		} elseif ($aColumns[$i] == "Stato") {
			if ($aRow[$aColumns[$i]] == "1") {
				$row[] = "<i class=\"fa fa-check-circle green\" aria-hidden=\"true\"></i>";
			} else {
				$row[] = "<i class=\"fa fa-times-circle red\" aria-hidden=\"true\"></i>";
			}
		} elseif ($aColumns[$i] == "CodiceAzienda") {
			$row[] = "<a href=\"".Config::$urlAppCliente."?code=".$aRow[$aColumns[$i]]."\" target=\"_blank\" title=\"App Cliente Azienda\">".$aRow[$aColumns[$i]]."</a>";	
		} elseif ($aColumns[$i] == "GestoreId") {
			$AnagraficaIdPass = $aRow[$aColumns[$i]];
			$row[] = "<a href=\"#\" onclick=\"loadMainContent('gestore','gestore.php?do=edit&amp;GestoreId=" . $AnagraficaIdPass . "',this);\" title=\"edita\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"edita\" title=\"edita\"></i></a>";
		} elseif ($aColumns[$i] != '') {
			$row[] = $aRow[$aColumns[$i]];
		}
	}

	$output['aaData'][] = array_decode_list($row);
}

// Restituisce i dati in formato JSON
echo json_encode($output);
?>
