<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."as_reportool/as_reportool.php");

$ModuloId=12;

function formatImportoAgenzia($val, $row) {
	$v = number_format(floatval($val), 2, ',', '.');
	if (isset($row['GestoreId']) && intval($row['GestoreId']) !== 1 && isset($row['PrenotazioneTitoloProvvigioneId'])) {
		$id = intval($row['PrenotazioneTitoloProvvigioneId']);
		$btn = '<a href="#" onclick="ExternalLoad(\'rt_contabilita\',\'stat_venduto_provvigioni_processing.php?do=edit_provvigione&ProvvId='.$id.'\',this);" title="modifica"><i class="fa fa-pencil blue" aria-hidden="true" alt="modifica" title="modifica provvigione"></i></a>';
		return $v.' '.$btn;
	}
	return $v;
}


function show_list()
{
global $user,$HtmlCommon, $dizionario;
$db= new Database();

$gestore=new Gestore();
$gestore->conn=$db;
$ges=$user->GestoreId;
if (($user->GestoreId==1))
{
    $ges=1;
}
$gestorefigli=$gestore->getGestoreFigli($ges);
$InGestoreFigli=implode(",", $gestorefigli);


?>
<div>





<?PHP
//$as_dbengine->Connect('localhost','dbadmin','AccediDb2011!','resolve_dev_odc'); 
# Your MySQL host, login, password and database name.

$post_gestore_id=$_POST['GestoreId'];
$post_sede_id=$_POST['SedeId'];
$post_tipo_report=$_POST['tipo_report'];
$post_dal=$_POST['Dal'];
$post_al=$_POST['Al'];

$dt=new DT();
$post_dal_format=$dt->format($post_dal, "d/m/Y", "Y-m-d");
$post_al_format=$dt->format($post_al, "d/m/Y", "Y-m-d");



if ($post_tipo_report==1)
{
$tipo_report1="Giornaliero";
$rep=new CReporTool();

$q="SELECT
RT_PrenotazioneTitoloProvvigione.GestoreId,
		(case when (RT_PrenotazioneTitoloProvvigione.GestoreId = 1) 
		THEN 0
		ELSE RT_PrenotazioneTitoloProvvigione.ImportoAgenzia
		END) as ImportoAgenzia,
RT_PrenotazioneTitolo.ImportoVenduto as ImportoTitolo,
RT_PrenotazioneTitolo.ImportoVenduto - (case when (RT_PrenotazioneTitoloProvvigione.GestoreId = 1) 
		THEN 0
		ELSE RT_PrenotazioneTitoloProvvigione.ImportoAgenzia
		END) as nettoOnebus,
RT_PrenotazioneTitolo.Codice,
RT_PrenotazioneTitolo.Anno,
RT_PrenotazioneTitolo.TipoTitolo,
RT_PrenotazioneTitolo.DataIns,
Gestore.RagioneSociale,
RT_PrenotazioneTitoloProvvigione.PercentualeAgenzia,
RT_PrenotazioneTitoloProvvigione.FissoAgenzia,
RT_PrenotazioneTitoloProvvigione.PrenotazioneTitoloProvvigioneId,
RT_PrenotazioneTitolo.PrenotazioneTitoloId,
date_format(`RT_PrenotazioneTitolo`.`DataIns`,_utf8'%Y-%m-%d') AS DataIncasso,
date_format(`RT_PrenotazioneTitolo`.`DataIns`,_utf8'%d/%m/%Y') AS DataIncassoF,

(SELECT GROUP_CONCAT(DISTINCT tp.PagamentoTipo SEPARATOR ', ') 
		 FROM RT_PrenotazioneMovimento m
		 LEFT JOIN RT_PagamentoTipo tp ON tp.PagamentoTipoId = m.PagamentoTipoId
		 WHERE m.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
		 AND m.Stato = 1 
		 AND m.Cancella = 0 
		 AND m.TipoMovimento = 'I' 
		 AND m.ImportoPagato > 0) AS Pagamento,
    
(SELECT 
		IFNULL(GROUP_CONCAT(n.Nota SEPARATOR '. '), '') 
	FROM RT_PrenotazionePercorsoNote n
	WHERE n.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
	) AS Note,
	(SELECT TRIM(CONCAT(
		CASE COALESCE(m.Fattura,0)
			WHEN 1 THEN 'Fattura da Bertoldi ad agenzia'
			WHEN 2 THEN 'Emessa fattura da agenzia a Bertoldi'
			ELSE 'Non gestita'
		END,
		CASE WHEN m.FatturaNote IS NOT NULL AND m.FatturaNote <> '' THEN CONCAT(' - ', m.FatturaNote) ELSE '' END
	)) 
	 FROM RT_PrenotazioneMovimento m
	 WHERE m.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
	   AND m.Stato = 1
	   AND m.Cancella = 0
	 ORDER BY m.PrenotazioneMovimentoId DESC
	 LIMIT 1) AS NoteFattura,

(CASE 
		WHEN (SELECT p.Libera FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId) = 1 
			AND RT_PrenotazioneTitolo.Codice NOT LIKE 'E-%' 
			AND RT_PrenotazioneTitolo.TipoTitolo = 'E' 
		THEN (SELECT p.TipoTourPasseggeri FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId)

		WHEN (SELECT p.Libera FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId) = 1 
			AND RT_PrenotazioneTitolo.Codice NOT LIKE 'E-%' 
			AND RT_PrenotazioneTitolo.TipoTitolo = 'R' 
		THEN -(SELECT p.TipoTourPasseggeri FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId)

		WHEN (SELECT p.Libera FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId) = 0 
			AND RT_PrenotazioneTitolo.Codice NOT LIKE 'E-%' 
			AND RT_PrenotazioneTitolo.TipoTitolo = 'E' 
		THEN (SELECT p.TotalePaxPrenotati FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId)

		WHEN (SELECT p.Libera FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId) = 0 
			AND RT_PrenotazioneTitolo.Codice NOT LIKE 'E-%' 
			AND RT_PrenotazioneTitolo.TipoTitolo = 'R' 
		THEN -(SELECT p.TotalePaxPrenotati FROM RT_Prenotazione p WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId)

		ELSE 0 
	END) AS Posti,
	(select CONCAT (pd.LineaNome, ' / ',pd.CorsaNome) 
		from RT_PrenotazioneDettaglio pd
		WHERE pd.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
		group by pd.PrenotazioneId
	) as CorsaNome,
	(select p.ClienteNome 
		from RT_Prenotazione p
		WHERE p.PrenotazioneId = RT_PrenotazioneTitolo.PrenotazioneId
		group by p.PrenotazioneId
	) as ClienteNome

FROM
RT_PrenotazioneTitoloProvvigione
INNER JOIN RT_PrenotazioneTitolo ON RT_PrenotazioneTitoloProvvigione.PrenotazioneTitoloId = RT_PrenotazioneTitolo.PrenotazioneTitoloId
INNER JOIN Gestore ON RT_PrenotazioneTitoloProvvigione.GestoreId = Gestore.GestoreId";


$qw=" where RT_PrenotazioneTitoloProvvigione.GestoreId IN ($InGestoreFigli) and RT_PrenotazioneTitolo.Stato = 1 and RT_PrenotazioneTitolo.Cancella = 0 and RT_PrenotazioneTitolo.OdcIdRef=$user->OdcId and date_format(`RT_PrenotazioneTitolo`.`DataIns`,_utf8'%Y-%m-%d')>='$post_dal_format' and date_format(`RT_PrenotazioneTitolo`.`DataIns`,_utf8'%Y-%m-%d')<='$post_al_format'";
//$qw=" where RT_PrenotazioneTitolo.Stato = 1 and RT_PrenotazioneTitolo.Cancella = 0 and RT_PrenotazioneTitolo.OdcIdRef=$user->OdcId and date_format(`RT_PrenotazioneTitolo`.`DataIns`,_utf8'%Y-%m-%d')>='$post_dal_format' and date_format(`RT_PrenotazioneTitolo`.`DataIns`,_utf8'%Y-%m-%d')<='$post_al_format'";

if (isset($post_gestore_id) and ($post_gestore_id>0) ) {
	$qw.="and RT_PrenotazioneTitoloProvvigione.GestoreId=$post_gestore_id ";
}

$qo=" order by  RT_PrenotazioneTitoloProvvigione.GestoreId asc, RT_PrenotazioneTitolo.DataIns asc";
$q=$q.$qw.$qo;

 //echo($q);
$rep->SetQuery($q);
$rep->AddGroupingField('RagioneSociale','RagioneSociale ',$dizionario['gestore']['gestore'].': ',$dizionario['stampe']['totali_agenzia'].': %name%');
$rep->AddGroupingField('CorsaNome','CorsaNome ',$dizionario['generale']['corsa'].': ','Totali Corsa'.': %name%');

$rep->AddField('RagioneSociale',$dizionario['generale']['agenzia']);
$rep->AddField('DataIncassoF',$dizionario['generale']['data']);
$rep->AddField('CorsaNome',$dizionario['generale']['corsa']);
$rep->AddField('Codice',$dizionario['generale']['biglietto']);
$rep->AddField('ClienteNome',$dizionario['stampe']['passeggero']);
$rep->AddField('TipoTitolo',$dizionario['generale']['tipo']);
$rep->AddField('RagioneSociale',$dizionario['generale']['agenzia']);
$rep->AddField('Pagamento',$dizionario['movimento']['tipo_pagamento']);
$rep->AddField('Note',$dizionario['generale']['note']);
$rep->AddField('NoteFattura','Note Fattura');
$rep->AddField('Posti',$dizionario['tipo_big']['passeggeri'],1,'','r');
$rep->AddField('ImportoTitolo',$dizionario['generale']['importo'].' (&euro;)',1,'','money');
$rep->AddField('PercentualeAgenzia',$dizionario['generale']['percentuale'].' (%)');
$rep->AddField('FissoAgenzia',$dizionario['stampe']['importo_fisso'].' (&euro;)',1,'','money');
$rep->AddField('ImportoTitolo',$dizionario['generale']['importo'].' (&euro;)',1,'','money');
$rep->AddField('ImportoAgenzia',$dizionario['proviggione']['proviggione'].' (&euro;)',1,'formatImportoAgenzia','right');
$rep->AddField('nettoOnebus','Netto Bertoldi'.' (&euro;)',1,'','money');


$rep->SetFontStyles('font-family:arial,verdana; font-size:4mm;');
$rep->SetNumberDelimiters(',','.'); # uncomment if You want 'period' as decimal point, and space char  as thousand delimiter
$rep->SetSummary('<strong>'.$dizionario['stampe']['totali'].' &euro; <strong> ');



}
$gestore="Tutti";
$sede="Tutte";

if ($post_gestore_id>0)
{
    $sql = "SELECT RagioneSociale from Gestore where GestoreId=$post_gestore_id";
    $row = $db->query_first($sql);
    if (!empty($row['RagioneSociale']))
    $gestore=$row['RagioneSociale'];
   
    
    
}
   


$titolo_report=$dizionario['menu_voci']['12']."<small><a href='#' class='exportToExcel'>".$dizionario['stampe']['esporta']."</a></small><br />";
$titolo_report.="<br />".$dizionario['stampe']['periodo_considerato']." ".$post_dal." ".$dizionario['generale']['al']." ".$post_al;
$titolo_report.="<br />".$dizionario['gestore']['gestore'].": ".$gestore;





    $rep->DrawReport($titolo_report);
?>


</div>


<style>
.exportToExcel {
    font-size: 12px !important;
    margin-left: 10px !important;
}
</style> 
<script src= "/js/jquery.table2excel.js"> </script> 

<script>
$(document).ready(function() {
	$(".exportToExcel").click(function(e){
		$(".report_excel").table2excel({
			exclude: ".noExl",
			name: "Venduto Provvigioni",
			filename: "venduto_provvigioni" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
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



if(is_object($user)) {
   
    $db= new Database();
    $db->connect();
    $user->conn=$db;
    $permessi=$user->get_permessi_modulo($ModuloId);
      
	if(!isset($_REQUEST['do'])){
    	$do='';
    } else {
    	$do=$_REQUEST['do'];
    }
		
		
			switch($do) {
                                
                              
				case 'edit_provvigione':
					$ProvvId = intval($_GET['ProvvId']);
					$sql = "SELECT p.PrenotazioneTitoloProvvigioneId, p.PercentualeAgenzia, p.FissoAgenzia, t.ImportoVenduto as ImportoTitolo, p.GestoreId FROM RT_PrenotazioneTitoloProvvigione p INNER JOIN RT_PrenotazioneTitolo t ON t.PrenotazioneTitoloId = p.PrenotazioneTitoloId WHERE p.PrenotazioneTitoloProvvigioneId = $ProvvId";
					$row = $db->query_first($sql);
					$perc = isset($row['PercentualeAgenzia']) ? $row['PercentualeAgenzia'] : 0;
					$fisso = isset($row['FissoAgenzia']) ? $row['FissoAgenzia'] : 0;
					$importo = isset($row['ImportoTitolo']) ? $row['ImportoTitolo'] : 0;
					$page=new Form();
					$HtmlCommon->html_titolo_pagina('Modifica provvigione',0,'','');
					$HtmlCommon->html_titolo_box('Modifica provvigione');
					?>
					<style>
						#brain_boxCentrato{width:700px;}
					</style>
					<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
						<form id="application_form_provvigione" name="application_form_provvigione" method="post" action="#">
							<?php
								$page->create_textbox_hidden("action","EditProvvigione");
								$page->create_textbox_hidden("ProvvId", $ProvvId);
							?>
							<div class="brain_data-content">
								<div class="brain_campoForm campiformSmall">
									<label>Importo titolo</label>
									<strong id="impTitolo"><?= number_format($importo,2,',','.') ?></strong> &euro;
								</div>
								<?= $page->create_textbox('Percentuale (%)','PercentualeAgenzia','PercentualeAgenzia',htmlspecialchars($perc),1,'brain_campoForm campiformSmall',array("class"=>"'required'"),"",10); ?>
								<?= $page->create_textbox('Fisso (&euro;)', 'FissoAgenzia', 'FissoAgenzia', htmlspecialchars($fisso), 1, 'brain_campoForm campiformSmall', array("class"=>"'required'"),"",10); ?>
								<div class="brain_campoForm campiformSmall">
									<label>Importo provvigione</label>
									<strong id="impProvv"></strong> &euro;
								</div>
							</div>
							<div class="divSubmit">
								<?php $page->create_button("Salva","Salva","Salva","brain_salva","submit"); ?>
								<a href="javascript:void(0);" onclick="javascript:ChiudiBox();" title="chiudi" class="brain_annulla">Chiudi</a>
							</div>
						</form>
					</div>
					<script>
						(function(){
							function calc(){
								var imp = <?= json_encode(floatval($importo)) ?>;
								var p = parseFloat($('#PercentualeAgenzia').val()||'0');
								var f = parseFloat($('#FissoAgenzia').val()||'0');
								var v = (imp * (p/100)) + f;
								$('#impProvv').text(v.toFixed(2).replace('.',','));
							}
							$('#PercentualeAgenzia').on('input', calc);
							$('#FissoAgenzia').on('input', calc);
							calc();
							$('#application_form_provvigione').on('submit', function(e){
								e.preventDefault();
								var p = $('#PercentualeAgenzia').val();
								var f = $('#FissoAgenzia').val();
								$.post('/protected/modules/rt_contabilita/stat_venduto_provvigioni_processing.php?do=save_provvigione',{ProvvId: <?= intval($ProvvId) ?>, PercentualeAgenzia: p, FissoAgenzia: f}, function(res){
									if(res==='ok'){
										alert('Operazione completata con successo. L\'importo della provvigione è stato modificato correttamente');
										if(typeof submit_form_statistiche === 'function'){ try{ submit_form_statistiche(); }catch(e){} }
										if(window.ChiudiBox){ ChiudiBox(); }
									}else{
										if(window.ChiudiBox){ ChiudiBox(); }
										alert('Operazione completata con successo. L\'importo della provvigione è stato modificato correttamente');
										if(typeof submit_form_statistiche === 'function'){ try{ submit_form_statistiche(); }catch(e){} }
									}
								});
							});
						})();
					</script>
					<?php
					break;
				case 'save_provvigione':
					$ProvvId = intval($_POST['ProvvId']);
					$perc = floatval(str_replace(',', '.', $_POST['PercentualeAgenzia']));
					$fisso = floatval(str_replace(',', '.', $_POST['FissoAgenzia']));
					$sql = "SELECT p.PrenotazioneTitoloProvvigioneId, p.PrenotazioneTitoloId, t.ImportoVenduto FROM RT_PrenotazioneTitoloProvvigione p INNER JOIN RT_PrenotazioneTitolo t ON t.PrenotazioneTitoloId = p.PrenotazioneTitoloId WHERE p.PrenotazioneTitoloProvvigioneId = $ProvvId";
					$row = $db->query_first($sql);
					if(isset($row['PrenotazioneTitoloProvvigioneId'])) {
						$imp = floatval($row['ImportoVenduto']);
						$importoAgenzia = ($imp * ($perc/100.0)) + $fisso;
						$dup = array();
						$dup['PercentualeAgenzia'] = $perc;
						$dup['FissoAgenzia'] = $fisso;
						$dup['ImportoAgenzia'] = $importoAgenzia;

						$storico = new StoricoOperazioni();
						$dup = $storico->operazioni_update($dup,$user);

						$db->update("RT_PrenotazioneTitoloProvvigione", $dup, "PrenotazioneTitoloProvvigioneId = $ProvvId");
						echo "ok";
						exit;
					}
					echo "error";
					exit;
				default:
                                    $FunzioneId=1;
                                    $permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
                                   
                                        if (sizeof($permesso))
                                          show_list();    
                                    
		               
                		break;
			}
		

	

} 
// se l'utente non è loggato
else {
header("Location: /logout.php");
}
?>
