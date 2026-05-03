<!-- Autore: Marco Casaburi
	Data Ultima Modifica: 06/11/2015 
 -->
<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" type="text/css" href="/css/stile_prenotazioni.css" />
<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
include_once($basepath . "/protected/include/ipg-util.php");
$config = new Config();
$run = $config->load();
$modulespath_= Config::$modulespath;
$classespath_= Config::$classespath;
$errors = new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.PrenotazioneMovimento.php");
include_once($classespath_."class.FatturaInCloudViaggiatore.php");
include_once($classespath_."class.PagamentoTipo.php");
include_once($classespath_."class.PrefissoTelefono.php");
include_once($classespath_."class.Flotta.php");
include_once($classespath_."Graph/class.LineaGraph.php");
include_once ($classespath_ . "Graph/class.GrafoTratte.php");

global $ModuloId;
global $user;
global $prenotazione_wizard, $funzione_edit, $abilita_modifica;

$ModuloId=2;// modulo base mediazione

$funzione_edit = false;
$prenotazione_wizard = null;

if(isset($_SESSION['PRENOTAZIONE_WIZARD'])) {
	$prenotazione_wizard = unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
}


function RimborsoParziale() {
	global $user, $HtmlCommon, $ModuloId, $dizionario;

	$db = new Database();
	$db->connect();

	$page = new Form();
	$dt = new DT();

	// Array per definire i tipi di tragitto disponibili
	$arr_stato[] = array("StatoId" => '0', "Stato" => $dizionario['biglietto']['andata_ritorno']);
	$arr_stato[] = array("StatoId" => 'Andata', "Stato" => $dizionario['biglietto']['andata']);
	$arr_stato[] = array("StatoId" => 'Ritorno', "Stato" => $dizionario['biglietto']['ritorno']);

	// Recupera l'ID del titolo di prenotazione
	$TitoloId = $_REQUEST['PrenotazioneTitoloId'];

	// Recupera il numero di prenotazione e il codice del titolo
	$sql = "Select PrenotazioneNumeroId, Codice, PrenotazioneId from RT_PrenotazioneTitolo where PrenotazioneTitoloId=$TitoloId";
	$ArrPrezzo = $db->query_first($sql);
	$PrenotazioneNumeroId = $ArrPrezzo['PrenotazioneNumeroId'];
	$Codice = $ArrPrezzo['Codice'];

	// Recupera prenotazione associata al titolo
	//$sql = "Select * from RT_Prenotazione where PrenotazioneId = " . $ArrPrezzo['PrenotazioneId'];
	//$prenotazione = $db->query_first($sql);

	// Recupera il tipo di titolo (E = Emesso, R = Rimborso)
	$sql = "SELECT TipoTitolo FROM RT_PrenotazioneTitolo where PrenotazioneTitoloId = $TitoloId";
	$TipoTitoloRow = $db->query_first($sql);
	$TipoTitolo = $TipoTitoloRow['TipoTitolo'];

	// Determina i tragitti disponibili per il rimborso
	if ($TipoTitolo == 'E') {
		$sql = "select distinct Tragitto from RT_PrenotazioneDettaglio where PrenotazioneNumero=$PrenotazioneNumeroId and Escludi=0";
		$ArrNumeroTragitti = $db->fetch_array($sql);
		$countTragitti = sizeof($ArrNumeroTragitti);
		if ($countTragitti < 2) {
			$arr_stato = null;
			$arr_stato[0]['StatoId'] = $ArrNumeroTragitti[0]['Tragitto'];
			$arr_stato[0]['Stato'] = $ArrNumeroTragitti[0]['Tragitto'];
		}
	} else {
		$sql = "select distinct Tragitto from RT_PrenotazioneDettaglio where PrenotazioneNumero=$PrenotazioneNumeroId";
		$ArrNumeroTragitti = $db->fetch_array($sql);
		$countTragitti = sizeof($ArrNumeroTragitti);
		if ($countTragitti < 2) {
			$arr_stato = null;
			$arr_stato[0]['StatoId'] = $ArrNumeroTragitti[0]['Tragitto'];
			$arr_stato[0]['Stato'] = $ArrNumeroTragitti[0]['Tragitto'];
		} else {
			$arr_stato = null;
			$arr_stato[0]['StatoId'] = 0;
			$arr_stato[0]['Stato'] = $dizionario['biglietto']['andata_ritorno'];
		}
	}

	// Recupera i tipi di pagamento disponibili per il rimborso
	$PagamentoTipo = new PagamentoTipo();
	$PagamentoTipo->conn = $db;
	$sql = "SELECT * FROM Gestore WHERE GestoreId = " . $user->GestoreId;
	$gestore = $db->query_first($sql);
	if (!$gestore['Verificato']) {
		$tipo = 12;
		$allPagamentiTipo = $PagamentoTipo->getTipoRimborsoForSelect($tipo);
	} else {
		$allPagamentiTipo = $PagamentoTipo->getAllRimborsoForSelect();
	}

	// Titolo della pagina e del box
	$HtmlCommon->html_titolo_pagina($dizionario['biglietto']['rimborso_titolo'] . " " . $Codice, 0, "", "");
	$HtmlCommon->html_titolo_box($dizionario['biglietto']['rimborso_titolo'] . " " . $Codice);

	include_once("biglietto_validator.php");
	?>
	<script type="text/javascript">
		$(document).ready(function () {

			// Configurazione del datepicker per la selezione della data di rimborso
			$(function () {
				$("#DataRimborso").datepicker({
					maxDate: new Date(),
					monthNames: [<?=$dizionario['generale']['nome_mesi']?>],
					monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
					monthStatus: '<?=$dizionario['generale']['mese_status']?>',
					yearStatus: '<?=$dizionario['generale']['anno_status']?>',
					weekHeader: 'Sm', weekStatus: '',
					weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
					dayNames: [<?=$dizionario['generale']['nome_giorni']?>],
					dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
					dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
					dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
					dateStatus: '<?=$dizionario['generale']['data_status']?>',
					dateFormat: 'dd/mm/yy', firstDay: 1,
					initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
					dateFormat: 'dd/mm/yy'
				});
			});
		});
	</script>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
			<form id="application_form" name="application_form" method="post" action="#">
				<div class="brain_formModifica">
					<h1><?=$dizionario['biglietto']['rimborso_titolo']?> <?=$Codice?></h1>
					<div class="brain_data-content">
						<?
						// Se non ci sono tragitti disponibili, mostra un messaggio
						if ($countTragitti == 0) {
							?>
							<label><?=$dizionario['biglietto']['titolo_gia_rimborsato']?></label>
							<?
						} else {
							// Configura il modulo per il rimborso
							if ($TipoTitolo == 'E') {
								$page->create_textbox_hidden("action", "RimborsoParziale");
							} else {
								$page->create_textbox_hidden("action", "RimborsoExtra");
							}
							$page->create_textbox_hidden("PrenotazioneNumeroId", $PrenotazioneNumeroId);
							$page->create_textbox_hidden("CodiceTitolo", $Codice);

							print("<br style=\"clear:both;\"/>");

							// Selezione del tragitto
							$page->create_select($dizionario['biglietto']['tragitto'], "Tragitto", "RimborsoTragitto", "brain_campoForm", $arr_stato, -1, "StatoId", "Stato", null, 1);

							//indica se cancellare l'evento su google calendar
							?>
							<input style="margin: 23px 0px 0px 0px !important;
									padding: 0px !important;
									opacity: initial;
									height: auto;
									width: auto;
									position: inherit;"
								id = "GoogleCalendarRimuovi" name="GoogleCalendarRimuovi" type="checkbox" value="1" checked='checked' /> 
							<label style="background: none !important;
									height: inherit;
									width: auto;
									margin: 24px 0px 0px 6px;" 
								id="label_GoogleCalendarRimuovi" for="GoogleCalendarRimuovi"><?= $dizionario['biglietto']['rimuovi_tour_calendario']?></label>
					
							<?php

							print("<br style=\"clear:both;\"/>");

							// Selezione del tipo di rimborso
							$page->create_select($dizionario['biglietto']['tipo_rimborso'], "PagamentoTipoId", "TipoPagamento", "brain_campoForm", $allPagamentiTipo, 0, "PagamentoTipoId", "EtichettaRimborso", array("class" => "'required'"), 1);
							$page->create_textbox($dizionario['biglietto']['data_rimborso'], "DataRimborso", "DataRimborso", $dt->format(date('Y-m-d H:i:s', time()), "Y-m-d H:i:s", "d/m/Y"), 0, "brain_campoForm", array("class" => "'italianDate'"), "", "10");

							print("<br style=\"clear:both;\"/>");

							// Campi per l'importo massimo, il valore rimborsato e il residuo
							$page->create_textbox($dizionario['biglietto']['max_rimborsabile'], "RimborsoImportoMassimo", "ImportoMassimo", "", 0, "brain_campoForm", array('readonly' => 'readonly'));
							$page->create_textbox($dizionario['biglietto']['importo_rimborsato'], "RimborsoValoreRimborso", "ValoreRimborso", 0, 1, "brain_campoForm", array("class" => "'required numberDE'"));

							print("<br style=\"clear:both;\"/>");

							$page->create_textbox($dizionario['biglietto']['residuo_rimborso'], "RimborsoResiduo", "Residuo", "", 0, "brain_campoForm", array('readonly' => 'readonly'));

							$page->create_textbox_hidden("TipoMovimento", "R");
						}

						print("<br style=\"clear:both;\"/>");
						?>
						<span id="penaleRimborso"></span>
					</div>
				</div>
				<div class="divSubmit">
					<? if ($countTragitti > 0) { ?>
						<button class="brain_salva" id="rimborsoSalva" type="button"><?=$dizionario['generale']['salva']?></button>
					<? } ?>
				</div>
			</form>
		</div>
	</div>
	<?
}


function show_prenotazioni()
{
	global $user,$HtmlCommon,$db,$ModuloId, $dizionario;

	$HtmlCommon->html_titolo_pagina($dizionario['biglietto']['elenco_prenotazioni'],0,"","");
	$HtmlCommon->html_titolo_box($dizionario['biglietto']['elenco_prenotazioni']);
	$db= new Database();
	$db->connect();
	$_SESSION['PRENOTAZIONE_WIZARD']=null;
	unset($_SESSION['PRENOTAZIONE_WIZARD']);

	include_once("biglietto_validator.php");
	include_once("prenotazioni_datatable.php");


	/*$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
	 if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_biglietto','biglietto.php?do=add','nuova prenotazione / biglietto');
	 */
	?>
	<table cellpadding="0" cellspacing="0" border="0" class="display"
	id="brain_datatables">
	<thead>

	<tr class="brain_tabellaTr">
	<th width="10%"><?=$dizionario['generale']['stato']?></th>
	<th width="12%"><?=$dizionario['generale']['agenzia']?></th>
	<th width="8%"><?=$dizionario['generale']['num_itinerario']?></th>
	<th width="4%"><?=$dizionario['generale']['cp']?></th>
	<th width="8%"><?=$dizionario['generale']['titolo']?></th>
	<th width="4%"><?=$dizionario['generale']['anno']?></th>
	<th width="4%"><?=$dizionario['generale']['tipo_short']?></th>
	<th width="8%"><?=$dizionario['generale']['data_emissione']?></th>
	<th width="20%"><?=$dizionario['generale']['cliente']?></th>
	<th width="10%"><?=$dizionario['generale']['biglietto']?></th>
	<th width="10%"><?=$dizionario['generale']['importo']?></th>
	<th width="10%">Contabilizzato</th>
	<th width="3%"><?=$dizionario['generale']['edita']?></th>
	<th width="3%"><?=$dizionario['generale']['stampa']?></th>

	</tr>

	<tr class="brain_tabellaFilter">
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="text" /></th>
	<th><input type="hidden" /></th>
	<th><input type="hidden" /></th>
	</tr>
	</thead>
	<tbody>

	<tr>
	<td colspan="10" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
	</tr>
	</tbody>

	</table>
	<?
			$db->close();

}

function show_list() {
	global $user,$HtmlCommon,$db,$ModuloId, $dizionario;
	$HtmlCommon->html_titolo_pagina($dizionario['prenotazioni']['titolo_elenco'],0,"","");
	$HtmlCommon->html_titolo_box($dizionario['prenotazioni']['titolo_elenco']);
	$db= new Database();
	$db->connect();

	include_once("biglietto_validator.php");
	include_once("biglietto_datatable.php");

	$aggiungi=$user->ControllModuloFunzionePermesso($ModuloId,2);
	if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_biglietto','biglietto.php?do=add',$dizionario['prenotazioni']['btn_aggiungi'], '<i class="fa fa-plus" aria-hidden="true"></i> ');

	?>
	<div class="brain_servicebar">
		<a id="esportaPrenotazioni" style="color: #333;
		    float: right;
		    font-size: 13px;
		    padding: 3px 12px 6px 12px;
		    text-decoration: underline;" 
    href="#" title="<?php echo $dizionario['generale']['esporta']?>"><?php echo $dizionario['generale']['esporta']?></a>
	</div>
	<script>
		$(document).ready(function(){
			$('#esportaPrenotazioni').click(function(){
				if($('.sSearch_0').val() != "" ||
						$('.sSearch_1').val() != "" ||
						$('.sSearch_2').val() != "" ||
						$('.sSearch_3').val() != "" ||
						$('.sSearch_4').val() != "" ||
						$('.sSearch_5').val() != "" ||
						$('.sSearch_6').val() != "" ||
						$('.sSearch_7').val() != "" ||
						$('.sSearch_8').val() != "" ||
						$('.sSearch_9').val() != "" ||
						$('.sSearch_10').val() != "" ||
						$('.sSearch_11').val() != "" ||
						$('.sSearch_12').val() != "" ||
						$('.sSearch_13').val() != "" ||
						$('.sSearch_14').val() != "") {
				self.location.href = 'protected/modules/rt_biglietto/exportPrenotazioniExcel.php?sSearch_0='+$('.sSearch_0').val()
				+'&sSearch_1='+$('.sSearch_1').val()
				+'&sSearch_2='+$('.sSearch_2').val()
				+'&sSearch_3='+$('.sSearch_3').val()
				+'&sSearch_4='+$('.sSearch_4').val()
				+'&sSearch_5='+$('.sSearch_5').val()
				+'&sSearch_6='+$('.sSearch_6').val()
				+'&sSearch_7='+$('.sSearch_7').val()
				+'&sSearch_8='+$('.sSearch_8').val()
				+'&sSearch_9='+$('.sSearch_9').val()
				+'&sSearch_10='+$('.sSearch_10').val()
				+'&sSearch_11='+$('.sSearch_11').val()
				+'&sSearch_12='+$('.sSearch_12').val()
				+'&sSearch_13='+$('.sSearch_13').val()
				+'&sSearch_14='+$('.sSearch_14').val();
				} else {
					alert("<?php echo $dizionario['generale']['esporta_alert']?>");
				}	
			});
			
		});
	</script>
	<table cellpadding="0" cellspacing="0" border="0" class="display"
	id="brain_datatables">
	<thead>

	<tr class="brain_tabellaTr">
	<th width="8%"><?=$dizionario['generale']['stato']?></th>
	<th width="15%"><?=$dizionario['generale']['agenzia']?></th>
	<th width="12%"><?=$dizionario['generale']['data_creazione']?></th>
	<th width="8%"><?=$dizionario['generale']['num_itinerario']?></th>
	<th width="12%"><?=$dizionario['generale']['cliente']?></th>
	<th width="18%"><?=$dizionario['generale']['linea']?></th>
	<th width="5%"><?=$dizionario['generale']['data_partenza_short']?></th>
	<th width="4%"><?=$dizionario['generale']['ora_partenza_short']?></th>
	<th width="10%"><?=$dizionario['generale']['da']?></th>
	<th width="10%"><?=$dizionario['generale']['a']?></th>
	<th width="10%"><?=$dizionario['generale']['tipo']?></th>
	<th width="5%"><?=$dizionario['generale']['pax']?></th>
	<th width="5%"><?=$dizionario['generale']['totale']?></th>
	<th width="5%"><?=$dizionario['generale']['residuo']?></th>
	<th width="3%"><?=$dizionario['generale']['edita']?></th>
	</tr>

	<tr class="brain_tabellaFilter">
	<th><input type="text" class="sSearch_0"/></th>
	<th><input type="text" class="sSearch_1"/></th>
	<th><input type="text" class="sSearch_2"/></th>
	<th><input type="text" class="sSearch_3"/></th>
	<th><input type="text" class="sSearch_4"/></th>
	<th><input type="text" class="sSearch_5"/></th>
	<th><input type="text" class="sSearch_6"/></th>
	<th><input type="text" class="sSearch_7"/></th>
	<th><input type="text" class="sSearch_8"/></th>
	<th><input type="text" class="sSearch_9"/></th>
	<th><input type="text" class="sSearch_10"/></th>
	<th><input type="text" class="sSearch_11"/></th>
	<th><input type="text" class="sSearch_12"/></th>
	<th><input type="text" class="sSearch_13"/></th>
	<th><input type="hidden" /></th>
	</tr>
	</thead>
	<tbody>

	<tr>
	<td colspan="16" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
	</tr>
	</tbody>

	</table>
	<?
			$db->close();

}

