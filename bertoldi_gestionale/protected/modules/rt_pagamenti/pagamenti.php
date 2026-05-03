<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<?php 
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.Form.php");
include_once($classespath_."class.DT.php");
include_once($classespath_."class.PagamentoTipo.php");

global $ModuloId;
global $user;

$ModuloId=35;// modulo base mediazione


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
		if(!isset($_REQUEST['do'])){
	    	$do='';
	    } else {
	    	$do=$_REQUEST['do'];
	    }

		switch($do) {

			case "add":
				$ModuloId=35;
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					add();
				else
					$errore->stampa_errore(2);

				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
			case "edit":
				$ModuloId=35;
				$FunzioneId=4;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					add($_GET['PagamentoTipoId']);
				else
					$errore->stampa_errore(2);
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
			default:
				$FunzioneId=1;
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
// se l'utente non � loggato
else {
	header("Location: /logout.php");
}

function show_list() {
	global $user, $HtmlCommon, $ModuloId, $db, $dizionario;
	
	$HtmlCommon->html_titolo_pagina($dizionario['pagamento']['titolo_gestione'], 0, "", "");
	$HtmlCommon->html_titolo_box($dizionario['pagamento']['titolo_gestione']);

	$db= new Database();
	$db->connect();
	      
	include_once("pagamenti_datatable.php");
 
	$aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);
	if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_pagamenti','pagamenti.php?do=add',$dizionario['pagamento']['aggiungi']);
	?>   
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    	<thead>
            <tr class="brain_tabellaTr">
            	<th width="10%"><?=$dizionario['generale']['stato']?></th>
				<th width="50%"><?=$dizionario['pagamento']['et_pagamento']?></th>
				<th width="50%"><?=$dizionario['pagamento']['et_rimborso']?></th>
				<th width="10%"><?=$dizionario['pagamento']['sup_fisso']?></th>
                <th width="10%"><?=$dizionario['pagamento']['sup_perc']?></th>
                <th width="10%"><?=$dizionario['pagamento']['tempo_scadenza']?></th>
                <th width="10%"><?=$dizionario['pagamento']['sms_stato']?></th>
                <th width="10%"><?=$dizionario['pagamento']['email_stato']?></th>
                <th width="10%"><?=$dizionario['gestore']['gestore']?></th>
                <th width="10%"><?=$dizionario['pagamento']['rimborso']?></th>
                <th width="5%"><?=$dizionario['generale']['edita']?></th>
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
				<th><input type="hidden" /></th> 
			</tr>
		</thead>
		<tbody> 
			<tr>
				<td colspan="11" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		<tfoot> 
			<tr> 
				<td colspan="11"></td>
			</tr> 
		</tfoot> 
	</table>
	<?   
	$db->close();
}

