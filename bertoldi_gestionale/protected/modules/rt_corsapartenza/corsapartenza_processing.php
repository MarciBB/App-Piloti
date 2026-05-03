<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

global $db, $dizionario;

$ModuloId = 1;
$aColumns = array(
    'CorsaWebBloccata', 'CorsaBloccata', 'CorsaNome', 'LineaNome', 'DataPartenzaFormattata',
    'OrarioPartenza', 'PostiTotali', 'TotalePrenotati', 'PostiRealmentePrenotati',
    'ServiziPrenotati', 'AppCalendarioData', 'CorsaId'
);
$sIndexColumn = "CorsaId";
$sTable = "RT_ViewElencoGestioneOperativita_new";

$OdcIdRef = $user->OdcId;
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();

$sql1 = "select 
    c.`CorsaId` AS `CorsaId`,
    appcal.`AppCalendarioData` AS `AppCalendarioData`,
    date_format(appcal.`AppCalendarioData`, _utf8'%d/%m/%Y') AS `DataPartenzaFormattata`,
    c.`CorsaNome` AS `CorsaNome`,
    c.`LineaId` AS `LineaId`,
    `RT_Linea`.`LineaNome` AS `LineaNome`,
    c.`OrarioPartenza` AS `OrarioPartenza`,
    IFNULL(cpax.`PostiAggiunti`,0) AS `PostiAggiunti`,
    IFNULL(agg.`TotalePrenotati`,0) AS `TotalePrenotati`,
    IFNULL(agg.`TotaleRealmentePrenotati`,0) AS `PostiRealmentePrenotati`,
    IFNULL(serv.`ServiziPrenotati`,0) AS `ServiziPrenotati`,
    (`RT_TipologiaBus`.`TotalePosti` + IFNULL(cpax.`PostiAggiunti`,0)) AS `PostiTotali`,
    (`RT_TipologiaBus`.`TotalePosti` + IFNULL(cpax.`PostiAggiunti`,0) - IFNULL(agg.`TotaleRealmentePrenotati`,0)) AS `PostiRealmenteDisponibili`,
    if(isnull(`RT_CorsaBloccoWeb`.`CorsaBloccoId`), 0, 1) AS `CorsaWebBloccata`,
    if(isnull(`RT_CorsaBlocco`.`CorsaBloccoId`), 0, 1) AS `CorsaBloccata`,
    `RT_CorsaConsolidamento`.`DataIns` AS `DataConsolidamentoF`,
    `RT_CorsaInizioPreparazione`.`DataIns` AS `DataInizializzazioneF`,
    c.`OdcIdRef` AS `OdcIdRef`,
    c.`GestoreIdRef` AS `GestoreIdRef`,
    if(isnull(`RT_MaxDisponibilitaPostiCron`.`Posti`), 0, `RT_MaxDisponibilitaPostiCron`.`Posti`) AS `MaxPostiOccupati`
