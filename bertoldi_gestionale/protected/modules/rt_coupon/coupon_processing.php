<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$ModuloId = 45;
$aColumns = array('Stato', 'CouponNome', 'Codice', 'Importo', 'MaxUtilizzi', 'Utilizzi', 'Valore', 'DaVendere', 'DataIns', 'CouponId');
$sIndexColumn = "CouponId";
$sTable = "RT_Coupon";

global $db;

$OdcIdRef = $user->OdcId;
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();

/* Usa la connessione centralizzata $db */

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
			$sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " "
				. $db->escape($_GET['sSortDir_' . $i]) . ", ";
		}
	}
	$sOrder = substr_replace($sOrder, "", -2);
	if ($sOrder == "ORDER BY") {
		$sOrder = "";
	}
}

// Se non è presente alcun ordinamento, usa DataIns DESC come default
if (empty($sOrder)) {
	$sOrder = "ORDER BY DataIns DESC";
}

/* Filtering */
$sWhere = "";
if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
	$sWhere = "WHERE (";
	for ($i = 0; $i < count($aColumns); $i++) {
		if ($aColumns[$i] == 'Importo' || $aColumns[$i] == 'Valore') {
			$sWhere .= '(' . $aColumns[$i] . " LIKE '%" . $db->escape($_GET['sSearch']) . "%' OR ";
			$sWhere .= "Percentuale LIKE '%" . $db->escape($_GET['sSearch']) . "%') OR ";
		} else {
			$sWhere .= $aColumns[$i] . " LIKE '%" . $db->escape($_GET['sSearch']) . "%' OR ";
		}
	}
	$sWhere = substr_replace($sWhere, "", -3);
	$sWhere .= ')';
}

// Filtro per singola colonna
for ($i = 0; $i < count($aColumns); $i++) {
	$j = $i;
	if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
		if ($sWhere == "" || $sWhere == "WHERE ") {
			$sWhere = "WHERE ";
		} else {
			$sWhere .= " AND ";
		}

		// Filtro custom per la colonna Stato (attivo/disattivo)
		if ($aColumns[$i] == 'Stato') {
			$val = strtolower(trim($_GET['sSearch_' . $i]));
			if ($val == 'attivo') {
				$sWhere .= "Stato = 1 ";
			} elseif ($val == 'disattivo') {
				$sWhere .= "Stato = 0 ";
			}
		}
		// Filtro custom per la colonna Descrizione (CouponNome)
		else if ($aColumns[$i] == 'CouponNome') {
			$val = strtolower(trim($_GET['sSearch_' . $i]));
			$sWhere .= "CouponNome like '%" . $db->escape($val) . "%' ";
		}
		// Filtro custom per la colonna Codice
		else if ($aColumns[$i] == 'Codice') {
			$val = strtolower(trim($_GET['sSearch_' . $i]));
			$sWhere .= "Codice like '%" . $db->escape($val) . "%' ";
		}
		// Filtro custom per la colonna DaVendere (si/no)
		else if ($aColumns[$i] == 'DaVendere') {
			$val = strtolower(trim($_GET['sSearch_' . $i]));
			if ($val == strtolower($dizionario['generale']['si'])) {
				$sWhere .= "DaVendere = 1 ";
			} elseif ($val == strtolower($dizionario['generale']['no'])) {
				$sWhere .= "DaVendere = 0 ";
			}
		}
		// Filtro standard per le altre colonne
		else if ($aColumns[$i] == 'Importo' || $aColumns[$i] == 'Valore') {
			$sWhere .= '(' . $aColumns[$j] . " LIKE '%" . $db->escape($_GET['sSearch_' . $i]) . "%' ";
			$sWhere .= "OR Percentuale LIKE '%" . $db->escape($_GET['sSearch_' . $i]) . "%') ";
		} else {
			$sWhere .= $aColumns[$j] . " LIKE '%" . $db->escape($_GET['sSearch_' . $i]) . "%' ";
		}
	}
}

// Aggiunta filtro obbligatorio per OdcIdRef
if ($sWhere == "")
	$sWhere = " where OdcIdRef=$OdcIdRef";
else
	$sWhere .= " and OdcIdRef=$OdcIdRef";

// Query principale per DataTables
$sQuery = "
	SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) . "
	FROM   $sTable
	$sWhere 
	$sOrder
	$sLimit
";

// Esecuzione query principale
$rResult = $db->query($sQuery);

// Query per il numero di record filtrati
$sQuery = "SELECT FOUND_ROWS()";
$rResultFilterTotal = $db->query($sQuery);
$aResultFilterTotal = $db->fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];

// Query per il numero totale di record
$sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM   $sTable";
$rResultTotal = $db->query($sQuery);
$aResultTotal = $db->fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];

/*
 * Output per DataTables
 */
$output = array(
	"sEcho" => intval($_GET['sEcho']),
	"iTotalRecords" => $iTotal,
	"iTotalDisplayRecords" => $iFilteredTotal,
	"aaData" => array()
);