function add($step)
{
	global $HtmlCommon, $db, $prenotazione_wizard, $funzione_edit, $abilita_modifica,$user, $dizionario;
                          
	include_once("biglietto_validator.php");
    
	$GestoreStato=-1;

	if (!isset($step)) {
		$prenotazione_wizard=null;
		unset($prenotazione_wizard);
		$_SESSION['PRENOTAZIONE_WIZARD']=null;
		unset($_SESSION['PRENOTAZIONE_WIZARD']);
		$step=1;
		$mod=0;
	}
	
	if (isset($prenotazione_wizard) && is_object($prenotazione_wizard))
	{
		$PrenotazioneId = $prenotazione_wizard->Id;
		$prenotazione_wizard->conn=$db;
		$prenotazione_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr=$prenotazione_wizard->DatiGenerali;
		$Stato=$DatiGeneraliArr['Stato'];
		$NomePrenotazione=$DatiGeneraliArr['ClienteNome']." / ".$DatiGeneraliArr['CodicePrenotazione'];
		$StatoPrenotazione = $prenotazione_wizard->getStatoPrenotazioneStato($DatiGeneraliArr['PrenotazioneStato']);
		
		$mod=1;
		$abilita_modifica=true;
		$HtmlCommon->html_titolo_pagina($dizionario['biglietto']['modifica_prenotazione_biglietto']." ".$NomePrenotazione." - ".$dizionario['biglietto']['stato_prenotazione']." ".$StatoPrenotazione,1,"rt_biglietto","biglietto.php");
	} else {
		$mod=0;
		$abilita_modifica=false;
		$HtmlCommon->html_titolo_pagina($dizionario['biglietto']['nuova_prenotazione_compilazione'],1,"rt_biglietto","biglietto.php");
	}

	carica_menu_prenotazione($step, $mod);
	?>
	<div id="brain_mediazioneformcenter" class="brain_boxLeft wizart">
	<?php
	$db= new Database();
	$db->connect();

	if ($step==1)
		add_step_prenotazione();
	elseif ($step==2)
		pagamenti();
	elseif ($step==3)
		elenco_titoli_viaggio();
	elseif ($step==4)
		messaggi();
	?>
	</div>
	<?

}

function edit($PrenotazioneId, $step = 1)
{
	global $prenotazione_wizard, $db, $user;
	
	$prenotazione_wizard = new Prenotazione($PrenotazioneId);
	$prenotazione_wizard->conn = $db;
	$prenotazione_wizard->inizializzaDatiGenerali();
	
	$_SESSION['PRENOTAZIONE_WIZARD'] = serialize($prenotazione_wizard);
	
	add($step);
}

function add_step_prenotazione()
{
	global $prenotazione_wizard, $user, $db, $dizionario;

	$step_corrente = 1;

	$page = new Form();
	$dt = new DT();

	$azione = "add";
	$action = "create";

	$PrenotazioneId=0;
	if(isset($_REQUEST['CorsaId'])){
		$CorsaId=$_REQUEST['CorsaId'];
	} else {
		$CorsaId=null;
	}
	
	$StatoPrenotazioneId=0;
	$StatoPrenotazione=$dizionario['biglietto']['in_compilazione'];
	$RitornoOpen=0;
	
	$multi = false;
	$ripristinabile=false;
	
	//recupero info gestore user e prenotazione
	$gestoreIdUser = $user->GestoreId;
	$gestoreIdPrenotazione = $gestoreIdUser;
	$gestore = new Gestore();
	$gestore->conn = $db;
	$gestoreSelect = $gestore->getAllForSelect();
	
	if (is_object($prenotazione_wizard) and ($prenotazione_wizard->Id))
	{
		$PrenotazioneId=$prenotazione_wizard->Id;
		$prenotazione_wizard->conn=$db;
		$prenotazione_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		
		//recupero gestore prenotazione in modifica
		if(isset($DatiGeneraliArr['GestoreIdRef'])) {
			$gestoreIdPrenotazione = $DatiGeneraliArr['GestoreIdRef'];
		}
		
		$_SESSION['PRENOTAZIONE_WIZARD'] = serialize($prenotazione_wizard);

		$StatoPrenotazioneId=$prenotazione_wizard->getStatoPrenotazioneStatoIdByCorsa($CorsaId);

		// print_r($row);
		if($DatiGeneraliArr['PrenotazioneId'])
		{
			$azione = "edit";
			$action = "update";
		}
		
		$multi = $DatiGeneraliArr['Multi'];
		
		$s="select PrenotazioneTransazioneWeb from RT_PrenotazioneTransazione where PrenotazioneId=$PrenotazioneId";
		$row=$db->query_first($s);
		if (empty($row['PrenotazioneTransazioneWeb'])) {
		    $ripristinabile=true;
		} else {
		    $ripristinabile=true;
		}
	
	} else {
		if(isset($_GET['new']) && $_GET['new'] == 1){
			$_SESSION['PRENOTAZIONE_MULTI'] = null;
		}
		if (isset($_SESSION['PRENOTAZIONE_MULTI'])) {
			$multi = true;
		}
	}
	?>

	<script type="text/javascript">
	$(document).ready(function() {

		// Datepicker
		$(function() {
			$( "#RicercaDaId" ).datepicker({
				monthNames:
					[<?=$dizionario['generale']['nome_mesi']?>],
					monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
					monthStatus: '<?=$dizionario['generale']['mese_status']?>',
					yearStatus: '<?=$dizionario['generale']['anno_status']?>',
					weekHeader: 'Sm', weekStatus: '',
					weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
				dayNames:
					[<?=$dizionario['generale']['nome_giorni']?>],
					dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
					dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
					dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
					dateStatus: '<?=$dizionario['generale']['data_status']?>',
					dateFormat: 'dd/mm/yy', firstDay: 1,
					initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
		            dateFormat: 'dd/mm/yy'
			});


			$( "#RicercaAId" ).datepicker({
				monthNames:
					[<?=$dizionario['generale']['nome_mesi']?>],
					monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
					monthStatus: '<?=$dizionario['generale']['mese_status']?>',
					yearStatus: '<?=$dizionario['generale']['anno_status']?>',
					weekHeader: 'Sm', weekStatus: '',
					weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
				dayNames:
					[<?=$dizionario['generale']['nome_giorni']?>],
					dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
					dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
					dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
					dateStatus: '<?=$dizionario['generale']['data_status']?>',
					dateFormat: 'dd/mm/yy', firstDay: 1,
					initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
		            dateFormat: 'dd/mm/yy'
			});
			
			$( "#DataPartenzaLibera" ).datepicker({
				monthNames:
					[<?=$dizionario['generale']['nome_mesi']?>],
					monthNamesShort: [<?=$dizionario['generale']['nome_mesi_short']?>],
					monthStatus: '<?=$dizionario['generale']['mese_status']?>',
					yearStatus: '<?=$dizionario['generale']['anno_status']?>',
					weekHeader: 'Sm', weekStatus: '',
					weekStatus: '<?=$dizionario['generale']['settimana_status']?>',
				dayNames:
					[<?=$dizionario['generale']['nome_giorni']?>],
					dayNamesShort: [<?=$dizionario['generale']['nome_giorni_short']?>],
					dayNamesMin: [<?=$dizionario['generale']['nome_giorni_min']?>],
					dayStatus: '<?=$dizionario['generale']['giorno_status']?>',
					dateStatus: '<?=$dizionario['generale']['data_status']?>',
					dateFormat: 'dd/mm/yy', firstDay: 1,
					initStatus: '<?=$dizionario['generale']['seleziona_data']?>',
		            dateFormat: 'dd/mm/yy'
			});
			
            $(".Orario").mask("99:99");   

		});
	});
	</script>

	<form id="application_form" name="application_form" method="post"
	action="#">
	
		<?php
			$page->create_textbox_hidden("step_corrente",$step_corrente);
			$page->create_textbox_hidden("step_successivo",$step_corrente+1);
			$page->create_textbox_hidden("cambio_data",$StatoPrenotazioneId);
		?>
			<div class="brain_formModifica">

		<?php 
			if($gestoreIdUser == 1) { ?>
				<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['scelta_gestore']?></h2>
			<?php
				$page->create_select($dizionario['gestore']['gestore'],"GestoreIdRef","GestoreIdRef","brain_campiform",$gestoreSelect, $gestoreIdPrenotazione,"GestoreId","Gestore", null, 1);
				print("<br style=\"clear:both;\"/>");
			} else {
				$page->create_textbox_hidden("GestoreIdRef",$gestoreIdPrenotazione);
			}
		?>

			<input type='hidden' value="0" name="Prenotazione[Multi]">
			<?php if ($multi) { ?>
				<br style="clear: both;" />
				
				<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['riepilogo']?></h2>
				<div class="brain_data-content">
					<table width="100%" cellspacing="0" cellpadding="0" border="0" id="pagamentiTabella">
						<tbody>
							<tr class="rowIntestazione">
								<td><?=$dizionario['generale']['data_ora_partenza']?></td>
								<td><?=$dizionario['generale']['comune_partenza']?></td>
								<td><?=$dizionario['generale']['data_ora_arrivo']?></td>
								<td><?=$dizionario['generale']['comune_arrivo']?></td>
								<td><?=$dizionario['generale']['viaggio']?></td>
								<td><?=$dizionario['generale']['pax_prenotati']?></td>
								<td><?=$dizionario['generale']['pax_rimborsati']?></td>
								<td><?=$dizionario['generale']['importo']?></td>
							</tr>
						<?
						
						$prenotazioni = array();
						if (isset($_SESSION['PRENOTAZIONE_MULTI'])) {
							$prenotazioni = $_SESSION['PRENOTAZIONE_MULTI'];
						} else {
							$sql = "SELECT * FROM RT_Prenotazione p  WHERE p.CodicePrenotazione = '" . $DatiGeneraliArr['CodicePrenotazione'] . "' AND PrenotazioneStato  = " . $DatiGeneraliArr['PrenotazioneStato'] . " AND Cancella = 0";
							$prenotazioni = $db->fetch_array($sql);
						}
						
						$totale = 0;
						foreach ($prenotazioni as $prenotazione) {
							//$sql = "SELECT pd.DataPartenza, pd.OrarioPartenza, pd.ComunePartenza, pd.DataArrivo, pd.OrarioArrivo, pd.ComuneArrivo, pd.TipoViaggio, COUNT(*) TotalePaxPrenotati, SUM(pd.Importo) TotaleImporto, p.PrenotazioneId FROM RT_PrenotazioneDettaglio pd LEFT JOIN RT_Prenotazione p ON (p.PrenotazioneId = pd.PrenotazioneId) WHERE pd.Rimborso = 0 AND pd.PrenotazioneId = " . $prenotazione['PrenotazioneId'];
							$sql = "SELECT 
							pd.DataPartenza, pd.OrarioPartenza, pd.ComunePartenza, pd.DataArrivo, pd.OrarioArrivo, pd.ComuneArrivo, pd.TipoViaggio, pd.Tragitto, COUNT(*) TotalePaxPrenotati, SUM(pd.Importo) TotaleImporto, p.PrenotazioneId 
							FROM RT_PrenotazioneDettaglio pd 
							LEFT JOIN RT_Prenotazione p ON (p.PrenotazioneId = pd.PrenotazioneId) 
							WHERE pd.Rimborso = 0 AND pd.PrenotazioneId = " . $prenotazione['PrenotazioneId'] . "
							GROUP BY Tragitto";
							$rows = $db->fetch_array($sql);
							
							foreach ($rows as $row) {
								$sql = "SELECT COUNT(*) TotalePaxRimborsati, SUM(pd.Importo) TotaleImporto, p.PrenotazioneId 
								FROM RT_PrenotazioneDettaglio pd 
								LEFT JOIN RT_Prenotazione p ON (p.PrenotazioneId = pd.PrenotazioneId) 
								WHERE pd.Rimborso = 1 AND pd.Tragitto = '" . $row['Tragitto'] . "' AND pd.PrenotazioneId = " . $prenotazione['PrenotazioneId'];
								
								$rowsRimborsati = $db->query_first($sql);								
								
								if (count($rows) >= 2) {
									$totalePrenotazione = floatval($row['TotaleImporto']) / 2;
								} else {
									$totalePrenotazione = floatval($row['TotaleImporto']) / intval($row['TotalePaxPrenotati']);
								}
								$totalePaxPrenotati = intval($row['TotalePaxPrenotati']);
								$totalePaxRimborsati = intval($rowsRimborsati['TotalePaxRimborsati']);
								$totaleRiga = ($totalePaxPrenotati - $totalePaxRimborsati) * $totalePrenotazione;
								$totale += $totaleRiga;
								
								$totaleRiga = number_format($totaleRiga, 2, ",", ".");							

								echo "<tr class=\"rowBianca\">";
								echo "	<td>" . $dt->format($row['DataPartenza'], "Y-m-d", "d/m/Y") . " " . $dt->format($row['OrarioPartenza'], "H:i:s", "H:i") . "</td>";
								echo "	<td>" . $row['ComunePartenza'] . "</td>";
								echo "	<td>" . $dt->format($row['DataArrivo'], "Y-m-d", "d/m/Y") . " " . $dt->format($row['OrarioArrivo'], "H:i:s", "H:i") . "</td>";
								echo "	<td>" . $row['ComuneArrivo'] . "</td>";
								echo "	<td>" . $row['Tragitto'] . "</td>";
								echo "	<td>" . $totalePaxPrenotati . "</td>";
								echo "	<td>" . $totalePaxRimborsati . "</td>";
								echo "	<td>" . $totaleRiga . "</td>";
								echo "</tr>";
							}
						}
						?>
							<tr>
								<td colspan="7" style="text-align: right;"><?=$dizionario['generale']['totale_upper']?></td>
								<td><?= number_format($totale, 2, ",", "."); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
				
				<br style="clear: both;" />
			<?php } ?>    
	   			 	
			<br style="clear: both;" />
			
			<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['scelta_corse']?></h2>
			<div class="brain_data-content">
			<?php
				form_tipo1($azione);
			?>
			</div>
			
			<br style="clear: both;" />
			
			</div>
				<?php
					if ( ($StatoPrenotazioneId!=4) and  ($StatoPrenotazioneId!=6) ){
						spara_pulsanti_wizard(0);
					} elseif(($StatoPrenotazioneId==4) && ($user->SedeLegale==1) && ($ripristinabile==true)  ) {
						spara_pulsante_ripristina_web();
					}
				?>
			</form>
			<?
					$db->Close();
}