from
    `RT_Corsa` c
    join `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
    join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
    join `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
    join `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
    join `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
    left join `RT_CorsaBloccoWeb` ON (c.`CorsaId` = `RT_CorsaBloccoWeb`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaBloccoWeb`.`DataPartenza`)
    left join `RT_CorsaBlocco` ON (c.`CorsaId` = `RT_CorsaBlocco`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaBlocco`.`DataPartenza`)
    left join `RT_CorsaInizioPreparazione` ON (c.`CorsaId` = `RT_CorsaInizioPreparazione`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaInizioPreparazione`.`DataCorsa`)
    left join `RT_CorsaConsolidamento` ON (c.`CorsaId` = `RT_CorsaConsolidamento`.`CorsaId` and appcal.`AppCalendarioData` = `RT_CorsaConsolidamento`.`DataCorsa`)
    left join `RT_MaxDisponibilitaPostiCron` ON (appcal.`AppCalendarioData` = `RT_MaxDisponibilitaPostiCron`.`DataPartenza` and c.`CorsaId` = `RT_MaxDisponibilitaPostiCron`.`CorsaId`)
    /* Posti aggiunti per corsa/data */
    left join (
        select CorsaId, DataPartenza, SUM(NumeroPax) as PostiAggiunti
        from RT_CorsaPax
        where Cancella = 0 and OdcIdRef = 1
        group by CorsaId, DataPartenza
    ) cpax on (cpax.CorsaId = c.CorsaId and cpax.DataPartenza = appcal.`AppCalendarioData`)
    /* Aggregati prenotazioni: TotalePrenotati e TotaleRealmentePrenotati in un solo passaggio */
    left join (
        select 
            pper.CorsaId as CorsaId,
            pper.CorsaDataPartenza as CorsaDataPartenza,
            SUM(CASE 
                WHEN tb.OccupaPosto = 1 
                     AND p.PrenotazioneStato IN (1,2,3) 
                     AND pd.Escludi <> 1 
                     AND pd.Rimborso <> 1 
                THEN 1 ELSE 0 END
            ) as TotalePrenotati,
            SUM(CASE 
                WHEN tb.OccupaPosto = 1 
                     AND pd.Escludi <> 1 
                     AND pd.Rimborso <> 1 
                THEN 1 ELSE 0 END
            ) as TotaleRealmentePrenotati
        from RT_PrenotazionePercorso pper
        join RT_Prenotazione p on (pper.PrenotazioneId = p.PrenotazioneId)
        join RT_PrenotazioneDettaglio pd on (pd.PrenotazioneId = p.PrenotazioneId and pd.ComunePartenza = pper.ComuneSalita)
        join RT_AppPrenotazioneStato aps on (pper.PrenotazioneStato = aps.PrenotazioneStatoId)
        left join RT_PrenotazioneNumero pn on (pd.PrenotazioneNumero = pn.PrenotazioneNumeroId)
        left join RT_TipologiaBiglietto tb on (tb.TipologiaBigliettoId = pn.TipologiaBigliettoId)
        where (p.Cancella = 0 and pper.Cancella = 0 and pper.Stato = 1 and aps.OccupaPosti = 1 and p.OdcIdRef = $OdcIdRef)
        group by pper.CorsaDataPartenza, pper.CorsaId
    ) agg on (agg.CorsaId = c.CorsaId and agg.CorsaDataPartenza = appcal.`AppCalendarioData`)
    /* Servizi prenotati (non occupano posto; varie condizioni sullo stato) */
    left join (
        select 
            pper.CorsaId as CorsaId,
            pper.CorsaDataPartenza as CorsaDataPartenza,
            (count(p.PrenotazioneId) - sum(pd.Escludi)) as ServiziPrenotati
        from RT_PrenotazionePercorso pper
        join RT_Prenotazione p on (pper.PrenotazioneId = p.PrenotazioneId)
        join RT_PrenotazioneDettaglio pd on (pd.PrenotazioneId = p.PrenotazioneId and pd.ComunePartenza = pper.ComuneSalita)
        join RT_AppPrenotazioneStato aps on (pper.PrenotazioneStato = aps.PrenotazioneStatoId)
        left join RT_PrenotazioneNumero pn on (pd.PrenotazioneNumero = pn.PrenotazioneNumeroId)
        left join RT_TipologiaBiglietto tb on (tb.TipologiaBigliettoId = pn.TipologiaBigliettoId)
        where (p.Cancella = 0 and pper.Cancella = 0 and pper.Stato = 1 and aps.OccupaPosti = 1 and tb.OccupaPosto = 0 and p.OdcIdRef = $OdcIdRef)
          and (((p.PrenotazioneStato = 3) and (pd.Escludi = 0))
               or ((p.PrenotazioneStato = 1) and (p.Pagato = 1))
               or ((p.PrenotazioneStato = 1) and (p.Pagato = 0))
               or ((p.ABordo = 1) and (p.PrenotazioneStato = 1)))
        group by pper.CorsaDataPartenza, pper.CorsaId
    ) serv on (serv.CorsaId = c.CorsaId and serv.CorsaDataPartenza = appcal.`AppCalendarioData`)
";

$sql2 = "select 
    count(*) as tot
from
    `RT_Corsa` c
    join `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
    join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
    join `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
    join `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
";

// Paging
$sLimit = "";
if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
    $sLimit = "LIMIT " . $db->escape($_GET['iDisplayStart']) . ", " .
        $db->escape($_GET['iDisplayLength']);
}