// Elaborazione dei risultati della query
while ($aRow = $db->fetch_array($rResult)) {
	$row = array();

	for ($i = 0; $i < count($aColumns); $i++) {

		// Colonna Stato: mostra icona verde/rossa
		if ($aColumns[$i] == "Stato") {
			$Stato = $aRow[$aColumns[$i]];
			if ($Stato)
				$row[] = "<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"" . $dizionario['generale']['attiva'] . "\"></i>";
			else
				$row[] = "<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"" . $dizionario['generale']['disattiva'] . "\"></i>";
		}
		// Colonna Importo: mostra valore in euro o percentuale
		elseif ($aColumns[$i] == "Importo") {
			if ($aRow[$aColumns[$i]] > 0) {
				$row[] = $aRow[$aColumns[$i]] . "&euro;";
			} else {
				$sql = "Select * FROM RT_Coupon WHERE CouponId = " . $aRow[9];
				$temp = $db->query_first($sql);
				$row[] = $temp['Percentuale'] . "%";
			}
		}
		// Colonna Valore: mostra valore in euro o percentuale
		elseif ($aColumns[$i] == "Valore") {
			if ($aRow[$aColumns[$i]] > 0) {
				$row[] = $aRow[$aColumns[$i]] . "&euro;";
			} else {
				$sql = "Select * FROM RT_Coupon WHERE CouponId = " . $aRow[9];
				$temp = $db->query_first($sql);
				if ($temp['Percentuale'] == 0) {
					$row[] = $temp['Percentuale'] . "&euro;";
				} else {
					$row[] = $temp['Percentuale'] . "%";
				}
			}
		}
		// Colonna DataIns: mostra data e operatore
		elseif ($aColumns[$i] == "DataIns") {
			$sql = "Select c.DataIns, o.Username
					FROM RT_Coupon c
					LEFT JOIN Operatore o on o.OperatoreId = c.OpeIns 
					WHERE CouponId = " . $aRow[9];
			$temp = $db->query_first($sql);
			$dataIns = date('d/m/Y H:i', strtotime($temp['DataIns']));
			$row[] = $dataIns . " da " . $temp['Username'];
		}
		// Colonna DaVendere: mostra stato vendita e link azioni
		elseif ($aColumns[$i] == "DaVendere") {
			$CouponId = $aRow[9];
			if ($aRow[$aColumns[$i]] == 0) {
				$row[] = $dizionario['generale']['no'];
			} else {
				$sql = "Select DaVendere, VenditaStato, VenditaEmail, VenditaNotifica, VenditaNotificaData
						FROM RT_Coupon c
						WHERE CouponId = " . $CouponId;
				$temp = $db->query_first($sql);

				// Mostra la descrizione dello stato vendita
				if ($temp['VenditaStato'] == 1) {
					$statoVendita = $dizionario['coupon']['in_attesa_pagamento'];
				} else {
					$statoVendita = $dizionario['coupon']['coupon_emesso'];
				}

				$tempRow = $dizionario['generale']['si'] . "<br />" . $statoVendita . "<br />" . $temp['VenditaEmail'];

				// Mostra link per pagamento/invio email se in attesa pagamento
				if (isset($temp['DaVendere']) && $temp['DaVendere'] == 1 && isset($temp['VenditaStato']) && $temp['VenditaStato'] == 1) {
					$tempRow .= '<a class="brain_add" href="#"
						onclick="javascript:stripeLink(' . $CouponId . ');"
						title="' . $dizionario['biglietto']['pagamento_online_cliente'] . '"> ' . $dizionario['coupon']['link'] . '</a>';
					$tempRow .= '<a class="brain_add" href="#"
						onclick="javascript:stripeInvioLink(' . $CouponId . ');"
						title="' . $dizionario['biglietto']['pagamento_online_invia_cliente'] . '"></i> ' . $dizionario['coupon']['invia_email'] . '</a>';
				}
				// Mostra info pagamento se già pagato
				else if (isset($temp['DaVendere']) && $temp['DaVendere'] == 1 && isset($temp['VenditaStato']) && $temp['VenditaStato'] == 2) {
					$sql = "SELECT pt.* FROM RT_CouponTransazione pt WHERE pt.CouponId = " . $aRow[9];
					$tempTransazione = $db->query_first($sql);
					if ($tempTransazione) {
						$tempRow .= "Data Pagamento: " . date('d/m/Y H:i', strtotime($tempTransazione['DataIns'])) . "<br />";
					}
					if ($temp['VenditaNotifica'] == 1) {
						$tempRow .= "<br>Inviato: " . date('d/m/Y H:i', strtotime($temp['VenditaNotificaData']));
					} else {
						$tempRow .= "<br>Non Inviato";
					}
				}
				$row[] = $tempRow;
			}
		}
		// Colonna CouponId: mostra link per modifica
		elseif ($aColumns[$i] == "CouponId") {
			$CouponId = $aRow[$aColumns[$i]];
			$tempRow = "<a href=\"#\" onclick=\"javascript:ExternalLoad('rt_coupon','coupon.php?do=edit&amp;CouponId=" . $CouponId . "',this);\" title=\"edita\"><img alt=\"edita\" title=\"edita\" src=/images/edita_item.png /></a>";
			$row[] = $tempRow;
		}
		// Output generico per le altre colonne
		elseif ($aColumns[$i] != '') {
			$row[] = $aRow[$aColumns[$i]];
		}
	}

	// Decodifica eventuali caratteri speciali e aggiunge la riga all'output
	$output['aaData'][] = array_decode_list($row);
}

// Output JSON per DataTables
echo json_encode($output);
?>