function form_tipo1($azione)
{
	global $HtmlCommon, $db,$user, $prenotazione_wizard, $dizionario;

	$page=new Form();
	$dt=new DT();

	$arr_viaggio = array();
	$arr_viaggio[]= array("ViaggioId" => '1',"Viaggio" => $dizionario['biglietto']['solo_andata']);
	$arr_viaggio[]= array("ViaggioId" => '2',"Viaggio" => $dizionario['biglietto']['andata_ritorno']);

	$arr_tour = array();
	$arr_tour[]= array("TipoTourId" => '0',"TipoTour" => $dizionario['generale']['tour_gruppo']);
	$arr_tour[]= array("TipoTourId" => '1',"TipoTour" => $dizionario['generale']['tour_privato']);
	
	$arr_tipo = array();
	$arr_tipo[]= array("TipoPrenotazioneId" => '0',"TipoPrenotazione" => $dizionario['biglietto']['tipo_prenotazione_standard']);
	$arr_tipo[]= array("TipoPrenotazioneId" => '1',"TipoPrenotazione" => $dizionario['biglietto']['tipo_prenotazione_libera']);
	
	$flotta = new Flotta();
    $flotta->conn = $db;
    $arr_barca = $flotta->getAllForSelectModel();
	
	$TrattaTipo=new TrattaTipo();
	$TrattaTipo->conn=$db;
	$arr_tratta_tipo=$TrattaTipo->getAll();

	$Corsa=new Corsa();
	$Corsa->conn=$db;
	$arr_partenze = $Corsa->getAllFermatePartenza(true);
	$arr_arrivi = $Corsa->getAllFermateArrivo(true);

	$TrattaDirezione=new TrattaDirezione();
	$TrattaDirezione->conn=$db;
	$arr_tratta_direzione=$TrattaDirezione->getAll();

	$TrattaNome="";
	$PercorsoPeso="";
	$PercorsoStato=0;

	$RitornoOpen=0;
	$arr_sesso = array();
	$arr_sesso[]= array("SessoId" => '1',"Sesso" => $dizionario['generale']['signore']);
	$arr_sesso[]= array("SessoId" => '2',"Sesso" => $dizionario['generale']['signora']);
	$arr_sesso[]= array("SessoId" => '3',"Sesso" => $dizionario['generale']['nd']);

	// Select Assicurazione Si/No
	$arr_assicurazione = array(
		array("AssicurazioneId" => '1', "AssicurazioneLabel" => 'Si'),
		array("AssicurazioneId" => '0', "AssicurazioneLabel" => 'No')
	);
	
	$prefissoObj = new PrefissoTelefono($db);
	$arr_prefisso = $prefissoObj->getAllForSelect();
	$modPre=0;
	$filtro_libera_barca = 12;

	$assicurazione = 0;
	if ($azione=="edit") {
		$modPre=1;

		?>
		<script type="text/javascript">
		$(document).ready(function() {
			MostraElencoCorse();
		});
		</script>
		<?php
		$DatiGeneraliArr=$prenotazione_wizard->DatiGenerali;
		$NomeCliente=$DatiGeneraliArr['ClienteNome'];
		$CellularePrefissoCliente=$DatiGeneraliArr['ClienteCellularePrefisso'];
		$CellulareCliente=$DatiGeneraliArr['ClienteCellulare'];
		$RitornoOpen=$DatiGeneraliArr['RitornoOpen'];
		$ClienteEmail=$DatiGeneraliArr['ClienteEmail'];
		$Lingua = $DatiGeneraliArr['Lingua'];
		$CellulareFamiliare=$DatiGeneraliArr['ClienteCellulareFamiliare'];
		$SessoIdCliente=$DatiGeneraliArr['ClienteSessoId'];
		$TipoViaggioId=$DatiGeneraliArr['TipoViaggioId'];
		$TipoTour=$DatiGeneraliArr['TipoTour'];
		$TipoPrenotazione = $DatiGeneraliArr['Libera'];
		$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
		$DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso;
		$ComuneSalitaId=$DatiGeneraliPercorsoArr['ComuneSalitaId'];
		$ComuneDiscesaId=$DatiGeneraliPercorsoArr['ComuneDiscesaId'];
		$FermataSalitaAId=$DatiGeneraliPercorsoArr['FermataSalitaId'];
		$FermataDiscesaAId=$DatiGeneraliPercorsoArr['FermataDiscesaId'];
		$statoPrenotazione = $DatiGeneraliArr['PrenotazioneStato'];
		$assicurazione = $DatiGeneraliArr['Assicurazione'];

		$CorsaAndataId=$DatiGeneraliPercorsoArr['CorsaId'];
		$CorsaDataAndata=$DatiGeneraliPercorsoArr['CorsaDataPartenza'];
		$dt=new DT($CorsaDataAndata,'Y-m-d');
		$filtro_andata=$dt->getDate('d/m/Y');
		$filtro_libera_andata=$dt->getDate('d/m/Y');
		$filtro_libera_orario_partenza = substr($DatiGeneraliPercorsoArr['DataOraSalita'], 11, 5); 
		$filtro_libera_orario_arrivo = substr($DatiGeneraliPercorsoArr['DataOraDiscesa'], 11, 5);
		$filtro_libera_titolo = $DatiGeneraliArr['LiberaTitolo'];
		$sql = "SELECT * FROM RT_Corsa where CorsaId = $CorsaAndataId";

		$rowC = $db->query_first($sql);
		$filtro_libera_barca = $rowC['FlottaDefaultId'];

		$prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
		$DatiGeneraliPercorsoArr=$prenotazione_wizard->DatiGeneraliPercorso;
		$CorsaRitornoId=$DatiGeneraliPercorsoArr['CorsaId'];
		$CorsaDataRitorno=$DatiGeneraliPercorsoArr['CorsaDataPartenza'];
		if($RitornoOpen == 0){
			$dt=new DT($CorsaDataRitorno,'Y-m-d');
		} else {
			$dt=new DT($CorsaDataAndata,'Y-m-d');
		}
		$filtro_ritorno=$dt->getDate('d/m/Y');
		$azione = "update";
	}else{
            $modPre=0;
		if (isset($_SESSION['PRENOTAZIONE_MULTI'])) {
			$prenotazione_multi = $_SESSION['PRENOTAZIONE_MULTI'];
			$NomeCliente = $prenotazione_multi[0]['ClienteNome'];
			$SessoIdCliente = $prenotazione_multi[0]['ClienteSessoId'];
			$CellularePrefissoCliente = $prenotazione_multi[0]['ClienteCellularePrefisso'];
			$CellulareCliente = $prenotazione_multi[0]['ClienteCellulare'];
			$CellulareFamiliare = $prenotazione_multi[0]['ClienteCellulareFamiliare'];
			$ClienteEmail = $prenotazione_multi[0]['ClienteEmail'];
		} else {
			$prenotazione_multi = null;
			$NomeCliente = null;
			$SessoIdCliente = null;
			$CellularePrefissoCliente = null;
			$CellulareCliente = null;
			$CellulareFamiliare = null;
			$ClienteEmail = null;
		}
		$TipoViaggioId = null;
		$TipoTour = null;
		$TipoPrenotazione = 0;
		$RitornoOpen = 0;
		$ComuneSalitaId = null;
		$ComuneDiscesaId = null;
		$FermataSalitaAId = null;
		$FermataDiscesaAId = null;
		$statoPrenotazione = null;
		$CorsaAndataId = null;
		$CorsaDataAndata = null;
		$CorsaRitornoId = null;
		$CorsaDataRitorno = null;
		if($user->GestoreId == 1) {
			$assicurazione = 1; 
		} else {
			$assicurazione = 0;
		}

		$azione="create";
	}
	
	if(($statoPrenotazione==4) && ($user->SedeLegale==1)) {
	    $azione="ripristina_web";
	}

	$page->create_textbox_hidden("action",$azione);
	?>
	<input type="hidden"
	name="CorsaSelezionataA" id="CorsaSelezionataA"
	value="<?=$CorsaAndataId?>" />
	<input type="hidden"
	name="CorsaSelezionataR" id="CorsaSelezionataR"
	value="<?=$CorsaRitornoId?>" />
	<input type="hidden" name="RichiestaCofermaErrori"
	id="RichiestaCofermaErrori" value="1" />
	<input type="hidden"
	name="DataSelezionataA" id="DataSelezionataA"
	value="<?=$CorsaDataAndata?>" />
	<input type="hidden"
	name="DataSelezionataR" id="DataSelezionataR"
	value="<?=$CorsaDataRitorno?>" />
        
	<input type="hidden" name="modPre" id="modPre" value="<?=$modPre?>" />
        
        
	<?php
			
	if ($modPre==1) {
		?> 
			<input type="hidden" name="modificaData" id="modificaData" value="0" />
			<input type="hidden" name="modificaDataRitorno" id="modificaDataRitorno" value="0" />
			<input type="hidden" name="modificaNominativo" id="modificaNominativo" value="0" />
			<input type="hidden" name="modificaItinerario" id="modificaItinerario" value="0" />
		<?php 
	}
			
	$style1 = '';
	if($modPre==1 && $statoPrenotazione == 3){
		$arr_viaggio = array();
		if($TipoTour == 0){
			$arr_viaggio[]= array("ViaggioId" => '1',"Viaggio" => $dizionario['biglietto']['solo_andata']);
			$arr_viaggio[]= array("ViaggioId" => '2',"Viaggio" => $dizionario['biglietto']['andata_ritorno']);
		} else {
			$arr_viaggio[]= array("ViaggioId" => '1',"Viaggio" => $dizionario['biglietto']['solo_andata']);
		}
		
		$arr_tour = array();
		if($TipoTour == 0){
			$arr_tour[]= array("TipoTourId" => '0',"TipoTour" => $dizionario['generale']['tour_gruppo']);
		} else {
			$arr_tour[]= array("TipoTourId" => '1',"TipoTour" => $dizionario['generale']['tour_privato']);
		}
		
		$arr_tipo = array(); 
		$arr_tipo[] = array("TipoPrenotazioneId" => '0',"TipoPrenotazione" => $dizionario['biglietto']['tipo_prenotazione_standard']);
		$arr_tipo[]= array("TipoPrenotazioneId" => '1',"TipoPrenotazione" => $dizionario['biglietto']['tipo_prenotazione_libera']);
					
		$style = array(
			"class"=>"'required'",
			"onChange"=>"'javascript:MostraElencoCorse();'", "readonly" => "readonly"
			);
		$styleTipoViaggio = $style1 = array(
			"class"=>"'required'",
			"readonly" => "readonly"
			);
	} else {
		$style = array(
			"class"=>"'required'",
			"onChange"=>"'javascript:MostraElencoCorse();'");
		$style1 = $style = array(
			"class"=>"'required'");
		if($modPre==1 && $statoPrenotazione == 1){
			if($TipoTour == 1) {
				$styleTipoViaggio = array(
					"class"=>"'required'",
					"readonly" => "readonly",
					"disabled" => "disabled"
				);
			}
		}
		
		
	}
			
	$page->create_select($dizionario['biglietto']['partenza'],"Biglietto[PartenzaId]","PartenzaId","brain_campiform",$arr_partenze,$ComuneSalitaId,"ComuneId","Comune",  array(
			"class"=>"'required'",
			"onChange"=>"'javascript:MostraPossibiliDestinazioni(this);MostraElencoCorse();'"
			),1);


	$page->create_select($dizionario['biglietto']['destinazione'],"Biglietto[DestinazioneId]","DestinazioneId","brain_campiform",$arr_arrivi,$ComuneDiscesaId,"ComuneId","Comune",array(
			"class"=>"'required'",
			"onChange"=>"'javascript:MostraElencoCorse();'"
			),1);
			
	print("<input type='hidden' name='Prenotazione[TipoViaggioId]' id='TipoViaggioId' value='".((isset($TipoViaggioId)) ? $TipoViaggioId : '1')."'>");
	$page->create_select($dizionario['generale']['tipo_tour'],"Prenotazione[TipoTour]","TipoTour","brain_campiform",$arr_tour,(isset($TipoTour))? $TipoTour : '',"TipoTourId","TipoTour",$style1,1);
	if (!isset($styleTipoViaggio)) {
		$styleTipoViaggio = array("class"=>"'required'");
	}
	$page->create_select($dizionario['biglietto']['tipo_viaggio'],"TipoViaggioIdSelect","TipoViaggioIdSelect","brain_campiform",$arr_viaggio,(isset($TipoViaggioId))? $TipoViaggioId : 0,"ViaggioId","Viaggio", $styleTipoViaggio,1);
	?>
	<div class="libera-block" <?= ($modPre==1 && $TipoTour) ? '' : 'style="display:none;"'?>> 
		<?php
		print("<br style=\"clear:both;\"/>");
		print("<br style=\"clear:both;\"/>");
		if ( ($user->GestoreId==1) || ($user->GestoreId==2) ) {
			$options = array(
				"class"=>"'required'",
				"onChange"=>"'javascript:MostraElencoCorse();'",
			);	
		} else {
			$options = array(
				"class"=>"'required'",
				"onChange"=>"'javascript:MostraElencoCorse();'",
				"disabled" => "disabled"
			);
		}
		
		$page->create_select($dizionario['biglietto']['tipo_prenotazione'],"Prenotazione[Libera]","Libera","brain_campiform",$arr_tipo,(isset($TipoPrenotazione))? $TipoPrenotazione : '',"TipoPrenotazioneId","TipoPrenotazione", $options, 1);
		?>
	</div>
	<?php		
	print("<br style=\"clear:both;\"/>");
	print("<br style=\"clear:both;\"/>");
	print("<br style=\"clear:both;\"/>");
	?>

	<div id="ElencoCorseA"
		<?php if($azione=="create") echo('style="display:none;"'); ?>>
		<h1><?=$dizionario['biglietto']['selezione_andata']?></h1>
		<div class="elencoContainerTabella">
		<?php
				print("<br style=\"clear:both;\"/>");
		$page->create_textbox($dizionario['biglietto']['ricerca_andata'],"RicercaDaId","Controlli[RicercaDaId]",(isset($filtro_andata))? $filtro_andata : date('d/m/Y'),1,"brain_campoForm",array("class"=>"'required italianDate'",
				"onChange"=>"'javascript:MostraElencoCorseAR(\"A\");'"),"","10");
		print("<br style=\"clear:both;\"/>");
		
		?>

		<div id="ElencoA"></div>
		<?php
				print("<br style=\"clear:both;\"/>");
		?>
		</div>
		<!-- elencoContainerTabella -->
	</div>

	<div id="ElencoCorseR" style="display: none;">
		<h1><?=$dizionario['biglietto']['selezione_ritorno']?></h1>
		<div class="elencoContainerTabella">
		
			<?php 
			//checkbox di ritorno aperto
			if(false) { ?>
			
				<input id = "CorsaRitornoAperto" name="CorsaRitornoAperto" type="checkbox" value="true" <?php if(isset($RitornoOpen) && $RitornoOpen==1){ echo  "checked='checked'";}?>/> <label id="label_CorsaRitornoAperto" for="CorsaRitornoAperto">Ritorno Aperto</label>
			
			<?php } ?>
			<?php
					print("<br style=\"clear:both;\"/>");
			$page->create_textbox($dizionario['biglietto']['ricerca_ritorno'],"RicercaAId","Controlli[RicercaAId]",(isset($filtro_ritorno))? $filtro_ritorno : date('d/m/Y'),1,"brain_campoForm",array("class"=>"'required italianDate'",
					"onChange"=>"'javascript:MostraElencoCorseAR(\"R\");'"),"","10");
			
			$arr_is[]= array("ArrIsId" => '0',"ArrIs" => $dizionario['generale']['no']);
			$arr_is[]= array("ArrIsId" => '1',"ArrIs" => $dizionario['generale']['si']);
			$page->create_textbox_hidden("Prenotazione[RitornoOpen]",(isset($RitornoOpen) && $RitornoOpen==1)?$RitornoOpen:0);
							/*
			$page->create_select("Ritorno Open","Prenotazione[RitornoOpen]","RitornoOpen","brain_campoForm",$arr_is,$RitornoOpen,"ArrIsId","ArrIs",
					array("class"=>"'required'"),1);*/
			?>
			<div id="ElencoR"></div>
			<?php
					print("<br style=\"clear:both;\"/>");
			?>
		</div>
		<!-- elencoContainerTabella -->
	</div>
	<div id="ElencoCorseL" style="display: none;">
		<h1><?=$dizionario['biglietto']['selezione_libera']?></h1>
		<div class="corsaLiberaAndata">
			<input type="hidden" name="filtro_libera_orario_partenza" id="filtro_libera_orario_partenza" value="<?=$filtro_libera_orario_partenza?>" />
			<input type="hidden" name="filtro_libera_orario_arrivo" id="filtro_libera_orario_arrivo" value="<?=$filtro_libera_orario_arrivo?>" /> 		
			<?= $page->create_textbox($dizionario['biglietto']['libera_data_andata'],"DataPartenzaLibera","DataPartenzaLibera",(isset($filtro_libera_andata))? $filtro_libera_andata : date('d/m/Y'), 1, "brain_campoForm", array("class"=>"'required italianDate'", "onChange" => "'javascript:MostraTipoBiglietti(\"A\");'"), "", "10") ?>
			<?= $page->create_textbox($dizionario['biglietto']['libera_titolo'],"TitoloLibera","TitoloLibera",(isset($filtro_libera_titolo))? $filtro_libera_titolo : "", 1, "brain_campoForm", array("class"=>"'required'"), "", "40") ?>
			<?= $page->create_textbox($dizionario['biglietto']['libera_orario_partenza'],"OrarioPartenzaLibera","OrarioPartenzaLibera",(isset($filtro_libera_orario_partenza))? $filtro_libera_orario_partenza : "2", 1, "brain_campoForm", array("class"=>"'required Orario'"), "", "10") ?>
			<?= $page->create_textbox($dizionario['biglietto']['libera_orario_destinazione'],"OrarioArrivoLibera","OrarioArrivoLibera",(isset($filtro_libera_orario_arrivo))? $filtro_libera_orario_arrivo : "2", 1, "brain_campoForm", array("class"=>"'required Orario'"), "", "10") ?>
			<?= $page->create_select($dizionario['generale']['flotta'],"BarcaLibera","BarcaLibera","brain_campoForm",$arr_barca,(isset($filtro_libera_barca) && $filtro_libera_barca != '')? $filtro_libera_barca : '',"FlottaId","Modello", array(
				"class"=>"'required'",
				),1);
			?>
		</div>
	</div>
	<div id="InfoFermateA"
		<?php if($azione=="create") echo('style="display:none;"'); ?>>
			<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['fermate_salita_discesa']?></h2>
			<div class="info"></div>

		<?php  print("<div style=\"clear:both;\"/>");?>
	</div>

	<div id="InfoFermateR" style="display: none;">
		<div class="info"></div>
		<?php  print("<br style=\"clear:both;\"/>"); ?>
	</div>

	<div id="InfoBiglietti"
			<?php if($azione=="create") echo('style="display:none;"'); ?>>
				<?php  print("<br style=\"clear:both;\"/>");?>
				<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['biglietti_pax']?></h2>

				<div id="TipologiaBiglietti">
					<!-- seleziono tutte le tipologie biglietti associate alla corsa -->
				</div>
	</div>

	<div id="InfoPasseggero">
		<?php  print("<br style=\"clear:both;\"/>"); ?>
		<?php  print("<br style=\"clear:both;\"/>"); ?>
		<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['info_passeggeri']?></h2>
		
		<!-- Tabella passeggeri -->
		<div id="passeggeri" class="brain_rowAll"></div>
			
		<?php
			$nasconti_campi_passeggero = "";
			
			//$page->create_textbox_hidden("Prenotazione[ClienteNome]", $NomeCliente);
			$page->create_textbox_hidden("Prenotazione[ClienteSessoId]", isset($SessoIdCliente)? $SessoIdCliente : 0);
			
			$page->create_textbox($dizionario['biglietto']['nome'],"Nome","Prenotazione[ClienteNome]",$NomeCliente,1,"brain_campoForm brain_4_input_inline",array("class"=>"'required'"));
			//$page->create_select("Sesso:","Prenotazione[ClienteSessoId]","SessoId","brain_campoForm",$arr_sesso,(isset($SessoIdCliente))? $SessoIdCliente : 0,"SessoId","Sesso",array("class"=>"'required'"),1);
			print("<br style=\"clear:both;\"/>");
			$page->create_select($dizionario['biglietto']['prefisso_cell'],"Prenotazione[ClienteCellularePrefisso]","CellularePrefisso","brain_campoForm brain_3_input_inline",$arr_prefisso,(isset($CellularePrefissoCliente))? $CellularePrefissoCliente : 0,"Prefisso","Descrizione",array("class"=>"'required select2'"),1);
			$page->create_textbox($dizionario['biglietto']['cell'],"Cellulare","Prenotazione[ClienteCellulare]",$CellulareCliente,1,"brain_campoForm brain_4_input_inline",array("class"=>"'phone'"));
			$page->create_textbox($dizionario['biglietto']['tel_familiare'],"TelFamiliare","Prenotazione[ClienteCellulareFamiliare]",$CellulareFamiliare,0,"brain_campoForm brain_4_input_inline",array("class"=>"'phone'"));
			print("<br style=\"clear:both;\"/>");
			$page->create_textbox($dizionario['biglietto']['email'],"ClienteEmail","Prenotazione[ClienteEmail]",$ClienteEmail,0,"brain_campoForm brain_4_input_inline",array("class"=>"'mail'"));
			echo "<div class='brain_campoForm brain_4_input_inline' style='padding-top: 40px;'>";
			echo "<i><small>(Lingua del cliente: ".$dizionario['generale'][$Lingua].")</small></i>";
			echo "</div>";
			print("<br style=\"clear:both;\"/>");
			?>
			Attiva l'assicurazione: sar&agrave; emessa solo se il tour selezionato prevede l'assicurazione, la partenza è consecutiva ad oggi e la durata totale del tour (inclusa eventuale sosta) supera le 2 ore
			<?php
			print("<br style=\"clear:both;\"/>");
			// select per assicurazione YOLO
			$val_assicurazione = isset($DatiGeneraliArr['Assicurazione']) ? $DatiGeneraliArr['Assicurazione'] : 0;
			$page->create_select(
				'Attiva assicurazione',
				'Prenotazione[Assicurazione]',
				'Assicurazione',
				'brain_campoForm brain_4_input_inline',
				$arr_assicurazione,
				$val_assicurazione,
				'AssicurazioneId',
				'AssicurazioneLabel',
				array("class" => "'required'"),
				1
			);
        
		?>
	</div>

	<div id="SelezionePosti" style="display: none;">
		<?  print("<br style=\"clear:both;\"/>"); ?>
		<?  print("<br style=\"clear:both;\"/>"); ?>
		<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['selezione_posti']?></h2>
		<div id="SelezionaPostiA"></div>
	</div>

	<div id="NotePerTratta" style="display: none;">
		<?  print("<br style=\"clear:both;\"/>"); ?>
		<?  print("<br style=\"clear:both;\"/>"); ?>
		<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['note_tratta']?></h2>
		<div id="ElencoNoteAndata"></div>
	</div>

	<div id="elenco_comuni"></div>
	<?php print("<br style=\"clear:both;\"/>");
				
}