// Ordering
$sOrder = "order by appcal.`AppCalendarioData` , `RT_Linea`.`PercorsoId` , `RT_Linea`.`LineaNome` , c.`CorsaNome` ";
if (isset($_GET['iSortCol_0'])) {
    $sOrder = "$sOrder , ";
    for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
            $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " " .
                $db->escape($_GET['sSortDir_' . $i]) . ", ";
        }
    }
    $sOrder = substr_replace($sOrder, "", -2);
}

// Filtering
$sWhere = "where
    ((c.`Cancella` = 0 AND c.`Stato` = 1)
        and (appcal.`AppCalendarioData` >= c.`AttivaDal`)
        and (appcal.`AppCalendarioData` <= c.`AttivaAl`)
        and ((appcal.`Feriale` = c.`IncludiFeriale` AND c.`IncludiFeriale` = 1)
            or (appcal.`Prefestivo` = c.`IncludiPrefestivo` AND c.`IncludiPrefestivo` = 1)
            or (appcal.`Festivo` = c.`IncludiFestivo`  AND c.`IncludiFestivo` = 1)))";
$filtro = 0;

$sWhere2 = $sWhere;

if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
    $sWhere = "$sWhere AND (";
    for ($i = 0; $i < count($aColumns); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '" . $db->escape($_GET['sSearch']) . "%' OR ";
    }
    $sWhere = substr_replace($sWhere, "", -3);
    $sWhere .= ')';
}

// Individual column filtering
for ($i = 0; $i < count($aColumns); $i++) {
    $j = $i-2;
    
    if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == "") {
            $sWhere = "WHERE ";
        } else {
            $sWhere .= " AND ";
        }
        $filtro = 1;

        if ($aColumns[$j] == 'DataPartenzaFormattata') {
            $sWhere .= "date_format(appcal.`AppCalendarioData`, _utf8'%d/%m/%Y') LIKE '" . $db->escape($_GET['sSearch_' . $i]) . "%' ";
        }
        if ($aColumns[$j] == 'CorsaNome') {
            $sWhere .= "c.`CorsaNome` LIKE '%" . $db->escape($_GET['sSearch_' . $i]) . "%' ";
        }
        if ($aColumns[$j] == 'LineaNome') {
            $sWhere .= "`RT_Linea`.`LineaNome` LIKE '%" . $db->escape($_GET['sSearch_' . $i]) . "%' ";
        }
        if ($aColumns[$j] == 'OrarioPartenza') {
            $sWhere .= "c.`OrarioPartenza` LIKE '%" . $db->escape($_GET['sSearch_' . $i]) . "%' ";
        }
    }
}

// SQL queries
if ($sWhere == "")
    $sWhere = " where c.`OdcIdRef`=$OdcIdRef";
else
    $sWhere .= " and c.`OdcIdRef`=$OdcIdRef";

if ($filtro == 0) {
    $oggi = Date('Y-m-d');
    $sWhere .= " and appcal.`AppCalendarioData`>='$oggi'";
}

$sql1 = "$sql1 
    $sWhere 
    group by c.`CorsaId` , appcal.`AppCalendarioData` 
    $sOrder 
    $sLimit";

$sql2 = "$sql2 
    $sWhere ";

// $rResult = mysql_query( $sQuery, $gaSql['link'] ) or die(mysql_error());
$rResult = $db->fetch_array($sql1);
$tot = $db->query_first($sql2);

$rResultTotal = count($rResult);
$aResultTotal = count($rResult);
$iTotal = $aResultTotal[0];

// Output
$output = array(
    "sEcho" => intval($_GET['sEcho']),
    "iTotalRecords" => $tot['tot'],
    "iTotalDisplayRecords" => $tot['tot'],
    "aaData" => array()
);

