<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");

$aColumns = array('Data', 'Nome', 'NumeroRighe', 'YoloId');
$sIndexColumn = "YoloId";
$sTable = "RT_Yolo";

// Query principale con conteggio righe associate
$sql = "SELECT 
    RT_Yolo.YoloId, 
    RT_Yolo.Data, 
    RT_Yolo.Nome, 
    (SELECT COUNT(*) FROM RT_YoloRiga WHERE RT_YoloRiga.YoloId = RT_Yolo.YoloId) AS NumeroRighe
    FROM RT_Yolo
";

// Usa la connessione centralizzata $db (Database)

// Paging
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = "LIMIT " . $db->escape($_GET['iDisplayStart']) . ", " .
        $db->escape($_GET['iDisplayLength']);
}

// Ordering
$sOrder = " ORDER BY Data DESC ";
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

// Filtering solo sulla colonna Data (Da/A)
$sWhere = "WHERE RT_Yolo.Tipo = 'I'";
if (isset($_GET['sSearch_0']) && $_GET['sSearch_0'] != '') {
    $sWhere .= " AND RT_Yolo.Data >= '" . $db->escape($_GET['sSearch_0']) . "'";
}
if (isset($_GET['sSearch_1']) && $_GET['sSearch_1'] != '') {
    $sWhere .= " AND RT_Yolo.Data <= '" . $db->escape($_GET['sSearch_1']) . "'";
}

// Query finale
$sQuery = "$sql $sWhere $sOrder $sLimit";

// Esecuzione query
$rResult = $db->query($sQuery);

// Conteggio filtrato
$sQuery = "SELECT FOUND_ROWS()";
$rResultFilterTotal = $db->query($sQuery);
$aResultFilterTotal = $db->fetch_array($rResultFilterTotal);
$iFilteredTotal = $aResultFilterTotal[0];

// Conteggio totale
$sQuery = "SELECT COUNT(*) as tot FROM RT_Yolo";
$rResultTotal = $db->query($sQuery);
$aResultTotal = $db->fetch_array($rResultTotal);
$iTotal = $aResultTotal[0];

// Output
$output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $iTotal,
    "iTotalDisplayRecords" => $iTotal,
    "aaData" => array()
);

while ($aRow = $db->fetch_array($rResult)) {
    $row = array();
    // Data (formattata)
    $dataString = $aRow['Data'];
    if ($dataString == '0000-00-00' || empty($dataString)) {
        $row[] = 'N.D.';
    } else {
        $date = new DateTime($dataString);
        $row[] = $date->format('d/m/Y');
    }
    // Nome file
    $row[] = $aRow['Data'];
    $row[] = $aRow['Nome'];
    // Numero righe associate
    $row[] = $aRow['NumeroRighe'];

    // Esito
    $nomeInput = $aRow['Nome'];
    $nomeOutput = 'BB_OUT_' . $nomeInput;
    $esito = $dizionario['yolo']['da_elaborare']; // Default esito "Da elaborare"

    // Cerca file di output (Tipo = 'O') con lo stesso nome
    $sqlOut = "SELECT YoloId FROM RT_Yolo WHERE Tipo = 'O' AND Nome = '" . $db->escape($nomeOutput) . "'";
    $resOut = $db->query($sqlOut);
    if ($resOut && ($rowOut = $db->fetch($resOut))) {
        // Se esiste un file di output associato (Tipo = 'O')
        $yoloOutId = $rowOut['YoloId'];
        // Prendi la prima riga del file di output
        $sqlRigaOut = "SELECT Record FROM RT_YoloRigaOut WHERE YoloId = " . intval($yoloOutId) . " ORDER BY YoloRigaOutId ASC LIMIT 1";
        $resRigaOut = $db->query($sqlRigaOut);
        if ($resRigaOut && ($rigaOut = $db->fetch($resRigaOut))) {
            // Se la prima riga è "000TUTTO OK" (anche con zeri multipli), esito OK
            if (trim($rigaOut['Record']) == '000TUTTO OK') {
                $esito = $dizionario['yolo']['ok'];
            } else {
                // Altrimenti, esito Errore
                $esito = $dizionario['yolo']['errore'];
            }
        } else {
            // Se non ci sono righe nel file di output, esito Errore
            $esito = $dizionario['yolo']['errore'];
        }
    }
    // Se non trova nessun file di output, $esito resta "Da elaborare"
    $row[] = $esito;

    // Azioni (modifica/elimina)
    $YoloId = $aRow['YoloId'];
    $temp = "<a href=\"#\" onclick=\"loadMainContent('rt_yolo','yolo.php?do=show&amp;YoloId=" . $YoloId . "',this);\" title=\"mostra\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"mostra\" title=\"mostra\"></i></a>";
    $row[] = $temp;

    $output['aaData'][] = $row;
}

header('Content-Type: application/json');
echo json_encode($output);
?>
