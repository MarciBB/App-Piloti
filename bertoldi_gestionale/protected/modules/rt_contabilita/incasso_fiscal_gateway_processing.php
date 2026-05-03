<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$config->load();
$classespath_ = Config::$classespath;
include_once($classespath_ . "as_reportool/as_reportool.php");

$db = new Database();
$db->connect();

// Calcolo intervallo: dalle 23:00 del giorno prima alle 23:00 del giorno selezionato
$dt = new DT();
$dataFiltro = isset($_POST['Dal']) ? $_POST['Dal'] : date('d/m/Y');
$dataFiltroSql = $dt->format($dataFiltro, "d/m/Y", "Y-m-d");
$dataInizio = date('Y-m-d 23:00:00', strtotime($dataFiltroSql . ' -1 day'));
$dataFine = $dataFiltroSql . ' 23:00:00';

// Query incassi
$q_incassi = "SELECT 
        order_id, 
        creation_date, 
        'Emesso' AS Operazione, 
        totale
      FROM bills
      WHERE (creation_date >= '$dataInizio' AND creation_date <= '$dataFine')";

// Incassi
$res_incassi = $db->fetch_array($q_incassi);
$num_incassi = count($res_incassi);

// Query annullamenti
$q_annullamenti = "SELECT 
        order_id, 
        creation_date, 
        CASE WHEN live = 1 THEN 'Emesso' ELSE 'Annullato' END AS Operazione, 
        totale
      FROM bills
      WHERE (last_update_date >= '$dataInizio' AND last_update_date <= '$dataFine' AND live = 0)";

// Annullamenti
$res_annullamenti = $db->fetch_array($q_annullamenti);
$num_annullamenti = count($res_annullamenti);

// Tabella incassi
$rep_incassi = new CReporTool();
$rep_incassi->SetQuery($q_incassi);
$rep_incassi->AddField('order_id', $dizionario['fiscaly']['scontrino_n']);
$rep_incassi->AddField('creation_date', $dizionario['fiscaly']['data_emissione']);
$rep_incassi->AddField('Operazione', $dizionario['fiscaly']['operazione']);
$rep_incassi->AddField('totale', $dizionario['fiscaly']['totale'], 1, '', 'money');
$rep_incassi->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
$rep_incassi->SetNumberDelimiters(',', '.');
$rep_incassi->SetSummary('<strong>'.$dizionario['fiscaly']['totale_generale']. ' | '.$dizionario['fiscaly']['scontrini']. ': ' . $num_incassi . '</strong>');


// Format intervallo per titoli: YYYY/mm/dd H:i
$fmtInizio = date('Y/m/d H:i', strtotime($dataInizio));
$fmtFine = date('Y/m/d H:i', strtotime($dataFine));

$titolo_report_incassi = $dizionario['menu_voci']['58'].'<br />';
$titolo_report_incassi .= "<br />".$dizionario['fiscaly']['scontrini_emessi']." <small><a href='#' class='exportToExcel'>Esporta</a></small><br />";
$titolo_report_incassi .= "<br />".$dizionario['stampe']['periodo_considerato']." ".$fmtInizio." ".$dizionario['generale']['al']." ".$fmtFine;
$titolo_report_incassi .= "<br />".$dizionario['fiscaly']['numero_scontrini'].": $num_incassi";

?>
<div class="emetti">
	<?php
$rep_incassi->DrawReport($titolo_report_incassi);
?>
</div>
<?php
// Tabella annullamenti
$rep_annullamenti = new CReporTool();
$rep_annullamenti->SetQuery($q_annullamenti);
$rep_annullamenti->AddField('order_id', $dizionario['fiscaly']['scontrino_n']);
$rep_annullamenti->AddField('creation_date', $dizionario['fiscaly']['data_emissione']);
$rep_annullamenti->AddField('Operazione', $dizionario['fiscaly']['operazione']);
$rep_annullamenti->AddField('totale', $dizionario['fiscaly']['totale'], 1, '', 'money');
$rep_annullamenti->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
$rep_annullamenti->SetNumberDelimiters(',', '.');
$rep_annullamenti->SetSummary('<strong>'.$dizionario['fiscaly']['totale_generale'].' | '.$dizionario['fiscaly']['scontrini'].': ' . $num_annullamenti . '</strong>');

$titolo_report_annullamenti = "<br />".$dizionario['fiscaly']['scontrini_annullati']." <small><a href='#' class='exportToExcelAnnulla'>Esporta</a></small><br />";
$titolo_report_annullamenti .= "<br />".$dizionario['stampe']['periodo_considerato']." ".$fmtInizio." ".$dizionario['generale']['al']." ".$fmtFine;
$titolo_report_annullamenti .= "<br />".$dizionario['fiscaly']['numero_scontrini'].": $num_annullamenti";

?>
<div class="annulla">
	<?php
	$rep_annullamenti->DrawReport($titolo_report_annullamenti);
	?>
</div>

<style>
.exportToExcel, .exportToExcelAnnulla {
    font-size: 12px !important;
    margin-left: 10px !important;
}
</style>
<script src="/js/jquery.table2excel.js"></script>
<script>
$(document).ready(function() {
    $(".exportToExcel").click(function(e){
        $("div.emetti .report_excel").table2excel({
            exclude: ".noExl",
            name: "IncassiFiscalGateway",
            filename: "incassi_fiscal_gateway_" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
            fileext: ".xls",
            exclude_img: true,
            exclude_links: true,
            exclude_inputs: true,
            preserveColors: true
        });
    });

	$(".exportToExcelAnnulla").click(function(e){
        $("div.annulla .report_excel").table2excel({
            exclude: ".noExl",
            name: "IncassiFiscalGateway",
            filename: "incassi_fiscal_gateway_" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
            fileext: ".xls",
            exclude_img: true,
            exclude_links: true,
            exclude_inputs: true,
            preserveColors: true
        });
    });
});
</script>