function quadratura_postviaggio($PrenotazioneId)
{

	global $prenotazione_wizard,$db,$user;
	$prenotazione_wizard=new Prenotazione($PrenotazioneId);

	$_SESSION['PRENOTAZIONE_WIZARD']=serialize($prenotazione_wizard);
	add(1);
}

function carica_menu_prenotazione($step_corrente,$mod) {
	global $abilita_modifica,$tratta_wizard,$db, $dizionario;
	//$tratta_wizard->conn=$db;
	//$menu=$tratta_wizard->getMenuWizard();


	$menu=array(
			1=>$dizionario['biglietto']['menu_prenotazione'],
			2=>$dizionario['biglietto']['menu_pagamenti'],
			3=>$dizionario['biglietto']['menu_titoli'],
			4=>$dizionario['biglietto']['whatsapp']
			);
	$CorsaId=0;
	if (isset($_REQUEST['CorsaId']))
		$CorsaId=$_REQUEST['CorsaId'];

	?>
	<div id="brain_mediazionemenuverticale" class="brain_tabVerticale">
	<ul>
	<?php
	$contamenu=1;
	while ($contamenu<=4) {
		$class1="";
		$class2="";

		if ($contamenu==$step_corrente) {
			$class1="sel";
			$class2="brain_firstspan sel";
		}

		$StatoStep="";

		if ( ($contamenu<=4) or (($contamenu>4) and ($mod))) { ?>

		<li class="<?=$class1?>"><span class="<?=$class2?>">
			<?php if ($mod) { ?>
				<a href="javascript:void(0);"
					onclick="loadMediazioneStep('rt_biglietto','biglietto.php?CorsaId=<?=$CorsaId?>&do=add&step=<?=$contamenu?>',this);"
					title="<?=$menu[$contamenu]?>"><?=$menu[$contamenu]." ".$StatoStep?>
				</a> 
			<?php } else {
					echo($menu[$contamenu]);
			}?>
			</span>
		</li>
		<?php }
		$contamenu++;
	}
	?>
	</ul>
	</div>
	<?php
}

function elenco_titoli_viaggio() {
	$step_corrente=3;

	global $prenotazione_wizard,$user,$db, $dizionario;

	$page=new Form();
	$dt=new DT();

	$azione="add";
	$action="create";

	$PrenotazioneId=0;
	$titoli=0;
	$CorsaId=0;
	$DataCorsa="";
	$StatoPrenotazione = 0;
	if (is_object($prenotazione_wizard) and ($prenotazione_wizard->Id))
	{
		$PrenotazioneId = $prenotazione_wizard->Id;
		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		$StatoPrenotazione = $DatiGeneraliArr['PrenotazioneStato'];
		$titoli = $prenotazione_wizard->TitoliEmessiYesNo();
	}
	
	if($StatoPrenotazione == 6){
		echo "<h2>".$dizionario['generale']['no_biglietti']."</h2>";
	} else {
		?>
		<div id="printing"></div>
		<form id="application_form" name="application_form" method="post"
		action="#">
	
		<?php
		// spara_pulsanti_wizard(0);
		$page->create_textbox_hidden("step_corrente",$step_corrente);
		$page->create_textbox_hidden("step_successivo",$step_corrente+1);
		?>
				
		<div class="brain_formModifica">
	
		<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['menu_titoli']?></h2>
	
		<div class="brain_data-content">
	
		<?php
	
			if (!$titoli) {
				/*
				if ($DatiGeneraliArr['Pagato']) {
					$page->create_button("EmettiTitoliDiViaggio","EmettiTitoliDiViaggio","Emetti Biglietto","brain_salva","button");
				}
				*/
				print("<br style=\"clear:both;\"/>");

				echo("<p>".$dizionario['biglietto']['no_biglietto']);
			    
				if($user->IsAdmin && $DatiGeneraliArr['PrenotazioneStato'] == 1) { ?>	
				<br><a target="_new" class="icona_stampa" href="/protected/modules/rt_previaggio/stampa_voucher_di_viaggio.php?Tipo=Prenotazione&PrenotazioneId=<?=$PrenotazioneId?>&seller_type=1"><img src="../images/print-icon.png" alt="Stampa"/> <?=$dizionario['pre']['voucher_titolo']?></a>
            	<?php 
				}
			} else {
				if (!$DatiGeneraliArr['Multi']) {
					$sql = "SELECT * FROM RT_PrenotazioneTitolo 
							WHERE PrenotazioneId = $PrenotazioneId 
							AND OdcIdRef = $user->OdcId 
							AND Stato = 1 
							AND Cancella = 0 
							ORDER BY PrenotazioneTitoloId";
				} else {
					$sql = "SELECT pt.* FROM RT_Prenotazione p 
							LEFT JOIN RT_PrenotazioneTitolo pt ON (p.PrenotazioneId = pt.PrenotazioneId) 
							WHERE p.CodicePrenotazione = '" . $DatiGeneraliArr['CodicePrenotazione'] . "' 
							AND pt.OdcIdRef = 1 
							AND pt.Stato = 1 
							AND pt.Cancella = 0 
							ORDER BY pt.PrenotazioneTitoloId";
				}
				
				$ArrObject = $db->fetch_array($sql);
				
				$conta = 0;
				?>
				<h3><?=$dizionario['biglietto']['elenco_biglietti_viaggio']?></h3>
				<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
					<tbody>
						<tr class="rowIntestazione">
							<td><?=$dizionario['generale']['tipo']?></td>
							<td><?=$dizionario['biglietto']['codice_anno']?></td>
							<td><?=$dizionario['biglietto']['dettaglio_operaizone']?></td>
							<td><?=$dizionario['generale']['stampa']?></td>
							<td><?=$dizionario['generale']['rimborsa']?></td>
						</tr>
						<?php
						while($conta < sizeof($ArrObject))
						{
							$PrenotazioneObj = new Prenotazione($ArrObject[$conta]['PrenotazioneId']);
							$PrenotazioneObj->conn = $db;
							$PrenotazioneObj->inizializzaDatiGeneraliPercorso('A');
							$DatiGeneraliPercorsoArr = $PrenotazioneObj->DatiGeneraliPercorso;
							$PrenotazioneObj->inizializzaDatiGeneraliPercorso('R');
							$DatiGeneraliPercorsoArrRit = $PrenotazioneObj->DatiGeneraliPercorso;
							
							$PrenotazioneId = $ArrObject[$conta]['PrenotazioneId'];
							$TipoTitolo = $ArrObject[$conta]['TipoTitolo'];
							$CodiceTitolo = $ArrObject[$conta]['Codice'];
							$Anno = $ArrObject[$conta]['Anno'];
							$DataIns = $ArrObject[$conta]['DataIns'];
							$Titoloid = $ArrObject[$conta]['PrenotazioneTitoloId'];
							$prenotazioneNumeroId = $ArrObject[$conta]['PrenotazioneNumeroId'];
							$sql = "SELECT Cognome, Nome, GestoreId FROM Operatore WHERE OperatoreId=".$ArrObject[$conta]['OpeIns'];
							$row = $db->query_first($sql);
							$Username = $row['Cognome']." ".$row['Nome'];
							$sql = "SELECT RagioneSociale FROM Gestore WHERE GestoreId=".$row['GestoreId'];
							$row = $db->query_first($sql);
							$AgenziaOperatore = $row['RagioneSociale'];
							$sql = "SELECT RagioneSociale FROM Gestore WHERE GestoreId=".$ArrObject[$conta]['GestoreIdRef'];
							$row = $db->query_first($sql);
							$Agenzia = $row['RagioneSociale'];
							$Passeggero = $DatiGeneraliArr['ClienteNome'];
							if($DatiGeneraliArr['TipoTour'] == 0){
								$TipoBiglietto = $dizionario['generale']['tour_gruppo'];
							} else {
								$TipoBiglietto = $dizionario['generale']['tour_privato'];
							}

							$sql = "SELECT * FROM RT_PrenotazioneDettaglio WHERE PrenotazioneNumero=".$ArrObject[$conta]['PrenotazioneNumeroId'];
							$rows = $db->fetch_array($sql);
							
							$sql = "select * from RT_PrenotazioneTitolo t
        							left join RT_PrenotazioneNumero n on (t.PrenotazioneNumeroId = n.PrenotazioneNumeroId)
        							left join RT_TipologiaBiglietto b on (b.TipologiaBigliettoId = n.TipologiaBigliettoId)
        							where t.PrenotazioneTitoloId = $Titoloid";
							
							$rowsOccupa = $db->query_first($sql);
							if($TipoTitolo == 'E' || ($TipoTitolo == 'R' && $rowsOccupa['OccupaPosto']==0)|| ($TipoTitolo == 'R' && $rowsOccupa['OccupaPosto']==1 && (strpos($CodiceTitolo, 'E-') != 0 || strpos($CodiceTitolo, 'E-') == ""))){
								$DettaglioOperazione = $dizionario['biglietto']['dett_passeggero']." $Passeggero<br/>".
														$dizionario['generale']['tipo_tour'].": $TipoBiglietto<br/>";
								
							} else {
								$DettaglioOperazione = $dizionario['tipo_big']['pagamento_extra']."<br/>";
							}
							$if_primo_row = true;
                            $DataCorsaAndata="";
                                                        
                            // print_r($DatiGeneraliPercorsoArr);
                            if($TipoTitolo == 'E' || ($TipoTitolo == 'R' && $rowsOccupa['OccupaPosto']==0)|| ($TipoTitolo == 'R' && $rowsOccupa['OccupaPosto']==1 && (strpos($CodiceTitolo, 'E-') != 0 || strpos($CodiceTitolo, 'E-') == ""))){                            
								//calcolo la sosta prenotata
								$sosta = 0;
								if($DatiGeneraliArr['TipoTour'] == 1){
									$sql = "select * from RT_PrenotazioneBiglietto where TipologiaBigliettoId = 23 AND PrenotazioneId = $PrenotazioneId";
									$sostaRow = $db->query_first($sql);
									if(isset($sostaRow['NumeroPax'])) {
										$sosta = $sostaRow['NumeroPax'];
									}
								}
								
								foreach ($rows as $row) {
									if ($if_primo_row) {
										if ($row['Tragitto'] == 'Ritorno') {
											$CorsaId = $DatiGeneraliPercorsoArrRit['CorsaId'];
											$DataCorsa = $DatiGeneraliPercorsoArrRit['CorsaDataPartenza'];
											$Tragitto = $row['Tragitto'];
										} else {
											$CorsaId = $DatiGeneraliPercorsoArr['CorsaId'];
											$DataCorsa = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];
											$Tragitto = $row['Tragitto'];
	                                        $DataCorsaAndata=$DatiGeneraliPercorsoArr['DataOraSalita'];                                           
										}
										$if_primo_row = false;
									}
									if(Config::$sceltaPostiBus) {
										if($row['Tragitto'] == 'Ritorno'){
											$tempTragitto = 'R';
										} else {
											$tempTragitto = 'A';
										}
										$sql = "SELECT * FROM RT_PrenotazioneTitoloPosto
	  											WHERE PrenotazioneNumeroId = ".$rowsOccupa['PrenotazioneNumeroId']."
										  		AND Ordine = 0 AND TipoViaggio = '$tempTragitto'";
										$rowTemp = $db->query_first($sql);
									}
									
									
									 
									$DettaglioOperazione.="<br/>".$dizionario['biglietto']['dettaglio_viaggio']." " . $row['Tragitto'] ."<br/>";
									if($row['Tragitto'] == 'Ritorno' && $DatiGeneraliArr['RitornoOpen'] == 1){
										$DettaglioOperazione.=$dizionario['biglietto']['dett_partenza']." ".rimuoviParentesi($row['ComunePartenza'])." - Ritorno Aperto<br/>";
										$DettaglioOperazione.=$dizionario['biglietto']['dett_arrivo']." ".rimuoviParentesi($row['ComuneArrivo'])." - Ritorno Aperto<br/>";
									} else {
									    $oraPartenzaT = explode(":",$row['OrarioPartenza']);
									    $oraPartenza = $oraPartenzaT[0].":".$oraPartenzaT[1];
										//sommo la sosta all'orario di arrivo
										$tempOraArrivo = new DateTime($row['OrarioArrivo']);
										$tempOraArrivo->modify("+".$sosta." hours");
										$tempOraArrivoS = $tempOraArrivo->format("H:i:s");
										//fine: orario arrivo
									    $oraArrivoT = explode(":",$tempOraArrivoS); 
									    $oraArrivo = $oraArrivoT[0].":".$oraArrivoT[1];
									    $DettaglioOperazione .= $dizionario['biglietto']['dett_partenza']." ".rimuoviParentesi($row['ComunePartenza'])." - ".$row['DataPartenza']." ".$oraPartenza."<br/>";
									    $DettaglioOperazione .= $dizionario['biglietto']['dett_arrivo']." ".rimuoviParentesi($row['ComuneArrivo'])." - ".$row['DataArrivo']." ".$oraArrivo."<br/>";
									}
									if(Config::$sceltaPostiBus) {
										$DettaglioOperazione .= $dizionario['biglietto']['posto_occupato'].": ".$rowTemp['NumeroPosto']."<br/>";
									}
								}
								
								$DettaglioOperazione .= "<br/>".$dizionario['biglietto']['riepilogo']."<br/>";
								$sql = "SELECT * FROM RT_PrenotazioneBiglietto b
										LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
								 		WHERE PrenotazioneId = $PrenotazioneId and t.OccupaPosto = 1";
								$passeggeriBiglietto = $db->fetch_array($sql);
                            	if(count($passeggeriBiglietto)) {
                            		$DettaglioOperazione .= $dizionario['tipo_big']['passeggeri'].":<br/>";
                            		foreach($passeggeriBiglietto as $p) {
                            			$DettaglioOperazione .="- x".$p['NumeroPax']." ".$p['TipologiaBiglietto']."<br/>";
                            		}
                            	}
                            	
                            	$sql = "SELECT * FROM RT_PrenotazioneBiglietto b
										LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
								 		WHERE PrenotazioneId = $PrenotazioneId and t.OccupaPosto = 0";
                            	$passeggeriBiglietto = $db->fetch_array($sql);
                            	if(count($passeggeriBiglietto)) {
                            		$DettaglioOperazione .= $dizionario['tipo_big']['servizi'].":<br/>";
                            		foreach($passeggeriBiglietto as $p) {
                            			$DettaglioOperazione .="- x".$p['NumeroPax']." ".$p['TipologiaBiglietto']."<br/>";
                            		}
                            	}
                            	
                            }
							
							$DettaglioOperazione .= "<br/>".$dizionario['biglietto']['dett_effettuata']." $DataIns ".$dizionario['biglietto']['dett_da']." $Username ".$dizionario['biglietto']['dett_agenzia']." ".$AgenziaOperatore;
							$DettaglioOperazione .= "<br/>".$dizionario['biglietto']['dett_emessa']." ".$Agenzia;
							
							$seller_type=1;
							if ($user->SedeLegale==1)
								$seller_type=2;
							
							$urlStampa="/protected/modules/rt_previaggio/stampa_titoli_di_viaggio.php?CorsaId=".$CorsaId."&DataPartenza=".$DataCorsa."&PrenotazioneId=".$PrenotazioneId."&PrenotazioneTitoloId=".$Titoloid."&TipoTitolo=".$TipoTitolo."&Tragitto=".$Tragitto;
							$urlStampaCartaceo="/protected/modules/rt_previaggio/stampa_titoli_di_viaggio_cartaceo.php?CorsaId=".$CorsaId."&DataPartenza=".$DataCorsa."&PrenotazioneId=".$PrenotazioneId."&PrenotazioneTitoloId=".$Titoloid."&TipoTitolo=".$TipoTitolo."&Tragitto=".$Tragitto;
							$urlStampaCoupon="/protected/modules/rt_previaggio/stampa_titoli_di_viaggio_coupon.php?CorsaId=".$CorsaId."&DataPartenza=".$DataCorsa."&PrenotazioneId=".$PrenotazioneId."&PrenotazioneTitoloId=".$Titoloid."&TipoTitolo=".$TipoTitolo."&Tragitto=".$Tragitto;
                            //verifico se esiste coupon di sconto
							$sql = "SELECT * FROM RT_Coupon where CouponNome like '%$CodiceTitolo%'";
							$rowCoupon = $db->query_first($sql);
							$isCoupon = false;
							if(isset($rowCoupon['CouponId'])) {
								$isCoupon = true;
							}
							
							if ($TipoTitolo == 'E') {
								$sql="select distinct Tragitto from RT_PrenotazioneDettaglio where PrenotazioneNumero=$prenotazioneNumeroId and Escludi=0";
								$ArrNumeroTragitti = $db->fetch_array($sql);
								$countTragitti = sizeof($ArrNumeroTragitti);
								
								if ($countTragitti > 0) {
									$urlRimborso = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','biglietto.php?do=RimborsoParziale&amp;PrenotazioneTitoloId=".$Titoloid."',this);\" title=\"rimborsa\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"rimborsa\" title=\"rimborsa\"></i></a>";
								} else {
									$urlRimborso = $dizionario['biglietto']['titolo_rimborsato'];
								}
							} else {
								$urlRimborso = "<a href=\"#\" onclick=\"ExternalLoad('rt_biglietto','biglietto.php?do=RimborsoParziale&amp;PrenotazioneTitoloId=".$Titoloid."',this);\" title=\"rimborsa\"><i class=\"fa fa-pencil-square-o edita\" aria-hidden=\"true\" alt=\"rimborsa\" title=\"rimborsa\"></i></a>";
							}
							//gli extra li visualizzo come emessi
							if($TipoTitolo == "X"){
								$TipoTitolo = "E";
							}
							?>
				<tr class="rowBianca">
				<td style="text-align:center;"><?=$TipoTitolo?></td>
				<td style="text-align:center;"><?=$CodiceTitolo?> / <?=$Anno?></td>
				<td><span><?=$DettaglioOperazione?> </span></td>
				<td style="text-align:center;">
					<a href="<?=$urlStampa?>" target="_new" title="stampa titolo"><i class="fa fa-file-pdf-o edita" aria-hidden="true" alt="stampa" title="stampa"></i></a>
					<?php if($TipoTitolo != "R"){ ?>
						<a href="<?=$urlStampaCartaceo?>" target="_new" title="stampa titolo cartaceo"><i class="fa fa-ticket edita" aria-hidden="true" alt="stampa cartaceo" title="stampa cartaceo"></i></a>	
				    <?php } ?>
					<?php if($TipoTitolo == "R" && $isCoupon){ ?>
						<a href="<?=$urlStampaCoupon?>" target="_new" title="stampa coupon"><i class="fa fa-ticket edita" aria-hidden="true" alt="stampa coupon" title="stampa coupon"></i></a>	
				    <?php } ?>
				</td>
				<td style="text-align:center;">
				<?php
				$StatoPrenotazione = $DatiGeneraliArr['PrenotazioneStato'];
				if ($StatoPrenotazione != 6 && $StatoPrenotazione != 4){
					if($TipoTitolo != 'R'){
						$sql = "SELECT TempoMaxAnnullamento FROM RT_Linea WHERE LineaId = ".$row['LineaId'];
						$tempo = $db->query_first($sql);
						
						$sign = new DateTime($DataCorsaAndata);
						$sign->modify('-'.$tempo['TempoMaxAnnullamento'].' hour');		
						$dt = new DateTime();
	                                      
	                   //  echo($DataCorsaAndata);
	
						if($dt < $sign || $urlRimborso == $dizionario['biglietto']['titolo_rimborsato']){
							echo $urlRimborso;
						} else {
							echo $dizionario['biglietto']['non_rimborsabile'];
	                        if ($user->IsAdmin){
	                        	echo $urlRimborso;
	                        }
	                    }
					}
				}
				?>
				</td>
				</tr>
				<?php
					$conta++;
				}	?>
				</tbody>
				</table>
				<?php
			}
			?>
			</div>
		</div>
	</form>
	<?php
	}
}