function add($PagamentoTipoId = null) {
	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();
	
	$page = new Form();
	$dt = new DT();
	
	$pagamento = array();
	if(isset($PagamentoTipoId)) {
		$pagamentoObj = new PagamentoTipo($PagamentoTipoId);
		$pagamentoObj->conn = $db;
		$pagamentoObj->inizializzaDatiGenerali();
		$pagamento = $pagamentoObj->DatiGenerali;
		
		$HtmlCommon->html_titolo_pagina($dizionario['pagamento']['titolo_modifica'], 1, "rt_pagamenti", "pagamenti.php");
		$HtmlCommon->html_titolo_box ($dizionario['pagamento']['titolo_modifica']);
	} else {
		$HtmlCommon->html_titolo_pagina($dizionario['pagamento']['titolo_inserisci'], 1, "rt_pagamenti", "pagamenti.php");
		$HtmlCommon->html_titolo_box ($dizionario['pagamento']['titolo_inserisci']);
	}
	
	$arr_stato[]= array("StatoId" => '0', "Stato" => $dizionario['generale']['non_attivo']);
	$arr_stato[]= array("StatoId" => '1', "Stato" => $dizionario['generale']['attivo']);
	
	$arr_gestorePrimario[]= array("GestorePrimarioId" => '0', "GestorePrimario" => $dizionario['generale']['solo_agenzia']);
	$arr_gestorePrimario[]= array("GestorePrimarioId" => '1', "GestorePrimario" => $dizionario['generale']['solo_gestore']);
	$arr_gestorePrimario[]= array("GestorePrimarioId" => '2', "GestorePrimario" => $dizionario['generale']['entrambi']);
	
	$arr_visualizzaInRimborso[]= array("VisualizzaInRimborsoId" => '0', "VisualizzaInRimborso" => $dizionario['generale']['no']);
	$arr_visualizzaInRimborso[]= array("VisualizzaInRimborsoId" => '1', "VisualizzaInRimborso" => $dizionario['generale']['si']);
	
	include_once("pagamenti_validator.php");
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero wizart">
			<form id="application_form" class="brain_formModifica" name="application_form" method="post" action="#">

				<div class="brain_data-content"><?
					if($PagamentoTipoId == null) {
						$page->create_textbox_hidden("action","AggiungiPagamento");
					} else {
						$page->create_textbox_hidden("action","ModificaPagamento");
						$page->create_textbox_hidden("PagamentoTipoId", $PagamentoTipoId);
					}
					
					$page->create_textbox($dizionario['generale']['nome'].":", "Nome", "Pagamento[PagamentoTipo]", (isset($pagamento['PagamentoTipo']))? $pagamento['PagamentoTipo'] : "", 1, "brain_campiform", array("class"=>"'required'"));
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_select($dizionario['pagamento']['visualizza_rimborso'], "Pagamento[VisualizzaInRimborso]", "VisualizzaInRimborso", "brain_campiform", $arr_visualizzaInRimborso, (isset($pagamento['VisualizzaInRimborso']))? $pagamento['VisualizzaInRimborso'] : 0, "VisualizzaInRimborsoId", "VisualizzaInRimborso", array("class"=>"'required'"), 1);
					$page->create_textbox($dizionario['pagamento']['et_rimborso'].":", "EtichettaRimborso", "Pagamento[EtichettaRimborso]", (isset($pagamento['EtichettaRimborso']))? $pagamento['EtichettaRimborso'] : "", 0, "brain_campiform");
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_textbox($dizionario['pagamento']['sup_fisso'].":", "SupplementoFisso", "Pagamento[SupplementoFisso]", (isset($pagamento['SupplementoFisso']))? $pagamento['SupplementoFisso'] : "", 0, "brain_campiform");
					$page->create_textbox($dizionario['pagamento']['sup_perc'].":", "SupplementoPercentuale", "Pagamento[SupplementoPercentuale]", (isset($pagamento['SupplementoPercentuale']))? $pagamento['SupplementoPercentuale'] : "", 0, "brain_campiform");
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_textbox($dizionario['pagamento']['tempo_scadenza'].":", "TempoScadenza", "Pagamento[TempoScadenza]", (isset($pagamento['TempoScadenza']))? $pagamento['TempoScadenza'] : "", 0, "brain_campiform");
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_input_checkbox($dizionario['pagamento']['invio_sms'], "MsgSMSStato", "Pagamento[MsgSMSStato]", 1, 0, "brain_campoForm", (isset($pagamento['MsgSMSStato']) && $pagamento['MsgSMSStato'])? array("checked"=>"checked") : null);
					$page->create_texarea($dizionario['pagamento']['testo_sms'], "MsgSMSTesto", "Pagamento[MsgSMSTesto]", (isset($pagamento['MsgSMSTesto']))? $pagamento['MsgSMSTesto'] : "", 0, "brain_campoForm");
					
					print("<br style=\"clear:both;\"/>");
					print("<br style=\"clear:both;\"/>");
						
					$page->create_input_checkbox($dizionario['pagamento']['invio_email'], "MsgEmailStato", "Pagamento[MsgEmailStato]", 1, 0, "brain_campoForm", (isset($pagamento['MsgEmailStato']) && $pagamento['MsgEmailStato'])? array("checked"=>"checked") : null);
					$page->create_textbox($dizionario['pagamento']['ogg_email'], "OggettoEmail", "Pagamento[SubjectEmailTesto]", (isset($pagamento['SubjectEmailTesto']))? $pagamento['SubjectEmailTesto'] : "", 0, "brain_campoForm");
					$page->create_texarea($dizionario['pagamento']['testo_email'], "MsgEmailTesto", "Pagamento[MsgEmailTesto]", (isset($pagamento['MsgEmailTesto']))? $pagamento['MsgEmailTesto'] : "", 0, "brain_campoForm");
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_select($dizionario['generale']['stato'], "Pagamento[Stato]", "Stato", "brain_campoForm", $arr_stato, (isset($pagamento['Stato']))? $pagamento['Stato'] : 1, "StatoId", "Stato", array("class"=>"'required'"), 1);
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_select($dizionario['pagamento']['gestore_primario'], "Pagamento[GestorePrimario]", "GestorePrimario", "brain_campoForm", $arr_gestorePrimario, (isset($pagamento['GestorePrimario']))? $pagamento['GestorePrimario'] : 0, "GestorePrimarioId", "GestorePrimario", array("class"=>"'required'"), 1);
				?>
				</div>
				<div class="divSubmit"><?
					$page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
				?></div>
			</form>
		</div>
	</div>
<?	
}
?>