foreach ($rResult as $aRow) {
    $row = array();

    // Prima colonna: checkbox selezione massa
    $CorsaId = $aRow['CorsaId'];
    $DataPartenza = $aRow['AppCalendarioData'];
    $row[] = '<input type="checkbox" class="select-corsa" value="' . $CorsaId . '" data-partenza="' . $DataPartenza . '">';

    for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == "CorsaWebBloccata") {
            $StatoWeb = $aRow[$aColumns[$i]];
            if ($StatoWeb == 0)
                $row[] = "<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"" . $dizionario['generale']['attiva'] . "\"></i>";
            else
                $row[] = "<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"" . $dizionario['generale']['disattiva'] . "\"></i>";
        } elseif ($aColumns[$i] == "CorsaBloccata") {
            $Stato = $aRow[$aColumns[$i]];
            if ($Stato == 0)
                $row[] = "<i class=\"fa fa-check-circle green\" aria-hidden=\"true\" title=\"" . $dizionario['generale']['attiva'] . "\"></i>";
            else
                $row[] = "<i class=\"fa fa-times-circle red\" aria-hidden=\"true\" title=\"" . $dizionario['generale']['disattiva'] . "\"></i>";
        } elseif ($aColumns[$i] == "CorsaId") {
            $CorsaId = $aRow[$aColumns[$i]];
            $CorsaNome = $aRow['CorsaNome'];
            $CorsaNome = str_replace("'", "\\'", $CorsaNome);
            $DataPartenza = $aRow['AppCalendarioData'];
            $OrarioPartenza = $aRow['OrarioPartenza'];
            $Stato = $aRow['CorsaBloccata'];

            $blocco = "Bloccare";
            if ($Stato == 1)
                $blocco = "Sbloccare";

            $bloccoweb = "Bloccare";
            if ($StatoWeb == 1)
                $bloccoweb = "Sbloccare";

            // Azione: GestionePax
            $row[] = ("<a href=\"#\" onclick=\"ExternalLoad('rt_corsapartenza','corsapartenza.php?do=GestionePax&amp;DataPartenza=$DataPartenza&amp;CorsaId=" . $CorsaId . "',this);\" title=\"edita\"><i class=\"fa fa-plus-circle blue\" aria-hidden=\"true\" alt=\"aggiungi pax\" title=\"aggiungi pax\"></i></a>");
            // Nuova azione: Trasferisci (colonna trasferisci)
            $row[] = ("<a href=\"#\" onclick=\"ExternalLoad('rt_corsapartenza','corsapartenza.php?do=TrasferisciCorsa&amp;DataPartenza=$DataPartenza&amp;CorsaId=" . $CorsaId . "',this);\" title=\"trasferisci\"><i class=\"fa fa-exchange green\" aria-hidden=\"true\" alt=\"trasferisci\" title=\"trasferisci\"></i></a>");
            // Azione: CambiaCorsa (colonna cambia)
            $row[] = ("<a href=\"#\" onclick=\"ExternalLoad('rt_corsapartenza','corsapartenza.php?do=CambiaCorsa&amp;DataPartenza=$DataPartenza&amp;CorsaId=" . $CorsaId . "',this);\" title=\"edita\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"blocca\" title=\"cambia giorno\"></i></a>");
            $row[] = ("<a href=\"#\" onclick=\"javascript:BloccaSbloccaCorsa($Stato,$CorsaId,'$CorsaNome','$DataPartenza','$OrarioPartenza','$blocco');\" title=\"edita\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"blocca\" title=\"blocca - sblocca\"></i></a>");
            $row[] = ("<a href=\"#\" onclick=\"javascript:BloccaSbloccaCorsaWeb($StatoWeb,$CorsaId,'$CorsaNome','$DataPartenza','$OrarioPartenza','$bloccoweb');\" title=\"edita\"><i class=\"fa fa-refresh green\" aria-hidden=\"true\" alt=\"blocca\" title=\"blocca - sblocca\"></i></a>");
            $row[] = ("<a href=\"#\" onclick=\"loadMainContent('rt_previaggio','previaggio.php?do=GestionePreViagigo&amp;DataPartenza=$DataPartenza&amp;CorsaId=" . $CorsaId . "',this);\" title=\"edita\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"pre viaggio\" title=\"pre viaggio\"></i></a>");
        } elseif ($aColumns[$i] == 'PostiTotali') {
            $PostiPrenotati = $aRow['PostiTotali'];
            $AppCalendarioData = $aRow['AppCalendarioData'];
            $CorsaId = $aRow['CorsaId'];
            $row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','prenotati_tratta_corsa.php?do=show_list&amp;CorsaId=" . $CorsaId . "&DataCorsa=" . $AppCalendarioData . "');\" title=\"tratte della corsa\"><i class='fa fa-search edita' aria-hidden='true' alt='Dettaglio posti'></i></a>";
        } elseif ($aColumns[$i] == 'TotalePrenotati') {
            $PostiPrenotati = $aRow['TotalePrenotati'];
            $AppCalendarioData = $aRow['AppCalendarioData'];
            $CorsaId = $aRow['CorsaId'];
            if ($PostiPrenotati > 0)
                $row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','prenotati_corsa.php?do=show_list&amp;CorsaId=" . $CorsaId . "&DataCorsa=" . $AppCalendarioData . "');\" title=\"elenco prenotati\">" . $PostiPrenotati . "</a>";
            else
                $row[] = $PostiPrenotati;
        } elseif ($aColumns[$i] == 'ServiziPrenotati') {
            $ServiziPrenotati = $aRow['ServiziPrenotati'];
            $AppCalendarioData = $aRow['AppCalendarioData'];
            $CorsaId = $aRow['CorsaId'];
            if ($ServiziPrenotati > 0)
                $row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','servizi_corsa.php?do=show_list&amp;CorsaId=" . $CorsaId . "&DataCorsa=" . $AppCalendarioData . "');\" title=\"elenco servizi\">" . $ServiziPrenotati . "</a>";
            else
                $row[] = $ServiziPrenotati;
        } elseif ($aColumns[$i] == 'LineaId') {
            $PostiDisponibili = $aRow['PostiTotali'] - $aRow['TotalePrenotati'];
            $row[] = $PostiDisponibili;
        } elseif ($aColumns[$i] == 'CorsaNome') {
            $CorsaNome = $aRow['CorsaNome'];
            $AppCalendarioData = $aRow['AppCalendarioData'];
            $CorsaId = $aRow['CorsaId'];

            //recupero la corsa
            $sql = "SELECT * FROM RT_Corsa WHERE CorsaId = ".$CorsaId." AND Stato = 1 AND Cancella = 0";
            $corsaTemp = $db->query_first($sql);

            //verifico se esiste una corsa con lo stesso orario di partenza e arrivo
            $sql = "select pp.* from RT_PrenotazionePercorso pp
                        left join RT_Corsa c on c.CorsaId = pp.CorsaId
                        where pp.CorsaDataPartenza = '".$AppCalendarioData."' and c.FlottaDefaultId = ".$corsaTemp['FlottaDefaultId']."
                        and pp.CorsaOrarioPartenza >= '".$corsaTemp['OrarioPartenza']."' and pp.CorsaOrarioPartenza < '".$corsaTemp['OrarioArrivo']."'
                        and pp.PrenotazioneStato NOT IN (6,4) and pp.CorsaId <> '".$corsaTemp['CorsaId']."'";
            $checkFlotta = $db->query_first($sql);
            if(isset($checkFlotta['PrenotazionePercorsoId']) && $checkFlotta['PrenotazionePercorsoId'] > 0) {
                $tempRow = "<span style=\"color:red;\" title=\"Tour in conflitto con {$checkFlotta['LineaNome']} - {$checkFlotta['CorsaNome']}\"><i class='fa fa-exclamation-triangle'></i> $CorsaNome</span>";
            } else {
                $tempRow = $CorsaNome;
            }

            $row[] = $tempRow;
        } elseif ($aColumns[$i] == 'PostiRealmentePrenotati') {
            $PostiRealmentePrenotati = $aRow['PostiRealmentePrenotati'];
            if ($PostiRealmentePrenotati > 0)
                $row[] = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','realmente_prenotati_corsa.php?do=show_list&amp;CorsaId=" . $CorsaId . "&DataCorsa=" . $AppCalendarioData . "');\" title=\"elenco realmente prenotati\">" . $PostiRealmentePrenotati . "</a>";
            else
                $row[] = $PostiRealmentePrenotati;
        } elseif (($aColumns[$i] != '') and ($aColumns[$i] != 'AppCalendarioData')) {
            $row[] = ($aRow[$aColumns[$i]]);
        }
    }
    $output['aaData'][] = array_decode_list($row);
}
echo json_encode($output);
?>