function messaggi() {
	$step_corrente=4;

	global $prenotazione_wizard,$user,$db, $dizionario;

	$page=new Form();
	$dt=new DT();

	$PrenotazioneId=0;
	if (is_object($prenotazione_wizard) and ($prenotazione_wizard->Id))
	{
		$PrenotazioneId = $prenotazione_wizard->Id;
		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		$CodicePrenotazione = $DatiGeneraliArr['CodicePrenotazione'];
		$TotaleResiduo = $DatiGeneraliArr['TotaleResiduo'];
	}

	?>
	<div id="printing"></div>
	<form id="application_form" name="application_form" method="post"
	action="#">

	<?php
	// spara_pulsanti_wizard(0);
	$page->create_textbox_hidden("step_corrente",$step_corrente);
	$page->create_textbox_hidden("step_successivo",$step_corrente+1);
	?>
	
	
	<div class="brain_formModifica">
	
	<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['whatsapp']?></h2>

	<div class="brain_data-content">

	<h3><?=$dizionario['biglietto']['messaggio_invia']?></h3>
	<?php 
	$script = "'javascript:MessaggioTicket(\"".$CodicePrenotazione."\", \"".$TotaleResiduo."\");'";
	$arr_tipo = array();
	$arr_tipo[] = array('TipoId'=>'ticket', 'Tipo'=>$dizionario['biglietto']['messaggio_tipo_ticket']);
	$arr_tipo[] = array('TipoId'=>'payment', 'Tipo'=>$dizionario['biglietto']['messaggio_tipo_payment']);
	$arr_tipo[] = array('TipoId'=>'payment_postepay', 'Tipo'=>$dizionario['biglietto']['messaggio_tipo_payment_postepay']);
	$arr_tipo[] = array('TipoId'=>'payment_banca5', 'Tipo'=>$dizionario['biglietto']['messaggio_tipo_payment_banca5']);
	$arr_tipo[] = array('TipoId'=>'payment_paypal', 'Tipo'=>$dizionario['biglietto']['messaggio_tipo_payment_paypal']);
	$arr_tipo[] = array('TipoId'=>'payment_intesasanpaolo', 'Tipo'=>$dizionario['biglietto']['messaggio_tipo_payment_intesasanpaolo']);
	$arr_tipo[] = array('TipoId'=>'payment_barzahlen', 'Tipo'=>$dizionario['biglietto']['messaggio_tipo_payment_barzahlen']);
	$arr_tipo[] = array('TipoId'=>'whatsapp', 'Tipo'=>$dizionario['biglietto']['messaggio_tipo_whatsapp']);
	$page->create_select($dizionario['generale']['tipo'],"Tipo","Tipo","brain_campiform",$arr_tipo,$ComuneSalitaId,"TipoId","Tipo", array(
					"class"=>"'required'",
					"onChange"=>$script
					),1);
	print("<br style=\"clear:both;\"/>");
	print("<br style=\"clear:both;\"/>");
	$page->create_textbox_hidden("PrenotazioneId",$PrenotazioneId);
	$page->create_textbox_hidden("action","invioMessaggio");
	$page->create_texarea($dizionario['biglietto']['messaggio'],"Messaggio", "Messaggio", "", 1, "brain_campiform", array(
					"class"=>"'required'",
			"rows"=>"4", "cols"=>"60"
	));
	print("<br style=\"clear:both;\"/>");
	
	$page->create_button($dizionario['biglietto']['messaggio_invia'],"InviaMessaggio",$dizionario['biglietto']['messaggio_invia'],"brain_salva","submit");
	print("<br style=\"clear:both;\"/>");
	?>
	<?php
	//cronologia messaggi 
	$sql = "SELECT m.*, o.Cognome, o.Nome, g.RagioneSociale FROM RT_PrenotazioneMessaggio m
				left join Operatore o on o.OperatoreId = m.OpeIns
				left join Gestore g on g.GestoreId = o.GestoreId
				WHERE m.PrenotazioneId = $PrenotazioneId 
				ORDER BY m.DataIns";
			
	$ArrObject = $db->fetch_array($sql);
			
	$conta = 0;
	?>
	<h3><?=$dizionario['biglietto']['whatsapp_cronologia']?></h3>
	<table width="100%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
		<tbody>
			<tr class="rowIntestazione">
				<td><?=$dizionario['generale']['tipo']?></td>
				<td><?=$dizionario['biglietto']['messaggio']?></td>
				<td><?=$dizionario['biglietto']['data_messaggio']?></td>
				<td><?=$dizionario['operatore']['operatore']?></td>
			</tr>
			<?php while($conta < sizeof($ArrObject)) { 
				$messaggio = $ArrObject[$conta]['Messaggio'];
				$tipo = $ArrObject[$conta]['Canale'];
				$data = date("d/m/Y - H:i", strtotime($ArrObject[$conta]['DataIns']));
				$operatore = $ArrObject[$conta]['Nome']." ".$ArrObject[$conta]['Cognome']." - ".$ArrObject[$conta]['RagioneSociale'];
				?>
			
			<tr class="rowBianca">
				<td><span><?php if($tipo == 'ticket') {
					echo $dizionario['biglietto']['messaggio_tipo_ticket'];
					$messaggio = '<i>'.$dizionario['biglietto']['messaggio_ticket'].'</i>';
				} else if($tipo == 'payment'){
					echo $dizionario['biglietto']['messaggio_tipo_payment'];
				} else {
					echo $dizionario['biglietto']['messaggio_tipo_whatsapp'];
				}?></span></td>
				<td><span><?=$messaggio?></span></td>
				<td><span><?=$data?></span></td>
				<td><span><?=$operatore?> </span></td>
			</tr>
			<?php
				$conta++;
			}	?>
			</tbody>
			</table>
		</div>
	</div>
</form>
<?php

}


