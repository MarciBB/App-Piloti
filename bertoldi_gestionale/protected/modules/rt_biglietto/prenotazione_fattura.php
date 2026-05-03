<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.DT.php");
include_once($classespath_."class.FatturaInCloudViaggiatore.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Nazione.php");

global $ModuloId;
global $user;

$ModuloId = 2; // modulo base mediazione


if(is_object($user)) {

	/*  ID - FUNZIONE
		1	Lista
		2	Aggiunta
		3	Cancellazione
		4	Modifica
		5	Esportazione
		6	Importazione
		7	Stampa
	*/

	$permessi=$user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi)>0)
	{
		
	if (!empty($_REQUEST)){
		$do = $_REQUEST['do'];
	} else {
		$do='';
	}
		
		switch($do) {

			case "add":
				$ModuloId=2;
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso)) {
				    $fatturaRimborsata = null;
				    if(isset($_GET['$fatturaInCloudIdRimborsata'])) {
				        $fatturaRimborsata = $_GET['$fatturaInCloudIdRimborsata'];
				    }
				
				    add($_GET['PrenotazioneId'], $_GET['tipo'], $_GET['MovimentoId'], $fatturaRimborsata);
				} else
					$errore->stampa_errore(2);

				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
			
			default:
				$errore->stampa_errore(2);

				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
		}
	} // end verifica permessi
	else {
		$errore->stampa_errore(1);
	}

}
// se l'utente non è loggato
else {
	header("Location: /logout.php");
}

