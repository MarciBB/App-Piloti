<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<?php 
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load(); 
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "as_reportool/as_reportool.php");

$ModuloId = 8;

function show_list()
{
    global $user, $HtmlCommon, $dizionario;
    $db = new Database();
    $db->connect();

    $gestore = new Gestore();
    $gestore->conn = $db;
    $ges = $user->GestoreId;
    if (($user->GestoreId == 1) or ($user->GestoreId == 2)) {
        $ges = 1;
    }
    $gestorefigli = $gestore->getGestoreFigli($ges);
    $InGestoreFigli = implode(",", $gestorefigli);
?>
<div>
<?php
$post_linea_id = $_POST['LineaId'];
$post_corsa_id = $_POST['CorsaId'];
$post_dal = $_POST['Dal'];
$post_al = $_POST['Al'];
$ComuneSalitaId = $_POST['ComuneSalitaId'];
$ComuneDiscesaId = $_POST['ComuneDiscesaId'];
$durata_min = isset($_POST['durata_min']) ? $_POST['durata_min'] : '';
$durata_max = isset($_POST['durata_max']) ? $_POST['durata_max'] : '';

$dt = new DT();
$post_dal_format = $dt->format($post_dal, "d/m/Y", "Y-m-d");
$post_al_format = $dt->format($post_al, "d/m/Y", "Y-m-d");

$swhere = "";
if (isset($post_linea_id) && $post_linea_id > 0) {
    $swhere .= " AND rt_prenotazionepercorso.LineaNome = '" . addslashes($post_linea_id) . "' ";
}
if (isset($post_corsa_id) && $post_corsa_id > 0) {
    $swhere .= " AND rt_prenotazionepercorso.CorsaId = " . intval($post_corsa_id) . " ";
}
if (!empty($post_dal_format)) {
    $swhere .= " AND DATE(rt_prenotazionepercorso.DataOraSalita) >= '" . addslashes($post_dal_format) . "' ";
}
if (!empty($post_al_format)) {
    $swhere .= " AND DATE(rt_prenotazionepercorso.DataOraSalita) <= '" . addslashes($post_al_format) . "' ";
}
if (!empty($ComuneSalitaId)) {
    $swhere .= " AND rt_prenotazionepercorso.ComuneSalita = '" . addslashes($ComuneSalitaId) . "' ";
}
if (!empty($ComuneDiscesaId)) {
    $swhere .= " AND rt_prenotazionepercorso.ComuneDiscesa = '" . addslashes($ComuneDiscesaId) . "' ";
}
if ($durata_min !== '' || $durata_max !== '') {
    // Calcola la durata in ore tra DataOraSalita e DataOraDiscesa
    $having = [];
    if ($durata_min !== '') {
        $having[] = "(TIMESTAMPDIFF(MINUTE, rt_prenotazionepercorso.DataOraSalita, rt_prenotazionepercorso.DataOraDiscesa)/60) >= " . floatval($durata_min);
    }
    if ($durata_max !== '') {
        $having[] = "(TIMESTAMPDIFF(MINUTE, rt_prenotazionepercorso.DataOraSalita, rt_prenotazionepercorso.DataOraDiscesa)/60) <= " . floatval($durata_max);
    }
    $having_clause = count($having) ? " HAVING " . implode(" AND ", $having) : "";
} else {
    $having_clause = "";
}

$q = "
SELECT 
rt_prenotazione.PrenotazioneId AS PrenotazioneId,
rt_prenotazione.CodicePrenotazione AS CodicePrenotazione,
rt_prenotazione.ClienteNome AS ClienteNome,
rt_prenotazionepercorso.LineaNome AS LineaId,
rt_prenotazionepercorso.LineaNome AS LineaNome,
DATE_FORMAT(rt_prenotazionepercorso.DataOraSalita, _utf8 '%d/%m/%Y') AS CorsaDataPartenza,
DATE_FORMAT(rt_prenotazionepercorso.DataOraSalita, _utf8 '%H:%i') AS CorsaOrarioPartenza,
DATE_FORMAT(rt_prenotazionepercorso.DataOraDiscesa, _utf8 '%H:%i') AS CorsaOrarioArrivo,

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
rt_prenotazionepercorso.CorsaNome AS CorsaNome,
rt_prenotazione.PrenotazioneStato AS PrenotazioneStatoId,
rt_appprenotazionestato.PrenotazioneStato AS PrenotazioneStato,
rt_prenotazione.TotalePrenotazione AS TotalePrenotazione,
rt_prenotazione.TotaleResiduo AS TotaleResiduo,
rt_prenotazione.DataIns AS DataInsPrenotazione,
rt_prenotazionepercorso.DataOraSalita as CorsaDataPartenzaPrenotazione,
rt_prenotazionepercorso.DataOraDiscesa as CorsaDataArrivoPrenotazione,
TIMESTAMPDIFF(MINUTE, rt_prenotazionepercorso.DataOraSalita, rt_prenotazionepercorso.DataOraDiscesa)/60 AS DurataOre
FROM RT_Prenotazione AS rt_prenotazione 
JOIN RT_AppPrenotazioneStato AS rt_appprenotazionestato ON (rt_prenotazione.PrenotazioneStato = rt_appprenotazionestato.PrenotazioneStatoId) 
JOIN RT_PrenotazionePercorso AS rt_prenotazionepercorso ON (rt_prenotazione.PrenotazioneId = rt_prenotazionepercorso.PrenotazioneId) 
JOIN Operatore AS operatore ON (rt_prenotazione.OpeIns = operatore.OperatoreId)
JOIN Gestore AS gestore ON (rt_prenotazione.GestoreIdRef = gestore.GestoreId) 
WHERE rt_prenotazione.Stato = 1 AND rt_prenotazione.Cancella = 0
$swhere
GROUP BY rt_prenotazione.PrenotazioneId, rt_prenotazionepercorso.PrenotazionePercorsoId
$having_clause
ORDER BY rt_prenotazione.PrenotazioneId DESC
";

$rep = new CReporTool();
$rep->SetQuery($q);

// Mostra i campi richiesti
$rep->AddField('CodicePrenotazione', 'Numero Itinerario');
$rep->AddField('ClienteNome', 'Cliente');
$rep->AddField('LineaNome', 'Linea');
$rep->AddField('CorsaNome', 'Corsa');
$rep->AddField('CorsaDataPartenza', 'Data partenza');
$rep->AddField('CorsaOrarioPartenza', 'Orario partenza');
$rep->AddField('CorsaOrarioArrivo', 'Orario arrivo');
$rep->AddField('ComuneSalita', 'Comune salita');
$rep->AddField('ComuneDiscesa', 'Comune discesa');
$rep->AddField('TipoTour', 'Tipo');
//$rep->AddField('TotalePostiPrenotati', 'Pax', 1, '', 'i');
$rep->AddField('TotalePrenotazione', 'Totale prenotazione (€)', 1, '', 'money');


$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
$rep->SetNumberDelimiters(',', '.');
$rep->SetSummary('<strong>' . $dizionario['stampe']['totali'] . ' &euro;<strong> ');

$gestore = "Tutti";
$sede = "Tutte";

if ($post_gestore_id > 0) {
    $sql = "SELECT RagioneSociale from Gestore where GestoreId=$post_gestore_id";
    $row = $db->query_first($sql);
    if (!empty($row['RagioneSociale']))
        $gestore = $row['RagioneSociale'];
}
if ($post_sede_id > 0) {
    $sql = "SELECT CodiceSede from ElencoSediView where SedeId=$post_sede_id";
    $row = $db->query_first($sql);
    if (!empty($row['CodiceSede']))
        $sede = $row['CodiceSede'];
}

// Sostituisci la stampa del tipo report con la stampa della durata
$titolo_report = $dizionario['menu_voci']['57'] . "<small><a href='#' class='exportToExcel'>" . $dizionario['stampe']['esporta'] . "</a></small><br />";
$titolo_report .= "<br />Durata: min. " . htmlspecialchars($durata_min) . ", max: " . htmlspecialchars($durata_max);
$titolo_report .= "<br />" . $dizionario['stampe']['periodo_considerato'] . " " . $post_dal . " " . $dizionario['generale']['al'] . " " . $post_al;

$rep->DrawReport($titolo_report);
?>
</div>
<style>
.exportToExcel {
    font-size: 12px !important;
    margin-left: 10px !important;
}
</style> 
<script src="/js/jquery.table2excel.js"></script> 
<script>
$(document).ready(function() {
    $(".exportToExcel").click(function(e){
        $(".report_excel").table2excel({
            exclude: ".noExl",
            name: "Statistiche Base",
            filename: "statistiche_base" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
            fileext: ".xls",
            exclude_img: true,
            exclude_links: true,
            exclude_inputs: true,
            preserveColors: true
        });
    });
});
</script>
<?php
}

if (is_object($user)) {
    $db = new Database();
    $db->connect();
    $user->conn = $db;
    $permessi = $user->get_permessi_modulo($ModuloId);
    $do = $_REQUEST['do'];
    if (!isset($do))
        $do = '';
    switch ($do) {
        default:
            $FunzioneId = 1;
            $permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
            if (sizeof($permesso))
                show_list();
            break;
    }
} else {
    header("Location: /logout.php");
}
?>