function pagamenti()
{
	$step_corrente=2;

	global $prenotazione_wizard, $user, $db, $dizionario;

	$page = new Form();
	$dt = new DT();

	$azione = "add";
	$action = "create";

	$PrenotazioneId = 0;
	$CorsaId = 0;
	$StatoPrenotazione = 0;
	$gestoreIdPrenotazione = 0;
	if (is_object($prenotazione_wizard) and ($prenotazione_wizard->Id))
	{
		$PrenotazioneId = $prenotazione_wizard->Id;
		$prenotazione_wizard->conn = $db;
		$prenotazione_wizard->inizializzaDatiGenerali();
		$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
		
		$CodicePrenotazione = $DatiGeneraliArr['CodicePrenotazione'];
		$StatoPrenotazione = $DatiGeneraliArr['PrenotazioneStato'];
		$gestoreIdPrenotazione = $DatiGeneraliArr['GestoreIdRef'];
		$totaliImporti = $prenotazione_wizard->GetTotaliPrenotazione();
		
		$prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
		$DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
		$CorsaId = $DatiGeneraliPercorsoArr['CorsaId'];
	}

	if($StatoPrenotazione == 6){
		echo "<h2>".$dizionario['generale']['no_movimenti']."</h2>";
	} else {
	
		//$sql="SELECT pm.*, pt.PagamentoTipo FROM RT_PrenotazioneMovimento pm LEFT JOIN RT_PagamentoTipo pt ON (pm.PagamentoTipoId = pt.PagamentoTipoId) WHERE pm.OdcIdRef=$user->OdcId AND pm.PrenotazioneId=$PrenotazioneId AND pm.Cancella=0 ORDER BY Data DESC";
		$sql="SELECT pm.*, pt.PagamentoTipo 
			  FROM RT_Prenotazione p 
			  INNER JOIN RT_PrenotazioneMovimento pm ON (pm.PrenotazioneId = p.PrenotazioneId) 
		      LEFT JOIN RT_PagamentoTipo pt ON (pm.PagamentoTipoId = pt.PagamentoTipoId) 
		      WHERE p.CodicePrenotazione = '$CodicePrenotazione' 
		      AND p.PrenotazioneStato = " . $StatoPrenotazione . " AND p.PrenotazioneId = $PrenotazioneId 
		      AND pm.Stato = 1
			  ORDER BY DataIns DESC";
		$ArrObject = $db->fetch_array($sql);
		
		$sql="SELECT count(*) as MovimentoNoCoupon
			FROM RT_Prenotazione p
			INNER JOIN RT_PrenotazioneMovimento pm ON (pm.PrenotazioneId = p.PrenotazioneId)
			LEFT JOIN RT_PagamentoTipo pt ON (pm.PagamentoTipoId = pt.PagamentoTipoId)
			WHERE p.CodicePrenotazione = '$CodicePrenotazione'
			AND p.PrenotazioneStato = " . $StatoPrenotazione . " AND p.PrenotazioneId = $PrenotazioneId 
			AND pt.EmettiCoupon = 0 AND pm.Cancella = 0 AND pm.Stato = 1";
		$tempCount = $db->fetch_array($sql);
	
		$countMovimenti = $tempCount[0]['MovimentoNoCoupon'];
		
		$sql = "SELECT * FROM Gestore WHERE GestoreId = " . $DatiGeneraliArr['GestoreIdRef'];
		$gestore = $db->query_first($sql);
		//form paypal
		if (($StatoPrenotazione != 3 && !$gestore['Verificato']) || ($StatoPrenotazione == 3 && !$gestore['Verificato'] && $totaliImporti['TotaleResiduo'] > 0)) {
			$url_sito = Config::$httpHost;
			
			if(Config::$pagamentoProvvigioniAgenzie) {
				//recupero provvigione
				$sql = "select PrezzoTotalePax, NumeroPax, TipologiaBigliettoId, PrenotazioneId 
						from RT_PrenotazioneBiglietto 
						where PrenotazioneId = $PrenotazioneId";
				
				$rowsB = $db->fetch_array($sql);
				$provTotale = 0;
				if($totaliImporti['TotalePagato'] == 0) {
    				foreach($rowsB as $rowB){
    					$sql = "select * from RT_GestoreProvvigioneDettaglio
      							where GestoreId = ".$DatiGeneraliArr['GestoreIdRef']." AND BigliettoId = ".$rowB['TipologiaBigliettoId'];
    					$rowTemp = $db->query_first($sql);
    					
    					$provvigione = $rowB['PrezzoTotalePax']*$rowTemp['Percentuale']/100+($rowTemp['Fisso']*$rowB['NumeroPax']);
    					$provTotale += $provvigione;
    				}
				} else {
				    $sql = "select * from RT_GestoreProvvigioneDettaglio
      							where GestoreId = ".$DatiGeneraliArr['GestoreIdRef']." AND BigliettoId = 17";
				    $rowTemp = $db->query_first($sql);
				    $provTotale = $totaliImporti['TotaleResiduo']*$rowTemp['Percentuale']/100+($rowTemp['Fisso']);
				}
				
			}
			
			//PAYPAL
			$PAYPAL_MAIL = Config::$paypalMail;
			$PAYPAL_LINK = Config::$paypalLink;
			$bank_pagina_grazie = Config::$pageGrazie;
			$base_url = $url_sito;
			$daPagare = $totaliImporti['TotaleDaPagare'];
			$daPagare = $totaliImporti['TotaleResiduo'];
			$ImportoTotale_final = $daPagare;
			
			if(Config::$pagamentoProvvigioniAgenzie){
				$ImportoTotale_final = $ImportoTotale_final - $provTotale;
			}
			
			if(Config::$paypalAccount == 'standard') {
				echo "
					<form id=\"form_paypal\" target=\"_blank\" name=\"form_paypal\" action=\"$PAYPAL_LINK\" method=\"post\">
					<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
					<input type=\"hidden\" name=\"business\" value=\"$PAYPAL_MAIL\">
					<input type=\"hidden\" name=\"item_name\" value=\"Biglietto Bertoldi Boats\">
					<input type=\"hidden\" name=\"item_number\" value=\"$PrenotazioneId\">
					<input type=\"hidden\" name=\"amount\" value=\"$ImportoTotale_final\">
					<input type=\"hidden\" name=\"page_style\" value=\"Primary\">
					<input type=\"hidden\" name=\"return\" value=\"$bank_pagina_grazie\">
					<input type=\"hidden\" name=\"cancel_return\" value=\"$base_url\">
					<input type=\"hidden\" name=\"no_note\" value=\"$PrenotazioneId\">
					<input type=\"hidden\" name=\"currency_code\" value=\"EUR\">
					<input type=\"hidden\" name=\"custom\" value=\"$PrenotazioneId\">
					</form>";
			} else {
				echo "
					<form id=\"form_paypal\" target=\"_blank\" name=\"form_paypal\" action=\"$PAYPAL_LINK\" method=\"post\">
					<input type=\"hidden\" name=\"cmd\" value=\"_hosted-payment\">
					<input type=\"hidden\" name=\"business\" value=\"$PAYPAL_MAIL\">
					<input type=\"hidden\" name=\"item_name\" value=\"Biglietto Bertoldi Boats\">
					<input type=\"hidden\" name=\"item_number\" value=\"$PrenotazioneId\">
					<input type=\"hidden\" name=\"subtotal\" value=\"$ImportoTotale_final\">
					<input type=\"hidden\" name=\"template\" value=\"TemplateB\">
					<input type=\"hidden\" name=\"return\" value=\"$bank_pagina_grazie\">
					<input type=\"hidden\" name=\"cancel_return\" value=\"$base_url\">
					<input type=\"hidden\" name=\"no_note\" value=\"$PrenotazioneId\">
					<input type=\"hidden\" name=\"currency_code\" value=\"EUR\">
					<input type=\"hidden\" name=\"custom\" value=\"$PrenotazioneId\">
					</form>";
			}

			
			//TELECASH
			$url_notification = Config::$httpHost.'/protected/cron/telecash_notifica_automatica.php';
			$hash = createHash( "$ImportoTotale_final","978" );
			echo "
	   			<form id=\"form_telecash\" target=\"_blank\" method=\"post\" action=\"".Config::$telecashLink."\">
				<input type=\"hidden\" name=\"txntype\" value=\"sale\">
				<input type=\"hidden\" name=\"timezone\" value=\"Europe/Berlin\"/>
				<input type=\"hidden\" name=\"txndatetime\" value=\"". getDateTime() ."\"/>
				<input type=\"hidden\" name=\"hash_algorithm\" value=\"SHA256\"/>
				<input type=\"hidden\" name=\"hash\" value=\"". $hash. "\"/>
				<input type=\"hidden\" name=\"storename\" value=\"".Config::$telecashStoreId."\"/>
				<input type=\"hidden\" name=\"mode\" value=\"payonly\"/>
				<input type=\"hidden\" name=\"chargetotal\" value=\"$ImportoTotale_final\"/>
				<input type=\"hidden\" name=\"currency\" value=\"978\"/>
				<input type=\"hidden\" name=\"responseSuccessURL\" value=\"".$bank_pagina_grazie."\"/>
				<input type=\"hidden\" name=\"responseFailURL\" value=\"".$base_url."\"/>
				<input type=\"hidden\" name=\"transactionNotificationURL\" value=\"".$url_notification."\"/>
				<input type=\"hidden\" name=\"custom\" value=\"$PrenotazioneId\">			 
				</form>";
			
		}
		
		$sql = "SELECT MovimentoContabile FROM RT_AppPrenotazioneStato WHERE PrenotazioneStatoId = $StatoPrenotazione";
		$Stato = $db->query_first($sql);
		if (!empty($Stato)) {
			$MovimentoContabile = $Stato['MovimentoContabile'];
		} else {
			$MovimentoContabile = 0;
		}
		
		//controllo tratta italiana
		$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = ".$PrenotazioneId;
		$percorso = $db->query_first($sql);
		$sqlAndata = "SELECT r.idnazione FROM Comune c
                        LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
                        LEFT JOIN Regione r on r.RegioneId = p.RegioneId
                        WHERE c.ComuneId = ".$percorso['ComuneSalitaId'];
		$nazioneAndata = $db->query_first($sqlAndata);
		$sqlRitorno = "SELECT r.idnazione FROM Comune c
                        LEFT JOIN Provincia p on c.provincia = p.ProvinciaId
                        LEFT JOIN Regione r on r.RegioneId = p.RegioneId
                        WHERE c.ComuneId = ".$percorso['ComuneDiscesaId'];
		$nazioneRitorno = $db->query_first($sqlRitorno);
		if($nazioneAndata['idnazione'] == 1 && $nazioneRitorno['idnazione'] == 1) {
		    $viaggioItalia = true;
		} else {
		    $viaggioItalia = false;
		}
		//fine controllo tratta italiana
						
		?>
		<div id="printing"></div>
		<form id="application_form" name="application_form" method="post" action="#">
		<?
				// spara_pulsanti_wizard(0);
				$page->create_textbox_hidden("step_corrente",$step_corrente);
				$page->create_textbox_hidden("step_successivo",$step_corrente+1);
				?>
	
				<div class="brain_formModifica">
	
				<h2 class="sezione_prenotazioni"><?=$dizionario['biglietto']['pagamenti']?></h2>
	
	
				<div class="brain_data-content">
	
				<div class="brain_formModifica formGestoreEdita">
				<h2><?=$dizionario['biglietto']['operazioni_contabili']?></h2>
	
				<br /> <br />
				<div>
				<span><?=$dizionario['biglietto']['totale_prenotazione']?> <?php echo number_format($totaliImporti['TotalePrenotazione'], 2, ",", "."); ?>&euro;
				</span>&nbsp;<span><?=$dizionario['biglietto']['totale_da_pdagare']?> <?php echo number_format($totaliImporti['TotaleDaPagare'], 2, ",", "."); ?>&euro;
				</span>&nbsp;<span><?=$dizionario['biglietto']['totale_pagato']?> <?php echo number_format($totaliImporti['TotalePagato'], 2, ",", "."); ?>&euro;
				</span>&nbsp;<span><?=$dizionario['biglietto']['residuo']?> <?php echo number_format($totaliImporti['TotaleResiduo'], 2, ",", "."); ?>&euro;
				</span>
				<?php if(Config::$pagamentoProvvigioniAgenzie && !$gestore['Verificato']) { ?>
					&nbsp;<span style="color:red;"><?=$dizionario['biglietto']['totale_agenzia']?> <?php echo number_format($ImportoTotale_final, 2, ",", "."); ?>&euro;
					</span>
				<?php } ?>
				
				</div>
	
				<br /> <br /> 
				
				<?php if ($MovimentoContabile) { ?>
					<?php if ((($StatoPrenotazione != 3 && $StatoPrenotazione != 4 && $StatoPrenotazione != 16) || ($StatoPrenotazione == 3 && $totaliImporti['TotaleResiduo'] > 0)) && ($user->SedeLegale==1)) { ?>
							<div class="GestoreSedeAdd">
								<a class="brain_add" href="#"
									onclick="javascript:stripeLink(<?=$PrenotazioneId?>);"
									title="<?=$dizionario['biglietto']['pagamento_online_cliente']?>"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['pagamento_online_cliente']?>
								</a>
							
								<a class="brain_add" href="#"
									onclick="javascript:stripeInvioLink(<?=$PrenotazioneId?>);"
									title="<?=$dizionario['biglietto']['pagamento_online_invia_cliente']?>"><i class="fa fa-envelope" aria-hidden="true"></i> <?=$dizionario['biglietto']['pagamento_online_invia_cliente']?>
								</a>
								<?php if($gestoreIdPrenotazione != 1) { ?>
									<a class="brain_add" href="#"
										onclick="javascript:stripeInvioLinkAgenzia(<?=$PrenotazioneId?>);"
										title="<?=$dizionario['biglietto']['pagamento_online_invia_agenzia']?>"><i class="fa fa-envelope" aria-hidden="true"></i> <?=$dizionario['biglietto']['pagamento_online_invia_agenzia']?>
									</a>
								<?php } ?>
							</div>
							<br>
						<?php } ?>
					<?php if ($StatoPrenotazione != 3 && !$gestore['Verificato']) { ?>
						<script src="https://js.stripe.com/v3/"></script>
						<div class="GestoreSedeAdd">
							<a class="brain_add" href="#"
								onclick="javascript:stripe(<?=$PrenotazioneId?>);"
								title="aggiungi operazione contabile Stripe"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['nuovo_movimento']?> Stripe
							</a>
							<?php if(Config::$paypalActive){?>
							&nbsp&nbsp
							<a class="brain_add" href="#"
								onclick="javascript:paypal();"
								title="aggiungi operazione contabile"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['nuovo_movimento']?> PayPal
							</a>
							<?php } ?>
							<?php if(Config::$telecashActive){?>
							&nbsp&nbsp
							<a class="brain_add" href="#"
								onclick="javascript:telecash();"
								title="aggiungi operazione contabile"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['nuovo_movimento']?> Telecash
							</a>
							<?php } ?>
							&nbsp&nbsp
							<a class="brain_add" href="#"
								onclick="javascript:ExternalLoad('rt_biglietto','prenotazione_movimento.php?do=add&PrenotazioneId=<?=$PrenotazioneId?>&CorsaId=<?=$CorsaId?>');"
								title="aggiungi operazione contabile"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['nuovo_coupon']?>
							</a>
						</div>
						<br />
					<?php } else if ($StatoPrenotazione != 3) { ?>
						<div class="GestoreSedeAdd">
							<a class="brain_add" href="#"
								onclick="javascript:ExternalLoad('rt_biglietto','prenotazione_movimento.php?do=add&PrenotazioneId=<?=$PrenotazioneId?>&CorsaId=<?=$CorsaId?>');"
								title="aggiungi operazione contabile"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['nuovo_movimento']?>
							</a>
						</div>
						<br />
					<? } ?>
				<? } ?>
	
				<table width="100%" cellspacing="0" cellpadding="0" border="0"
				id="gestoreElencoAule">
				<tbody>
				<tr class="rowIntestazione">
				<td><?=$dizionario['biglietto']['tipo_movimento']?></td>
				<td><?=$dizionario['biglietto']['data_movimento']?></td>
				<td><?=$dizionario['generale']['importo']?></td>
				<td><?=$dizionario['biglietto']['supplemento']?></td>
				<td><?=$dizionario['biglietto']['causale']?></td>
				<td><?=$dizionario['biglietto']['data_pagamento']?></td>
				<td><?=$dizionario['biglietto']['importo_pagato']?></td>
				<td><?=$dizionario['generale']['creato_da']?></td>
				<td><?=$dizionario['generale']['modificato_da']?></td>
				<td><?=$dizionario['generale']['ricevuta']?></td>
				<td><?=$dizionario['generale']['edita']?></td>
				<?php if($viaggioItalia && Config::$fatturaincloudBiglietti && ($StatoPrenotazione == 3 || $StatoPrenotazione == 7) && $user->IsAdmin==1) { ?>
					<td><?=$dizionario['biglietto']['operazioni_fattura']?></td>
				<?php } ?>
				</tr>
	
				<?
				foreach ($ArrObject as $key => $movimento) {
					if ($movimento['TipoMovimento'] == 'I' || $movimento['TipoMovimento'] == 'P') {
						$TipoMovimento = $dizionario['biglietto']['m_incasso'];
					} elseif ($movimento['TipoMovimento'] == 'R') {
						$TipoMovimento = $dizionario['biglietto']['m_rimborso'];
					} elseif ($movimento['TipoMovimento'] == 'A') {
						$TipoMovimento = $dizionario['biglietto']['m_annullato'];
					} else {
						$TipoMovimento = "";
					}
					
					if($StatoPrenotazione == 3 && ($movimento['TipoMovimento'] == 'P' || ($movimento['TipoMovimento'] == 'I' && $movimento['PagamentoTipoId'] == 7))&& $user->GestoreId == 1){ 
						$annulla = true;
					} else {
						$annulla = false;
					}
					
					$PagamentoTipo = $movimento['PagamentoTipo'];
					
					if($movimento['Data'] != '') {
						$Data = $dt->format($movimento['Data'], "Y-m-d H:i:s", "d/m/Y");
					} else {
						$Data = '';
					}
					
					$Importo = number_format($movimento['Importo'], 2, ",", ".");
					$Supplemento = number_format($movimento['Supplemento'], 2, ",", ".");
					$Causale = $movimento['Causale'];
					
					if($movimento['DataPagamento'] != '') {
						$DataPagamento = $dt->format($movimento['DataPagamento'], "Y-m-d H:i:s", "d/m/Y");
					} else {
						$DataPagamento = '';
					}
					
					$ImportoPagato = number_format($movimento['ImportoPagato'], 2, ",", ".");
					
					$sql = "SELECT Cognome, Nome FROM Operatore WHERE OperatoreId=".$movimento['OpeIns'];
					$row = $db->query_first($sql);
					$Creato = $row['Cognome']." ".$row['Nome'];
					
					$sql = "SELECT Cognome, Nome FROM Operatore WHERE OperatoreId=".$movimento['OpeAgg'];
					$row = $db->query_first($sql);
					$Modificato = $row['Cognome']." ".$row['Nome'];
					?>
					<!-- QUI L'ELENCO DELLE FERMATE -->
					<tr class="rowBianca">
					<td><span><?=$TipoMovimento . $PagamentoTipo?> </span></td>
					<td><span><?=$Data?> </span></td>
					<td><span><?=$Importo?> </span></td>
					<td><span><?=$Supplemento?> </span></td>
					<td><span><?=$Causale?> </span></td>
					<td><span><?=$DataPagamento?> </span></td>
					<td><span><?=$ImportoPagato?> </span></td>
					<td><span><?=$Creato?> </span></td>
					<td><span><?=$Modificato?> </span></td>
					<td>
						<?php if(!isset($movimento['ScontrinoId']) && $movimento['TipoMovimento'] == 'I' && $movimento['ImportoPagato'] > 0 && $movimento['PagamentoTipoId'] != 12) { ?>
							<a href="#" id="emettiScontrino" onclick="emettiRicevuta(<?=$movimento['PrenotazioneMovimentoId']?>)"><?=$dizionario['biglietto']['emetti_ricevuta']?></a>
						<?php } else if(isset($movimento['ScontrinoId'])  && $movimento['TipoMovimento'] == 'I') { ?>
							<a href="#" onclick="downloadRicevuta(<?=$movimento['PrenotazioneMovimentoId']?>)">
								<?=$dizionario['biglietto']['scontrino_ricevuta']?><br>
								<?=$dizionario['biglietto']['scontrino_cod']?>: <?= $movimento['ScontrinoId'] ?><br>
								<?=$dizionario['biglietto']['scontrino_data']?>: <?= $dt->format($movimento['ScontrinoData'], "Y-m-d H:i:s", "d/m/Y H:i") ?>
							</a>
							<br>
							<?php if (isset($movimento['ScontrinoIdAnnullato'])) { ?>
								<?=$dizionario['biglietto']['scontrino_ricevuta_annullata']?><br>
								<?=$dizionario['biglietto']['scontrino_cod']?> <?= $movimento['ScontrinoIdAnnullato'] ?><br>
								<?=$dizionario['biglietto']['scontrino_data']?> <?= $dt->format($movimento['ScontrinoDataAnnullato'], "Y-m-d H:i:s", "d/m/Y H:i") ?>
							<?php } else { ?>
								<?php
									$sqlMovAnnullato = "SELECT * FROM RT_PrenotazioneMovimento WHERE ScontrinoIdAnnullato = ".$movimento['ScontrinoId'];
									$movimentoIndividuato = $db->query_first($sqlMovAnnullato);
								?>
								<?php if($movimentoIndividuato){ ?>
									<?=$dizionario['biglietto']['scontrino_ricevuta_annullata']?><br>
									<?=$dizionario['biglietto']['scontrino_cod']?> <?= $movimentoIndividuato['ScontrinoIdAnnullato'] ?><br>
									<?=$dizionario['biglietto']['scontrino_data']?> <?= $dt->format($movimentoIndividuato['ScontrinoDataAnnullato'], "Y-m-d H:i:s", "d/m/Y H:i") ?>
								<?php } else { ?>
									<br>
									<a href="#" onclick="annullaRicevuta(<?=$movimento['PrenotazioneMovimentoId']?>)">
										<?=$dizionario['biglietto']['annulla_scontrino']?>
									</a>
								<?php } ?>
							<?php } ?>

							<br><br>
							<?php if (isset($movimento['ScontrinoDataInvio'])) { ?>
								<?=$dizionario['biglietto']['scontrino_invio_data']?>: <?= $dt->format($movimento['ScontrinoDataInvio'], "Y-m-d H:i:s", "d/m/Y H:i") ?>
							<?php } else { ?>
								<?=$dizionario['biglietto']['scontrino_no_invio']?>
							<?php } ?>
							<br>
							<a href="#" id="inviaRicevuta" onclick="inviaRicevuta(<?=$movimento['PrenotazioneMovimentoId']?>)"><?=$dizionario['biglietto']['invia']?></a>
							
						<?php } else if(isset($movimento['ScontrinoId'])  && ($movimento['TipoMovimento'] == 'R' || $movimento['TipoMovimento'] == 'A')) { ?>
							<?=$dizionario['biglietto']['scontrino_ricevuta_annullata']?>
							<?=$dizionario['biglietto']['scontrino_cod']?> <?= $movimento['ScontrinoIdAnnullato'] ?><br>
							<?=$dizionario['biglietto']['scontrino_data']?> <?= $dt->format($movimento['ScontrinoDataAnnullato'], "Y-m-d H:i:s", "d/m/Y H:i") ?>
							<br><br>
							<a href="#" onclick="downloadRicevuta(<?=$movimento['PrenotazioneMovimentoId']?>)">
								<?=$dizionario['biglietto']['scontrino_ricevuta_residuo']?><br>
								<?=$dizionario['biglietto']['scontrino_cod']?> <?= $movimento['ScontrinoId'] ?><br>
								<?=$dizionario['biglietto']['scontrino_data']?> <?= $movimento['ScontrinoData'] ?>
							</a>
							<br><br>
							<?php if (isset($movimento['ScontrinoDataInvio'])) { ?>
								<?=$dizionario['biglietto']['scontrino_invio_data']?>: <?= $dt->format($movimento['ScontrinoDataInvio'], "Y-m-d H:i:s", "d/m/Y H:i") ?>
							<?php } else { ?>
								<?=$dizionario['biglietto']['scontrino_no_invio']?>
							<?php } ?>
							<br>
							<a href="#" id="inviaRicevuta" onclick="inviaRicevuta(<?=$movimento['PrenotazioneMovimentoId']?>)"><?=$dizionario['biglietto']['invia']?></a>
						<?php } else if(!isset($movimento['ScontrinoId'])  && isset($movimento['ScontrinoIdAnnullato']) && $movimento['TipoMovimento'] == 'R') { ?>
							<?=$dizionario['biglietto']['scontrino_ricevuta_annullata']?>
							<?=$dizionario['biglietto']['scontrino_cod']?> <?= $movimento['ScontrinoIdAnnullato'] ?><br>
							<?=$dizionario['biglietto']['scontrino_data']?> <?= $movimento['ScontrinoDataAnnullato'] ?>
						<?php }	else { ?>
							-
						<?php } ?>						
					</td>
					<td>
						<?php
						if ($StatoPrenotazione != 6 && $StatoPrenotazione != 4){
							$sql = "SELECT EmettiCoupon FROM RT_PagamentoTipo WHERE PagamentoTipoId = ".$movimento['PagamentoTipoId'];
							$rowTemp = $db->query_first($sql);
							if ($movimento['TipoMovimento'] != 'A' && $rowTemp['EmettiCoupon'] == 0) { ?>
								<?php if ($movimento['TipoMovimento'] == 'P' && !$gestore['Verificato'] && !$user->IsAdmin) { ?>
								<a href="#"
									onclick="javascript:stripe(<?=$PrenotazioneId?>);"
									title="aggiungi operazione contabile Stripe"><img src="/images/stripe.png" title="pagamento con Stripe" alt="pagamento con Stripe">
									</a>
								<?php if(Config::$paypalActive){?>
								<a title="edita"
									onclick="javascript:paypal();"
									href="#"><img src="/images/paypal.png" title="pagamento con PayPal" alt="pagamento con PayPal"></a>
								<?php } ?>	
									<?php if(Config::$telecashActive){?>
									<a title="edita"
									onclick="javascript:telecash();"
									href="#"><img src="/images/telecash.png" title="pagamento con Telecash" alt="pagamento con Telecash"></a>4
									<?php } ?>
									<a title="edita"
									onclick="javascript:ExternalLoad('rt_biglietto','prenotazione_movimento.php?do=edit&PrenotazioneId=<?=$PrenotazioneId?>&CorsaId=<?=$CorsaId?>&PrenotazioneMovimentoId=<?=$movimento['PrenotazioneMovimentoId']?>');"
									href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="pagamento con coupon" title="pagamento con coupon"></i></a>
								<?php } else { ?>
									<a title="edita"
									onclick="javascript:ExternalLoad('rt_biglietto','prenotazione_movimento.php?do=edit&PrenotazioneId=<?=$PrenotazioneId?>&CorsaId=<?=$CorsaId?>&PrenotazioneMovimentoId=<?=$movimento['PrenotazioneMovimentoId']?>');"
									href="#"><i class="fa fa-pencil-square-o edita" aria-hidden="true" alt="edita" title="edita"></i></a>
								<?php } ?>	
						<?php } 
						}
						?>
						
						<?php if($annulla){ ?>
							<a title="annulla"
									onclick="javascript:cancellaMovimento(<?=$movimento['PrenotazioneMovimentoId']?>, <?=$PrenotazioneId?>);"
									href="#"><img src="/images/del_orange.png" title="annulla" alt="annulla"></a>
						<?php } ?>
					</td>
					<?php if($viaggioItalia && Config::$fatturaincloudBiglietti && ($StatoPrenotazione == 3 || $StatoPrenotazione == 7) && $user->IsAdmin==1) { 
    					$f = new FatturaInCloudViaggiatore();
                	    $f->conn = $db;
                	    $fattura = $f->getTipoFatturaByMovimento($movimento['PrenotazioneMovimentoId']);
                	    ?>
						
    					<td>
    					<?php if(!$fattura) {
    					    if($movimento['TipoMovimento'] == 'I' && $movimento['ImportoPagato'] > 0) {
    					    ?>
    							<a class="brain_add" href="#"
									onclick="javascript:ExternalLoad('rt_biglietto','prenotazione_fattura.php?do=add&PrenotazioneId=<?=$PrenotazioneId?>&MovimentoId=<?=$movimento['PrenotazioneMovimentoId']?>&tipo=invoice');"
									title="emetti fattura"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['emetti_fattura']?>
								</a>
    								

    					<?php } 
    					   if($movimento['TipoMovimento'] == 'R' && $movimento['ImportoPagato'] < 0) { ?>
    							<a class="brain_add" href="#"
    									onclick="javascript:ExternalLoad('rt_biglietto','prenotazione_fattura.php?do=add&PrenotazioneId=<?=$PrenotazioneId?>&MovimentoId=<?=$movimento['PrenotazioneMovimentoId']?>&tipo=credit_note');"
    									title="emetti nota di credito"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['emetti_nota_credito']?>
    								</a>
    					
    					
    					<?php }
    					    } else { ?>
    					   <?= $dizionario['biglietto']['fatturato']?>
    					<?php }?>
    					
    					</td>
    				<?php } ?>
					</tr>
					<?
				}
				?>
					</tbody>
					</table>
					<!-- FINE -->
					
					<?php if ($MovimentoContabile) { ?>
						
						<?php if ($StatoPrenotazione != 3 && !$gestore['Verificato']) { ?>
							<div class="GestoreSedeAdd">
								<a class="brain_add" href="#"
									onclick="javascript:stripe(<?=$PrenotazioneId?>);"
									title="aggiungi operazione contabile Stripe"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['nuovo_movimento']?> Stripe
								</a>
								<?php if(Config::$paypalActive){?>
								&nbsp&nbsp
								<a class="brain_add" href="#"
									onclick="javascript:paypal();"
									title="aggiungi operazione contabile"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['nuovo_movimento']?> PayPal
								</a>
								<?php } ?>
								<?php if(Config::$telecashActive){?>
								&nbsp&nbsp
								<a class="brain_add" href="#"
									onclick="javascript:telecash();"
									title="aggiungi operazione contabile"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['nuovo_movimento']?> Telecash
								</a>
								<?php } ?>
								&nbsp&nbsp
								<a class="brain_add" href="#"
									onclick="javascript:ExternalLoad('rt_biglietto','prenotazione_movimento.php?do=add&PrenotazioneId=<?=$PrenotazioneId?>&CorsaId=<?=$CorsaId?>');"
									title="aggiungi operazione contabile"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['nuovo_coupon']?>
								</a>
							</div>
							<br />
						<?php } else if ($StatoPrenotazione != 3 && $countMovimenti <= 0) { ?>
							<div class="GestoreSedeAdd">
								<a class="brain_add" href="#"
									onclick="javascript:ExternalLoad('rt_biglietto','prenotazione_movimento.php?do=add&PrenotazioneId=<?=$PrenotazioneId?>&CorsaId=<?=$CorsaId?>');"
									title="aggiungi operazione contabile"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['biglietto']['nuovo_movimento']?>
								</a>
							</div>
							<br />
						<? } ?>
					<? } ?>
				<?php //FATTURAZIONE FATTURA IN CLOUD
				if($viaggioItalia && Config::$fatturaincloudBiglietti && ($StatoPrenotazione == 3 || $StatoPrenotazione == 7) && $user->IsAdmin==1) { 
            	    $f = new FatturaInCloudViaggiatore();
            	    $f->conn = $db;
            	    $fatture = $f->getFatture($PrenotazioneId);
            	    ?>
                	<div style="margin-top:30px;">
                		<h2><?=$dizionario['biglietto']['operazioni_fattura']?></h2>
                		<br>
						<table width="100%" cellspacing="0" cellpadding="0" border="0"
            				id="gestoreElencoAule">
            				<tbody>
                				<tr class="rowIntestazione">
                    				<td><?=$dizionario['biglietto']['tipo']?></td>
                    				<td><?=$dizionario['pre']['num']?></td>
                    				<td><?=$dizionario['generale']['data']?></td>
                    				<td><?=$dizionario['generale']['descrizione']?></td>
                    				<td><?=$dizionario['generale']['importo']?></td>
                				</tr>
                				<?php foreach($fatture as $f) {?>
                					<tr>
                        				<td><?= $dizionario['biglietto'][$f['Tipo']]?></td>
                        				<td><?= $f['FatturaNumero']?></td>
                        				<td><?= $dt->format($f['FatturaData'], "Y-m-d", "d/m/Y") ?></td>
                        				<td><?= $f['Articolo']?></td>
                        				<td><?= number_format($f['ArticoloPrezzoLordo'], 2, ",", ".")?></td>
                    				</tr>
                				<?php } ?>
						
							</tbody>
						</table>		
                		
                	</div>
        		<?php } ?>
				</div>
			</div>
		</div>
	</form>
	<?
	
	}
}

