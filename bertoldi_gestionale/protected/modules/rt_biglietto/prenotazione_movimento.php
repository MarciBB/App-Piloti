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
include_once($classespath_."class.PagamentoTipo.php");
include_once($classespath_."class.PrenotazioneMovimento.php");
include_once($classespath_."class.CanalePagamento.php");
include_once($classespath_."class.Prenotazione.php");

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
				if (sizeof($permesso))
					add($_GET['PrenotazioneId'], $_GET['CorsaId']);
				else
					$errore->stampa_errore(2);

				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
			case "edit":
				$ModuloId=2;
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					add($_GET['PrenotazioneId'], $_GET['CorsaId'], $_GET['PrenotazioneMovimentoId']);
				else
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
// se l'utente non � loggato
else {
	header("Location: /logout.php");
}

function add($PrenotazioneId, $CorsaId, $PrenotazioneMovimentoId = null) {

	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();

	$page = new Form();
	$dt = new DT();

	$PagamentoTipo = new PagamentoTipo();
	$PagamentoTipo->conn = $db;

	$CanalePagamento = new CanalePagamento();
	$CanalePagamento->conn = $db;
	$allCanalePagamento = $CanalePagamento->getAllForSelect();

	$fatturaOptions = array();
	$fatturaOptions[] = array('Id' => 1, 'Label' => 'Fattura da Bertoldi ad agenzia');
	$fatturaOptions[] = array('Id' => 2, 'Label' => 'Emessa fattura da agenzia a Bertoldi');
	$fatturaOptions[] = array('Id' => 0, 'Label' => 'Non gestita');

	$action = null;
	$movimento = array();
	$scontrinoInvioAuto = 0;
	if(isset($PrenotazioneMovimentoId)) {
		$movimentoObj = new PrenotazioneMovimento($PrenotazioneMovimentoId);
		$movimentoObj->conn = $db;
		$movimentoObj->inizializzaDatiGenerali();
		$movimento = $movimentoObj->DatiGenerali;
		$scontrinoInvioAuto = $movimento['ScontrinoInvioAuto'];
		
		$HtmlCommon->html_titolo_pagina($dizionario['movimento']['titolo_modifica'],0 ,"", "");
		$HtmlCommon->html_titolo_box ($dizionario['movimento']['titolo_modifica']);
		
		$action = 'edit';
	}else{
		$movimento['Data'] = date('Y-m-d H:i:s', time());
		
		$HtmlCommon->html_titolo_pagina($dizionario['movimento']['titolo_inserisci'],0 ,"", "");
		$HtmlCommon->html_titolo_box ($dizionario['movimento']['titolo_inserisci']);
		
		$action = 'add';
	}
	
	$prenotazioneObj = new Prenotazione();
	$prenotazioneObj->Id = $PrenotazioneId;
	$prenotazioneObj->conn = $db;
	$prenotazioneObj->inizializzaDatiGenerali();
	$prenotazione = $prenotazioneObj->DatiGenerali;
	$totaliImporti = $prenotazioneObj->GetTotaliPrenotazione();

	$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = $PrenotazioneId AND Direzione = 'A'";
	$percorso = $db->query_first($sql);
	$sql = "SELECT * FROM Gestore WHERE GestoreId = " . $prenotazione['GestoreIdRef'];
	$gestore = $db->query_first($sql);
	
	// recupero i metodi di pagamento o rimborso
	if (isset($movimento['TipoMovimento']) && $movimento['TipoMovimento'] == 'R') {
		if(!$gestore['Verificato']) {
			$tipo = 12;
			$allPagamentiTipo = $PagamentoTipo->getTipoRimborsoForSelect($tipo);
		} else {
			$allPagamentiTipo = $PagamentoTipo->getAllRimborsoForSelect();
		}
		
		foreach ($allPagamentiTipo as $index => $pagamento) {
			$allPagamentiTipo[$index]['PagamentoTipo'] = $pagamento['EtichettaRimborso'];
		}
	} else {
		if(isset($movimento["PagamentoTipoId"])){
			$tipo = $movimento["PagamentoTipoId"];
		} else {
			$tipo = 0;
		}
		
		if(!$gestore['Verificato'] && !$user->IsAdmin) {
			//se l'agenzia non � verificata attivo il pagamento tramite coupon
			if($movimento["PagamentoTipoId"] <> 12 && $movimento["TipoMovimento"] == 'I') {
				$tipo = $movimento["PagamentoTipoId"];
			} else {
				$tipo = 12;
			}	
			$allPagamentiTipo = $PagamentoTipo->getTipoFromSelect($tipo);
		} else {
			$allPagamentiTipo = $PagamentoTipo->getAllForSelect($tipo);
		}
	}
	
	// rimuovo il metodi di pagamento "a bordo" se la prenotazione � pagata
	if ($action == 'edit' && $prenotazione['Pagato']) {
		$deleteElements = array();
		foreach ($allPagamentiTipo as $index => $value) {
			if ($value['PagamentoTipoId'] == "7") {
				$deleteElements[] = $index;
				break;
			}
		}
		foreach ($deleteElements as $v){
			array_splice($allPagamentiTipo, $v, 1);
// 			unset($allPagamentiTipo[$v]);
		}
	}
	include_once("prenotazione_movimento_validator.php");
	?>

	<script type="text/javascript">
    $(document).ready(function() {

	   // Datepicker
		$(function() {
			$( "#DataMovimento" ).datepicker({
			maxDate: new Date(),
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

			$( "#DataPagamento" ).datepicker({
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
		            dateFormat: 'dd/mm/yy',
		            maxDate: '0'
			});

			$( "#DataScadenza" ).datepicker({
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
		            dateFormat: 'dd/mm/yy',
			        minDate: '0'
			});

			$("#OraScadenza").mask("99:99");
			$("#OraMovimento").mask("99:99");
		});

		<?php if(!$gestore['Verificato'] && $tipo == 12) { ?>
			$('#TipoPagamento').val(12);
			checkCoupon();
			$('#Supplemento').val('0,00');
		<?php } ?>
    });
   </script>

	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero wizart">
			<form id="application_form" class="brain_formModifica" name="application_form" method="post" action="#">

				<div class="brain_data-content"><?
					if($PrenotazioneMovimentoId == null) {
						$page->create_textbox_hidden("action","AggiungiPrenotazioneMovimento");
					} else {
						$page->create_textbox_hidden("action","ModificaPrenotazioneMovimento");
						$page->create_textbox_hidden("PrenotazioneMovimentoId", $PrenotazioneMovimentoId);
					}

					?>
					<h3><?=$dizionario['movimento']['info_generali']?></h3>
					<?php
					$page->create_select($dizionario['movimento']['tipo_pagamento'],"Movimento[PagamentoTipoId]","TipoPagamento","brain_campiform",$allPagamentiTipo,(isset($movimento['PagamentoTipoId']))? $movimento['PagamentoTipoId'] : 0,"PagamentoTipoId","PagamentoTipo", array("class"=>"'required'"), 1);

					if(isset($PrenotazioneMovimentoId) && isset($movimento['TipoMovimento']) && ($movimento['TipoMovimento'] == 'I' || $movimento['TipoMovimento'] == 'R')) {	
						$disable = true;
					} else {
						$disable = false;
					}
					?> 
					
					<input style="margin: 23px 0px 0px 0px !important;
									padding: 0px !important;
									opacity: initial;
									height: auto;
									width: auto;
									position: inherit;"
							id = "Movimento[ScontrinoInvioAuto]" name="Movimento[ScontrinoInvioAuto]" type="checkbox" value="true" <?php if($scontrinoInvioAuto == 1){ echo  "checked='checked'";}?>  <?= ($disable) ? 'disabled="disabled"':''?> /> 
					<label style="background: none !important;
									height: inherit;
									width: auto;
									margin: 24px 0px 0px 6px;" 
							id="label_Movimento[ScontrinoInvioAuto]" for="Movimento[ScontrinoInvioAuto]"><?= $dizionario['biglietto']['scontrino_invia_auto']?></label>
					
					<?php
					print("<br style=\"clear:both;\"/>");
					print("<br style=\"clear:both;\"/>");

					$page->create_textbox($dizionario['movimento']['data_movimento'], "DataMovimento", "DataMovimento", (isset($movimento['Data']))? $dt->format($movimento['Data'], "Y-m-d H:i:s", "d/m/Y") : "", 1, "brain_campiform", array("class"=>"'required italianDate'"), "", "10");
					$page->create_textbox($dizionario['movimento']['ora_movimento'],"OraMovimento","OraMovimento", (isset($movimento['Data']))? $dt->format($movimento['Data'], "Y-m-d H:i:s", "H:i") : "", 0,"brain_campiform", "", "", "4");

					print("<br style=\"clear:both;\"/>");

					if ($action == 'edit' && $prenotazione['Pagato']) {
						$page->create_textbox($dizionario['movimento']['importo'],"Importo", "", number_format($movimento['Importo'], 2, ",", ""), 1, "brain_campiform", array("class"=>"'required numberDE'", "disabled"=>"true"));
						$page->create_textbox_hidden("Movimento[Importo]", number_format($movimento['Importo'], 2, ",", ""));
					} else {
						$page->create_textbox($dizionario['movimento']['importo'],"Importo", "Movimento[Importo]", (isset($movimento['Importo']))? number_format($movimento['Importo'], 2, ",", "") : number_format($totaliImporti['TotaleResiduo'], 2, ",", ""), 1, "brain_campiform", array("class"=>"'required numberDE'", "readonly"=>"true"));
					}
					
					if ($action == 'edit' && $prenotazione['Pagato']) {
						$page->create_textbox($dizionario['movimento']['supplemento'],"Supplemento", "", number_format($movimento['Supplemento'], 2, ",", "."), 1, "brain_campiform", array("class"=>"'required'", "disabled"=>"true"));
						$page->create_textbox_hidden("Movimento[Supplemento]", number_format($movimento['Supplemento'], 2, ",", "."));
					} else {
						$page->create_textbox($dizionario['movimento']['supplemento'],"Supplemento", "Movimento[Supplemento]", (isset($movimento['Supplemento']))? number_format($movimento['Supplemento'], 2, ",", ".") : "", 1, "brain_campiform", array("class"=>"'required'", "readonly"=>"true"));
					}

					print("<br style=\"clear:both;\"/>");
					
					$page->create_texarea($dizionario['movimento']['causale'],"Causale", "Movimento[Causale]", (isset($movimento['Causale']))? $movimento['Causale'] : "", 0, "brain_campiform");

					print("<br style=\"clear:both;\"/>");
					
					?>
					<h3><?=$dizionario['movimento']['info_scadenza']?></h3>
					<?php
					$page->create_textbox($dizionario['movimento']['data_scadenza'], "DataScadenza", "DataScadenza", (isset($movimento['Scadenza']))? $dt->format($movimento['Scadenza'], "Y-m-d H:i:s", "d/m/Y") : "", 0, "brain_campiform", array("class"=>"'italianDate'"), "", "10");
					$page->create_textbox($dizionario['movimento']['ora_scadenza'],"OraScadenza","OraScadenza", (isset($movimento['Scadenza']))? $dt->format($movimento['Scadenza'], "Y-m-d H:i:s", "H:i") : "", 0,"brain_campiform", "", "", "4");
					
					print("<br style=\"clear:both;\"/>");
					?>
					<h3><?=$dizionario['movimento']['info_registrazione']?></h3>
					<?php
					if ($action == 'edit' && $prenotazione['Pagato']) {
					} else {
						$page->create_textbox($dizionario['movimento']['codice_coupon'],"CodiceCoupon","CodiceCoupon", "", 0, "brain_campiform","");
						?><span id="risultatoControlloCodice"></span>
						<?php 
					}
					$page->create_textbox($dizionario['movimento']['data_pagamento'], "DataPagamento", "Movimento[DataPagamento]", (isset($movimento['DataPagamento']))? $dt->format($movimento['DataPagamento'], "Y-m-d H:i:s", "d/m/Y") : "", 0, "brain_campiform", array("class"=>"'italianDate'"), "", "10");
					
					if ($action == 'edit' && $prenotazione['Pagato']) {
						$page->create_textbox($dizionario['movimento']['importo_pagato'],"ImportoPagato","", number_format($movimento['ImportoPagato'], 2, ",", ""), 0, "brain_campiform",array("class"=>"'numberDE'", "disabled"=>"true"));
						$page->create_textbox_hidden("Movimento[ImportoPagato]", number_format($movimento['ImportoPagato'], 2, ",", ""));
					} else {
						if(isset($movimento['ImportoPagato']) && $movimento['ImportoPagato'] > 0) {
							$text_saldo = $dizionario['generale']['reset'];
						} else {
							$text_saldo = $dizionario['generale']['saldo'];
						}
						$page->create_textbox($dizionario['movimento']['importo_pagato']." <div id=\"spanSaldoReset\">(<a id=\"Saldo\" href=\"#\">$text_saldo</a>)</div>","ImportoPagato","Movimento[ImportoPagato]", (isset($movimento['ImportoPagato']))? number_format($movimento['ImportoPagato'], 2, ",", "") : "0,00", 0, "brain_campiform",array("class"=>"'numberDE'"));
					}
					
					
					$page->create_textbox($dizionario['movimento']['codice_pagamento'],"CodicePagamento","Movimento[CodicePagamento]", (isset($movimento['CodicePagamento']))? $movimento['CodicePagamento'] : "", 0, "brain_campiform","");
					$page->create_select($dizionario['movimento']['canale_pagamento'],"Movimento[CanalePagamentoId]","CanalePagamento","brain_campiform",$allCanalePagamento,(isset($movimento['CanalePagamentoId']))? $movimento['CanalePagamentoId'] : 0,"CanalePagamentoId","CanalePagamento", "", 0);
					print("<br style=\"clear:both;\"/>");
						
					$selectedFattura = isset($movimento['Fattura']) ? $movimento['Fattura'] : 0;
					$page->create_select('Fattura',"Movimento[Fattura]","Fattura","brain_campiform",$fatturaOptions,$selectedFattura,"Id","Label", "", 0);
					$page->create_texarea('Fattura note',"FatturaNote", "Movimento[FatturaNote]", (isset($movimento['FatturaNote']))? $movimento['FatturaNote'] : "", 0, "brain_campiform");
					
					print("<br style=\"clear:both;\"/>");
					$page->create_textbox_hidden("Movimento[TipoMovimento]", "I");
					$page->create_textbox_hidden("Movimento[PrenotazioneId]", $PrenotazioneId);
					$page->create_textbox_hidden("CorsaId", $CorsaId);
				?>
				</div>
				<div class="divSubmit"><?
					$page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
					$page->create_button("ControllaCoupon","ControllaCoupon",$dizionario['movimento']['controlla_coupon'],"brain_salva","button");
				?></div>
			</form>
		</div>
	</div>
<?
}
?>