function add($PrenotazioneId, $tipo, $MovimentoId, $fatturaInCloudIdRimborsata = null) {

	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();

	$page = new Form();

	$fattura = new FatturaInCloudViaggiatore();
	$fattura->conn = $db;

	$action = null;
	
	switch ($tipo) {
	    case 'invoice':
	        $HtmlCommon->html_titolo_pagina($dizionario['biglietto']['emetti_fattura'],0 ,"", "");
	        $HtmlCommon->html_titolo_box ($dizionario['biglietto']['emetti_fattura']);
	        $action = 'add_invoice';
	        $dataFattura = $dizionario['biglietto']['data_fattura'];
	        break;
	    case 'receipt':
	        $HtmlCommon->html_titolo_pagina($dizionario['biglietto']['emetti_ricevuta'],0 ,"", "");
	        $HtmlCommon->html_titolo_box ($dizionario['biglietto']['emetti_ricevuta']);
	        $action = 'add_receipt';
	        $dataFattura = $dizionario['biglietto']['data_ricevuta'];
	        break;
	    case 'credit_note':
	        $HtmlCommon->html_titolo_pagina($dizionario['biglietto']['emetti_nota_credito'],0 ,"", "");
	        $HtmlCommon->html_titolo_box ($dizionario['biglietto']['emetti_nota_credito']);
	        $action = 'add_credit_note';
	        $dataFattura = $dizionario['biglietto']['data_nota_credito'];
	        break;
	}

	$prenotazioneObj = new Prenotazione();
	$prenotazioneObj->Id = $PrenotazioneId;
	$prenotazioneObj->conn = $db;
	$prenotazioneObj->inizializzaDatiGenerali();
	$prenotazione = $prenotazioneObj->DatiGenerali;
	
	include_once("prenotazione_fattura_validator.php");
	
	//array nazioni select
	$nazione_residenza=new Nazione(null);
	$nazione_residenza->conn=$db;
	$nazione_residenza->getAllNazione();
	$arr_nazione_residenza=$nazione_residenza->ArrNazione;
	$NazioneResidenzaId=1;
	
	//array stato fattura select
	$arr_stato = array();
	$arr_stato[] = ['FatturaStatoId'=>0, 'FatturaStato'=>'SALDATO'];
	$arr_stato[] = ['FatturaStatoId'=>1, 'FatturaStato'=>'NON SALDATO'];
	?>

	<script type="text/javascript">
    $(document).ready(function() {

	   // Datepicker
		$(function() {
			$( "#FatturaData" ).datepicker({
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
		});

    });
   </script>

	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero wizart">
			<form id="application_form" class="brain_formModifica" name="application_form" method="post" action="#">

				<div class="brain_data-content"><?
				    $page->create_textbox_hidden("action",$action);
				    $page->create_textbox_hidden("Tipo",$tipo);
				    $page->create_textbox_hidden("PrenotazioneId",$PrenotazioneId);
				    $page->create_textbox_hidden("MovimentoId",$MovimentoId);
				    $page->create_textbox_hidden("PrenotazioneCodice",$prenotazione['CodicePrenotazione']);
				    $page->create_textbox_hidden("FatturaInCloudIdRimborsata",$fatturaInCloudIdRimborsata);
				    ?>
					<h3><?=$dizionario['biglietto']['info_generali']?></h3>
					<?php
					$page->create_textbox($dizionario['gestore']['ragione_sociale']."/".$dizionario['regole']['nome'],"FatturaInCloudViaggiatore[Nome]","Nome", "", 0,"brain_campiform", array("class"=>"'required'"), "", "5");
					$page->create_textbox($dizionario['gestore']['partita_iva'],"FatturaInCloudViaggiatore[PIVA]","PIVA", "", 0,"brain_campiform", array("class"=>"'required'"), "", "5");
					$page->create_textbox($dizionario['generale']['codice_fiscale'],"FatturaInCloudViaggiatore[CF]","CF", "", 0,"brain_campiform", array("class"=>"'required'"), "", "5");
					
					print(" <br style=\"clear:both;\"/>");
					
					?>
					<h3><?=$dizionario['biglietto']['sede']?></h3>
					<?php
					$page->create_textbox($dizionario['generale']['indirizzo'],"IndirizzoVia","IndirizzoVia",'',1,"brain_campiform ",array("class"=>"'required'"),"","35");
					$page->create_textbox($dizionario['comune']['cap'],"IndirizzoCap","IndirizzoCap",'',1,"brain_campiform",array("class"=>"'required digits'"),"","5","5");
					$page->create_textbox($dizionario['comune']['provincia'],"IndirizzoProvincia","IndirizzoProvincia",'',1,"brain_campiform",array("class"=>"'required'"),"","5","5");
					print(" <br style=\"clear:both;\"/>");
					
					$page->create_select($dizionario['comune']['nazione'],"IndirizzoStato","IndirizzoStato","brain_campiform",$arr_nazione_residenza,$NazioneResidenzaId,"NazioneId","Nazione",array("class"=>"'required'"),1);
					$page->create_textbox($dizionario['generale']['comune'],"IndirizzoComune","IndirizzoComune",'',1,"brain_campiform",array("class"=>"'required'"),'');
					
					print("<br style=\"clear:both;\"/>");
					?>
					<h3><?=$dizionario['biglietto']['info_contatti']?></h3>
					<?php 
					$page->create_textbox($dizionario['generale']['emailpec'],"PEC","PEC",'',1,"brain_campiform ",array("class"=>"'required'"),"","35");
					$page->create_textbox($dizionario['generale']['codice_destinatario'],"Codice_destinatario","Codice_destinatario",'',1,"brain_campiform ",array("class"=>""),"","35");
					$page->create_textbox($dizionario['generale']['email'],"Email","Email", "", 0,"brain_campiform", "", "", "5");
					$page->create_textbox($dizionario['pre']['tel'],"Tel","Tel", "", 0,"brain_campiform", "", "", "5");
					print("<br style=\"clear:both;\"/>");
					?>
					<h3><?=$dizionario['biglietto']['info_fattura']?></h3>
					<?php
					$page->create_select($dizionario['generale']['stato'],"FatturaStato","FatturaStato","brain_campiform",$arr_stato, 0,"FatturaStatoId","FatturaStato",array("class"=>"'required'"),1);
					$page->create_textbox($dataFattura,"FatturaData","FatturaData", "", 0,"brain_campiform", "", "", "5");				
				?>
				</div>
				<div class="divSubmit"><?
					$page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
				?></div>
			</form>
		</div>
	</div>
<?php
}
?>