function form_tipo1_postviaggio($PrenotazioneId)
{
    global $HtmlCommon, $db, $user, $prenotazione_wizard, $dizionario;

    $db = new Database();
    $db->connect();

    $prenotazione_wizard = new Prenotazione($PrenotazioneId);
    $prenotazione_wizard->conn = $db;
    $_SESSION['PRENOTAZIONE_WIZARD'] = serialize($prenotazione_wizard);

    $page = new Form();
    $dt = new DT();
    include_once("biglietto_validator.php");
    $HtmlCommon->html_titolo_pagina("Quadra prenotazione", 0, "", "");
    $HtmlCommon->html_titolo_box("Quadra prenotazione");
    ?>

    <script type="text/javascript">
    $(document).ready(function() {
        MostraElencoCorse();
        // MostraTipoBiglietti();
        // MostraNotePerTratta();
    });
    </script>

    <?
    $arr_stato[] = array("StatoId" => '0', "Stato" => 'Non Attivo');
    $arr_stato[] = array("StatoId" => '1', "Stato" => 'Attivo');

    $arr_viaggio[] = array("ViaggioId" => '1', "Viaggio" => 'Solo andata');
    $arr_viaggio[] = array("ViaggioId" => '2', "Viaggio" => 'Andata e ritorno');

    $TrattaTipo = new TrattaTipo();
    $TrattaTipo->conn = $db;
    $arr_tratta_tipo = $TrattaTipo->getAll();

    $CorsaId = $_REQUEST['CorsaId'];
    $Corsa = new Corsa($CorsaId);
    $Corsa->conn = $db;
    $arr_partenze = $Corsa->getAllPickup();
    $arr_arrivi = $Corsa->getAllDropOff();

    $TrattaDirezione = new TrattaDirezione();
    $TrattaDirezione->conn = $db;
    $arr_tratta_direzione = $TrattaDirezione->getAll();

    $TrattaNome = "";
    $PercorsoPeso = "";
    $PercorsoStato = 0;

    $arr_sesso[] = array("SessoId" => '1', "Sesso" => 'Sig.');
    $arr_sesso[] = array("SessoId" => '2', "Sesso" => 'Sig.ra / Sig.na');
    $arr_sesso[] = array("SessoId" => '3', "Sesso" => 'N.D.');
    $azione = "quadratura";
    $modPre = 0;

    if ($azione == "quadratura") {
        $prenotazione_wizard->inizializzaDatiGenerali();
        $DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;

        $NomeCliente = $DatiGeneraliArr['ClienteNome'];
        $CellulareCliente = $DatiGeneraliArr['ClienteCellulare'];
        $CellulareFamiliare = $DatiGeneraliArr['ClienteCellulareFamiliare'];
        $Lingua = $DatiGeneraliArr['Lingua'];
        $ClienteEmail = $DatiGeneraliArr['ClienteEmail'];
        $SessoIdCliente = $DatiGeneraliArr['ClienteSessoId'];
        $TipoViaggioId = $DatiGeneraliArr['TipoViaggioId'];
        $RitornoOpen = $DatiGeneraliArr['RitornoOpen'];

        $prenotazione_wizard->inizializzaDatiGeneraliPercorso('A');
        $DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
        $ComuneSalitaId = $DatiGeneraliPercorsoArr['ComuneSalitaId'];
        $ComuneDiscesaId = $DatiGeneraliPercorsoArr['ComuneDiscesaId'];
        $FermataSalitaAId = $DatiGeneraliPercorsoArr['FermataSalitaId'];
        $FermataDiscesaAId = $DatiGeneraliPercorsoArr['FermataDiscesaId'];
        $CorsaAndataId = $DatiGeneraliPercorsoArr['CorsaId'];
        $CorsaDataAndata = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];

        $prenotazione_wizard->inizializzaDatiGeneraliPercorso('R');
        $DatiGeneraliPercorsoArr = $prenotazione_wizard->DatiGeneraliPercorso;
        $CorsaRitornoId = $DatiGeneraliPercorsoArr['CorsaId'];
        $CorsaDataRitorno = $DatiGeneraliPercorsoArr['CorsaDataPartenza'];
        $StatoPrenotazioneId = $DatiGeneraliArr['StatoPrenotazione'];
        $modPre = 1;
        $azione = "update";
    }
    ?>

    <div id="brain_form_content" class="brain_row brain_contenuto">
        <div class="brain_boxIntero">
            <form id="application_form" name="application_form" method="post" action="#">
                <div class="brain_formModifica">
                    <div class="brain_data-content">
                        <? $page->create_textbox_hidden("action", $azione); ?>
                        <input type="hidden" name="CorsaSelezionataA" id="CorsaSelezionataA" value="<?= $CorsaAndataId ?>" />
                        <input type="hidden" name="CorsaSelezionataR" id="CorsaSelezionataR" value="<?= $CorsaRitornoId ?>" />
                        <input type="hidden" name="RichiestaCofermaErrori" id="RichiestaCofermaErrori" value="1" />
                        <input type="hidden" name="DataSelezionataA" id="DataSelezionataA" value="<?= $CorsaDataAndata ?>" />
                        <input type="hidden" name="DataSelezionataR" id="DataSelezionataR" value="<?= $CorsaDataRitorno ?>" />
                        <input type="hidden" name="modPre" id="modPre" value="<?= $modPre ?>" />

                        <? $page->create_select("Partenza:", "Biglietto[PartenzaId]", "PartenzaId", "brain_campiform", $arr_partenze, $ComuneSalitaId, "ComuneId", "Comune", array(
                            "class" => "'required'",
                            "onChange" => "'javascript:MostraPossibiliDestinazioni(this);MostraElencoCorse();'"
                        ), 1);

                        $page->create_select("Destinazione:", "Biglietto[DestinazioneId]", "DestinazioneId", "brain_campiform", $arr_arrivi, $ComuneDiscesaId, "ComuneId", "Comune", array(
                            "class" => "'required'",
                            "onChange" => "'javascript:MostraElencoCorse();'"
                        ), 1);

                        $page->create_select("Tipo viaggio:", "Prenotazione[TipoViaggioId]", "TipoViaggioId", "brain_campiform", $arr_viaggio, $TipoViaggioId, "ViaggioId", "Viaggio", array(
                            "class" => "'required'",
                            "onChange" => "'javascript:MostraElencoCorse();'"
                        ), 1);

                        print("<br style=\"clear:both;\"/>");
                        print("<br style=\"clear:both;\"/>");
                        print("<br style=\"clear:both;\"/>");
                        ?>

                        <div id="ElencoCorseA">
                            <h1>Seleziona la corsa di andata</h1>
                            <? print("<br style=\"clear:both;\"/>");
                            $page->create_textbox("Ricerca corse di andata a partire dal:", "RicercaDaId", "Controlli[RicercaDaId]", date('d/m/Y'), 1, "brain_campoForm", array("class" => "'required italianDate'",
                                "onChange" => "'javascript:MostraElencoCorseAR(\"A\");'"), "", "10");
                            print("<br style=\"clear:both;\"/>");
                            ?>
                            <div id="ElencoA">
                                <? //GetCorse($CorsaDataAndata,$CorsaDataAndata,$ComuneSalitaId,$ComuneDiscesaId,'A',$CorsaAndataId,$CorsaDataAndata); ?>
                            </div>
                            <? print("<br style=\"clear:both;\"/>"); ?>
                        </div>

                        <div id="ElencoCorseR" style="display: none;">
                            <h1>Seleziona la corsa di ritorno</h1>
                            <? print("<br style=\"clear:both;\"/>");
                            $page->create_textbox("Ricerca corse di ritorno a partire dal:", "RicercaAId", "Controlli[RicercaAId]", date('d/m/Y'), 1, "brain_campiform", array("class" => "'required italianDate'",
                                "onChange" => "'javascript:MostraElencoCorseAR(\"R\");'"), "", "10");
                            print("<br style=\"clear:both;\"/>");
                            $arr_is[] = array("ArrIsId" => '0', "ArrIs" => 'No');
                            $arr_is[] = array("ArrIsId" => '1', "ArrIs" => 'Si');
                            $page->create_textbox_hidden("Prenotazione[RitornoOpen]", 0);
                            /*
                            $page->create_select("Ritorno Open", "Prenotazione[RitornoOpen]", "RitornoOpen", "brain_campoForm", $arr_is, $RitornoOpen, "ArrIsId", "ArrIs",
                                array("class" => "'required'"), 1);
                            */
                            ?>
                            <div id="ElencoR"></div>
                            <? print("<br style=\"clear:both;\"/>"); ?>
                        </div>

                        <div id="InfoFermateA">
                            <? print("<br style=\"clear:both;\"/>"); ?>
                            <h2 class="sezione_prenotazioni">Fermate di salita e discesa</h2>
                            <div class="info"></div>
                            <? print("<br style=\"clear:both;\"/>"); ?>
                        </div>

                        <div id="InfoFermateR" style="display: none;">
                            <div class="info"></div>
                            <? print("<br style=\"clear:both;\"/>"); ?>
                        </div>

                        <div id="InfoBiglietti">
                            <? print("<br style=\"clear:both;\"/>"); ?>
                            <h2 class="sezione_prenotazioni">Biglietti e pax</h2>
                            <div id="TipologiaBiglietti">
                                <!-- seleziono tutte le tipologie biglietti associate alla corsa -->
                                <? GetTipologiaBiglietti($CorsaAndataId, $FermataSalitaAId, $FermataDiscesaAId, $TipoViaggioId); ?>
                            </div>
                        </div>

                        <div id="InfoPasseggero">
                            <? print("<br style=\"clear:both;\"/>"); ?>
                            <? print("<br style=\"clear:both;\"/>"); ?>
                            <h2 class="sezione_prenotazioni">Info Passeggeri</h2>
                            <?
                            $page->create_textbox("Cognome e Nome", "CognomeNome", "Prenotazione[ClienteNome]", $NomeCliente, 1, "brain_campoForm", array("class" => "'required'"));
                            $page->create_select("Titolo:", "Prenotazione[ClienteSessoId]", "SessoId", "brain_campoForm", $arr_sesso, $SessoIdCliente, "SessoId", "Sesso", array(
                                "class" => "'required'"
                            ), 1);

                            $page->create_textbox("Cellulare", "Cellulare", "Prenotazione[ClienteCellulare]", $CellulareCliente, 1, "brain_campoForm", array("class" => "'required'"));
                            $page->create_textbox("Tel. Familiare", "TelFamiliare", "Prenotazione[ClienteCellulareFamiliare]", $CellulareFamiliare, 0, "brain_campoForm", null);
                            $page->create_textbox("Email", "ClienteEmail", "Prenotazione[ClienteEmail]", $ClienteEmail, 0, "brain_campoForm", null);
                            ?>
                        </div>

                        <div id="SelezionePosti" style="display: none;">
                            <? print("<br style=\"clear:both;\"/>"); ?>
                            <? print("<br style=\"clear:both;\"/>"); ?>
                            <h2 class="sezione_prenotazioni">Selezione dei posti</h2>
                            <div id="SelezionaPostiA"></div>
                        </div>

                        <div id="NotePerTratta" style="display: none;">
                            <? print("<br style=\"clear:both;\"/>"); ?>
                            <h2 class="sezione_prenotazioni">Note per tratta</h2>
                            <div id="ElencoNoteAndata"></div>
                        </div>

                        <div id="elenco_comuni"></div>
                        <? print("<br style=\"clear:both;\"/>"); ?>
                    </div>
                </div>
                <div class="divSubmit">
                    <?
                    if (($StatoPrenotazioneId != 4) && ($StatoPrenotazioneId != 6) && ($StatoPrenotazioneId != 7)) {
                        spara_pulsanti_wizard(100);
                    }
                    ?>
                </div>
            </form>
        </div>
    </div>
    <?
}

function GetTipologiaBiglietti($CorsaId,$FermataIdAP,$FermataIdAD,$TV)
{
	global $user,$HtmlCommon, $dizionario;
	$page=new Form();
	$db= new Database();
	$db->connect();
	/*
	 $CorsaId=$_REQUEST['CorsaId'];
	$FermataIdAP=$_REQUEST['FermataIdAP'];
	$FermataIdAD=$_REQUEST['FermataIdAD'];
	$TV=$_REQUEST['TV']; */
	$ar=1;
	if ($TV==1)
		$ar=0;

	$prenotazione=new Prenotazione();
	$prenotazione->conn=$db;
	$arr_tratte=$prenotazione->GetTratte($CorsaId, $FermataIdAP, $FermataIdAD);
        //$arr_tratteAR=$prenotazione->GetTratteAR($CorsaId, $FermataIdAP, $FermataIdAD);
	$arr_listini=$prenotazione->GetListini($arr_tratte,$FermataIdAP,$FermataIdAD,$CorsaId,$arr_tratteAR[0],$arr_tratteAR[1]);



	$at=0;



	while ($at<sizeof($arr_tratte))
	{

		if ($at>0)
			$tratteid.=",".$arr_tratte[$at]['TrattaId'];
		else
			$tratteid=$arr_tratte[$at]['TrattaId'];


		$at++;
	}




	$sql="select distinct TipologiaBigliettoId,TipologiaBiglietto from RT_ViewPrenotazioneTipoBiglietti where TrattaId In($tratteid) and AR=$ar ";
	


	$ArrObject = $db->fetch_array($sql);


	$i=0;
	$numerobiglietti=-1;

	?>
	<div class="brain_rowAll">
	<table width="100%" cellspacing="0" cellpadding="0" border="0"
	id="pagamentiTabella">
	<tbody>
	<tr class="rowIntestazione">
	<th scope="row"><?=$dizionario['biglietto']['n_pax']?></th>
	<td><?=$dizionario['biglietto']['biglietto']?></td>
	<td><?=$dizionario['biglietto']['prezzo_pax']?></td>
	<td><?=$dizionario['biglietto']['prezzo_base']?></td>
	<td><?=$dizionario['biglietto']['riduzione']?></td>
	<td><?=$dizionario['biglietto']['aumento']?></td>
	<td><?=$dizionario['biglietto']['prezzo_finale']?></td>
	</tr>
	<?php

			while ($i< sizeof($ArrObject))
			{
				$BigliettoId=$ArrObject[$i]['TipologiaBigliettoId'];
				$BigliettoPrezzo=$ArrObject[$i]['TipologiaBiglietto'];
				$stringa=$BigliettoId."_".$BigliettoPrezzo;

				$BigliettoDescr=$BigliettoPrezzo;



				$ntratte=0;
				$PrezzoPax=0;
				while($ntratte<sizeof($arr_tratte))
				{
					$TrattaId=$arr_tratte[$ntratte]['TrattaId'];
					$ListinoId=$arr_listini[$TrattaId]['ListinoId'];
					$sql="Select * from RT_ListinoBiglietto where ListinoId=$ListinoId and BigliettoId=$BigliettoId and  OdcIdRef=$user->OdcId and Cancella=0";

					//   echo($sql);

					$ArrPrezzo = $db->query_first($sql);

					$listinoNome="";

					if (!empty($ArrPrezzo['ListinoId']))
						$PrezzoPax+=$ArrPrezzo['Prezzo'];





					$ntratte++;
				}

				if ($PrezzoPax>0)
				{
					$PrezzoPax_f= number_format($PrezzoPax, 2, ",", ".");

					$BigliettoPrezzo.=" ( ".$PrezzoPax_f." &euro; )";

					?>

	<tr class="rowBianca">

	<th scope="row"><input id="Pax<?=$i?>"
	onchange="javascript:CalcolaPrezzoTipoBiglietto();" type="text"
	maxlength="3" size="3" value="0"
	name="BigliettoTipologiaPax[<?=$BigliettoId?>_<?=$BigliettoDescr?>]"
	class="required digits"> <?php



			/*  $page->create_textbox($BigliettoPrezzo,"Pax".$i,"BigliettoTipologiaPax[$stringa]",0,1,"brain_campiform",array(
					 "onChange"=>"'javascript:CalcolaPrezzoTipoBiglietto($BigliettoId,\"$BigliettoPrezzo\",$PrezzoPax,this);'",
       "class"=>"'required number'"));*/
			$page->create_textbox_hidden("Prezzo".$i,$PrezzoPax);
			$page->create_textbox_hidden("Totale".$i,0);
			?>
			</th>
			<td><?=$ArrObject[$i]['TipologiaBiglietto']?></td>
			<td><?=$PrezzoPax_f?></td>
			<td id="PrezzoParziale<?=$i?>">0,00 &euro;</td>

			<td><input id="PrezzoRiduzione<?=$i?>"
			onchange="javascript:CalcolaPrezzoTipoBiglietto();" type="text"
			maxlength="8" size="8" value="0"
			name="BigliettoTipologiaPaxRid[<?=$BigliettoId?>]"
			class="required numberDE">
			</td>

			<td><input id="PrezzoAumento<?=$i?>"
			onchange="javascript:CalcolaPrezzoTipoBiglietto();" type="text"
			maxlength="8" size="8" value="0"
			name="BigliettoTipologiaPaxAum[<?=$BigliettoId?>]"
			class="required numberDE">
			</td>

			<td id="PrezzoFinale<?=$i?>">0,00 &euro;</td>

			</tr>
			<?php
					$numerobiglietti++;

				}
				$i++;

			}


	$page->create_textbox_hidden("NumeroBiglietti",$numerobiglietti);
	$page->create_textbox_hidden("TotalePax",0);
	$page->create_textbox_hidden("TotalePaxScelti",0);
	?>

	<tr class="rowIntestazione">
	<th scope="row">&nbsp;</th>
	<td></td>
	<td><strong></strong></td>
	<td><strong></strong></td>
	<td><strong></strong></td>
	<td><strong></strong></td>
	<td id="PrezzoTotalePax"><strong>0,00 &euro; </strong></td>

	</tr>
	</tbody>
	</table>
	</div>

	<?php
}

function spara_pulsante_ripristina_web() {
    global $dizionario;
    $page=new Form();
    $page->create_textbox_hidden("tipoPagamento", "0");
    ?>
	<div class="divSubmit">
		<?php  $page->create_button("ConfermaPrenotazioneStripe","ConfermaPrenotazioneStripe","Conferma prenotazione Stripe","brain_salva","submit"); ?>
		<?php  $page->create_button("ConfermaPrenotazionePaypal","ConfermaPrenotazionePaypal","Conferma prenotazione PayPal","brain_salva","submit"); ?>
	</form>
	</div>
	<?php
}

function spara_pulsanti_wizard_box()
{
	global $dizionario;
	$page=new Form();

	?>
	<div class="divSubmit">

	<?  $page->create_button($dizionario['generale']['salva'],"Salva","Salva","brain_salva","submit"); ?>



	<a href="javascript:void(0);" onclick="javascript:ChiudiBox();"
	title="chiudi" class="brain_annulla"><?=$dizionario['generale']['chiudi']?></a> <select
	name="application_formTrackList" id="application_formTrackList"
	multiple="multiple" class="changeListClass" style="display: none;"></select>
	</form>


	</div>
	<?php

}


function spara_pulsanti_wizard($steptogo) {
	global $funzione_edit,$prenotazione_wizard,$user,$db, $dizionario;
	if ($funzione_edit){
		spara_pulsanti_edit($steptogo);
	} else {
		if (!$funzione_edit)
			$page=new Form();
		
		$titoli_emessi=0;
		if (is_object($prenotazione_wizard))
		{
			$titoli_emessi = $prenotazione_wizard->TitoliEmessiYesNo();

		}

		if (is_object($prenotazione_wizard)) {
			//controllo data modifica andata
			$prenotazioneId = $prenotazione_wizard->Id;
			$slq = "Select RT_PrenotazioneDettaglio.DataPartenza, RT_PrenotazioneDettaglio.OrarioPartenza, RT_PrenotazioneDettaglio.LineaId, RT_PrenotazioneDettaglio.Tragitto, RT_Linea.TempoMaxModifica, RT_Linea.LineaId,
					RT_PrenotazioneDettaglio.ComunePartenza, RT_PrenotazioneDettaglio.ComuneArrivo
					FROM RT_PrenotazioneDettaglio
					left join RT_Linea on (RT_Linea.Lineaid = RT_PrenotazioneDettaglio.LineaId)
					Where RT_PrenotazioneDettaglio.PrenotazioneId = $prenotazioneId and RT_PrenotazioneDettaglio.Tragitto = 'Andata'
					group by RT_PrenotazioneDettaglio.DataPartenza";
			$date = $db->query_first($slq);
			$today = new DateTime();
			$sogliaLimite = new DateTime($date['DataPartenza'].' '.$date['OrarioPartenza']);
			$sogliaLimite->modify('-'.$date['TempoMaxModifica'].' hour');
				
			$diff = $today->diff($sogliaLimite);

			if($user->GestoreId != 1 ) {
				if($diff->invert == 0){	
					echo $dizionario['biglietto']['modificabile']." ". date_format($sogliaLimite, 'H:i') ." ".$dizionario['biglietto']['del']." ".date_format($sogliaLimite, 'd/m/Y').". ".$dizionario['biglietto']['mancano']." ". $diff->format($dizionario['biglietto']['mancano_format']);
				} else {
					echo $dizionario['biglietto']['no_modificabile']." ".date_format($sogliaLimite, 'H:i') ." ".$dizionario['biglietto']['del']." ".date_format($sogliaLimite, 'd/m/Y').".";
				}
			}
			
			$sql = "SELECT RitornoOpen, PrenotazioneStato, CodicePrenotazione  FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";
			$open = $db->query_first($sql);
			if($open['RitornoOpen'] == 0){
				//controllo data modifica ritorno
				$slq = "Select RT_PrenotazioneDettaglio.DataPartenza, RT_PrenotazioneDettaglio.OrarioPartenza, RT_PrenotazioneDettaglio.LineaId, RT_PrenotazioneDettaglio.Tragitto, RT_Linea.TempoMaxModifica, RT_Linea.LineaId
				FROM RT_PrenotazioneDettaglio
				left join RT_Linea on (RT_Linea.Lineaid = RT_PrenotazioneDettaglio.LineaId)
				Where RT_PrenotazioneDettaglio.PrenotazioneId = $prenotazioneId and RT_PrenotazioneDettaglio.Tragitto = 'Ritorno'
				group by RT_PrenotazioneDettaglio.DataPartenza";
				$dateR = $db->query_first($slq);
				if(isset($dateR['DataPartenza'])){
					$sogliaLimiteR = new DateTime($dateR['DataPartenza'].' '.$dateR['OrarioPartenza']);
					$sogliaLimiteR->modify('-'.$dateR['TempoMaxModifica'].' hour');
					$diffR = $today->diff($sogliaLimiteR);
					if( $user->GestoreId != 1 ) {
						if( $diffR->invert == 0 ){
							echo $dizionario['biglietto']['modificabile_ritorno']." ". date_format($sogliaLimiteR, 'H:i') ." ".$dizionario['biglietto']['del']." ".date_format($sogliaLimiteR, 'd/m/Y').". ".$dizionario['biglietto']['mancano']." ". $diffR->format($dizionario['biglietto']['mancano_format']);
						} else {
							echo $dizionario['biglietto']['no_modificabile_ritorno']." ".date_format($sogliaLimiteR, 'H:i') ." ".$dizionario['biglietto']['del']." ".date_format($sogliaLimiteR, 'd/m/Y').".";
						}
					}
				}
			} else {
				echo $dizionario['biglietto']['modificabile_ritorno_open'];
			}
			echo "<br>";
		}

		?>

		<div class="divSubmit">
		<?php

				if (!(is_object($prenotazione_wizard))) {
					$page->create_button("Avanti","Avanti",$dizionario['generale']['avanti'],"brain_salva","submit");
					$page->create_button("Prenota","Prenota",$dizionario['generale']['prenota'],"brain_salva","submit");
					if (isset($_SESSION['PRENOTAZIONE_MULTI'])) { 
					?>
					    <input type="button" value="<?=$dizionario['biglietto']['fine_multi']?>" id="FineMulti1" class="brain_salva" name="FineMulti">
					<?php } 
				} else {
					if (!$titoli_emessi) {
					    
					    if ($steptogo!=100) {
							//$page->create_button("Emetti","Emetti","emetti titolo","brain_salva","submit");
							$page->create_button("","ModificaAnagrafica",$dizionario['generale']['modifica_dati_anagrafici'],"brain_salva","submit");
					    }
						$page->create_button("Modifica","Modifica",$dizionario['generale']['modifica_prenotazione'],"brain_salva","submit");

						$page->create_button("Annulla","Annulla",$dizionario['generale']['annulla'],"brain_cancella","submit");

						/*if (($user->SedeLegale==1) and ($user->IsAdmin==1))
						$page->create_button("AnnullaForzato","AnnullaForzato","annulla con riserva","brain_cancella","submit");
						 */

						//se la prenotazione è nello stato prenotazione Da Confermare
						if($open['PrenotazioneStato'] == 2) {
							$page->create_textbox_hidden("prenotazioneDaConfermare", $prenotazioneId);
							$page->create_button("ConfermaPrenotazione","ConfermaPrenotazione",$dizionario['generale']['conferma_prenotazione'],"brain_salva","button");
						}
						
						
					} else { // rimborso
					    
					    if($open['PrenotazioneStato'] != 7 && $open['PrenotazioneStato'] != 16){
					       $page->create_button("Modifica","Modifica",$dizionario['generale']['cambio_data'],"brain_salva","submit");
					    }
						if($open['PrenotazioneStato'] != 16){
							$page->create_button("ModificaAnagrafica","ModificaAnagrafica",$dizionario['generale']['modifica_dati_anagrafici'],"brain_salva","submit");
						}

						$page->create_button("Annulla","Annulla",$dizionario['generale']['annulla'],"brain_cancella","submit");
						if($open['PrenotazioneStato'] != 16){
							$page->create_button("AnnullaViaggio","AnnullaViaggio",$dizionario['generale']['annulla_tour'],"brain_cancella","submit");
						}
					}    
				}
  
		?>

		<select name="application_formTrackList" id="application_formTrackList"
		multiple="multiple" class="changeListClass" style="display: none;"></select>
		</form>

		</div>
		<?

	}
}

function spara_pulsanti_edit($steptogo)
{

	global $abilita_modifica, $dizionario;

	$page=new Form();

	?>
		<div class="divSubmit">

		<?  $page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit"); ?>

		<a href="javascript:void(0);"
		onclick="loadMainContent('mediazione','mediazione.php?step=2',this);"
		title="Home" class="brain_annulla"><?=$dizionario['generale']['annulla']?></a> <select
		name="application_formTrackList" id="application_formTrackList"
		multiple="multiple" class="changeListClass" style="display: none;"></select>
		</form>


		</div>
		<?
}



if(is_object($user)) {

	/*      ID - FUNZIONE
 1	Lista
2	Aggiunta
3	Cancellazione
4	Modifica
5	Esportazione
6	Importazione
7	Stampa
	 */




	$db= new Database();
	$db->connect();
	$user->conn=$db;
	if (isset($tratta_wizard) && is_object($tratta_wizard))
		$tratta_wizard->conn=$db;
		$permessi=$user->get_permessi_modulo($ModuloId);
		if (sizeof($permessi)>0)
		{
			if(!isset($_REQUEST['do'])){
		    	$do='';
		    } else {
		    	$do=$_REQUEST['do'];
		    }


			switch($do) {
                            
				case "RimborsoParziale":
					$ModuloId=2;
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						RimborsoParziale ();
					else
						$errore->stampa_errore(2);
                                        
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni 
				break;    
                            
			case "add":
				$ModuloId=2;
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso)) {
					
					//reset multi prenotazione
					if (isset($_REQUEST['reset'])) {
						if ($_REQUEST['reset']) {
							unset($_SESSION['PRENOTAZIONE_MULTI']);
						}
					}
					if(isset($_REQUEST['step'])){
						add($_REQUEST['step']);
					} else {
						add(null);
					}
					
				} else
					$errore->stampa_errore(2);

					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;

			case "edit":
				$ModuloId=2;
				$FunzioneId=4;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				// print_r($permesso);
				if (sizeof($permesso))
				{
					
					//reset multi prenotazione
					if (isset($_REQUEST['reset']) && $_REQUEST['reset']) {
						unset($_SESSION['PRENOTAZIONE_MULTI']);
					}
					
					if (isset($_REQUEST['step'])) {
						edit($_REQUEST['PrenotazioneId'], $_REQUEST['step']);
					} else {
						edit($_REQUEST['PrenotazioneId']);
					}
				}
				else
					$errore->stampa_errore(2);

					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;

			case "QuadraturaPrenotazione":

				$FunzioneId=4;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				// print_r($permesso);
				if (sizeof($permesso))
					form_tipo1_postviaggio($_REQUEST['PrenotazioneId']);
				else
					$errore->stampa_errore(2);

					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;

			case "show_prenotazioni":
				$ModuloId=4;
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					show_prenotazioni();
				else
					$errore->stampa_errore(2);

					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;



			default:
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					show_list();
				else
					$errore->stampa_errore(2);

					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;
			}

		} // end verifica permessi
		else {
			$errore->stampa_errore(1);

		}

}
// se l'utente non ÃƒÆ’Ã‚Â¨ loggato
else {
	header("Location: /logout.php");
}



//funzioni di utilità
// Funzione per rimuovere il testo tra parentesi tonde
function rimuoviParentesi($string) {
	// Espressione regolare per rimuovere qualsiasi testo tra parentesi (con spazi opzionali)
	return preg_replace('/\s*\(.*?\)\s*/i', '', $string);
}
?>
