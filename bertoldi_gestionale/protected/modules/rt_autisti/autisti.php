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
include_once($classespath_."class.Autisti.php");

global $ModuloId;
global $user;

$ModuloId=36;// modulo base mediazione


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
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					add();
				else
					$errore->stampa_errore(2);

				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
			case "edit":
				$FunzioneId=4;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					add($_GET['AutistiId']);
				else
					$errore->stampa_errore(2);
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
			case "editAccount":
				$FunzioneId=4;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					editAccount($_REQUEST['AutistiId']);
				else
					Errors::$ErrorePermessiModuloFunzione;
				// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
				break;
			case "addPin":
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					addPin($_REQUEST['AutistiId']);
				else
					Errors::$ErrorePermessiModuloFunzione;
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
	
	$HtmlCommon->html_titolo_pagina($dizionario['autista']['titolo_gestione'], 0, "", "");
	$HtmlCommon->html_titolo_box($dizionario['autista']['titolo_gestione']);

	$db= new Database();
	$db->connect();
	      
	include_once("autisti_datatable.php");
 
	$aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);
	if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_autisti','autisti.php?do=add',$dizionario['autista']['aggiungi']);
	?>   
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    	<thead>
            <tr class="brain_tabellaTr">
            	<th width="10%"><?=$dizionario['generale']['stato']?></th>
				<th width="10%"><?=$dizionario['generale']['cognome']?></th>
				<th width="10%"><?=$dizionario['generale']['nome']?></th>
                <th width="10%"><?=$dizionario['generale']['telefono']?></th>
                <th width="10%"><?=$dizionario['generale']['cell']?></th>
                <th width="10%"><?=$dizionario['generale']['email']?></th>
                <th width="5%"><?=$dizionario['generale']['edita']?></th>
				<th width="5%"><?=$dizionario['generale']['gestisci_dispositivo']?></th>
			</tr>
            
			<tr class="brain_tabellaFilter">
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
				<td colspan="8" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		<tfoot> 
			<tr> 
				<td colspan="8"></td>
			</tr> 
		</tfoot> 
	</table>
	<?   
	$db->close();
}

function add($AutistaId = null) {
	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();
	
	$page = new Form();
	$dt = new DT();
	
	$autista = array();
	if(isset($AutistaId)) {
		$autistaObj = new Autista($AutistaId);
		$autistaObj->conn = $db;
		$autistaObj->inizializzaDatiGenerali();
		$autista = $autistaObj->DatiGenerali;
		
		$HtmlCommon->html_titolo_pagina($dizionario['autista']['titolo_modifica'], 1, "rt_autisti", "autisti.php");
		$HtmlCommon->html_titolo_box ($dizionario['autista']['titolo_modifica']);
	} else {
		$HtmlCommon->html_titolo_pagina($dizionario['autista']['titolo_inserisci'], 1, "rt_autisti", "autisti.php");
		$HtmlCommon->html_titolo_box ($dizionario['autista']['titolo_inserisci']);
	}
	
	$arr_stato[]= array("StatoId" => '0', "Stato" => $dizionario['generale']['non_attivo']);
	$arr_stato[]= array("StatoId" => '1', "Stato" => $dizionario['generale']['attivo']);
	
	$arr_sesso[]= array("SessoId" => '1',"Sesso" => $dizionario['generale']['maschio']);
	$arr_sesso[]= array("SessoId" => '2',"Sesso" => $dizionario['generale']['femmina']);
	$arr_sesso[]= array("SessoId" => '3',"Sesso" => $dizionario['generale']['nd']);
	
	include_once("autisti_validator.php");
	?>
	<script type="text/javascript"> 
    $(document).ready(function() {
        
	   // Datepicker
		$(function() {
			$( "#DataDiNascita" ).datepicker({
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
					if($AutistaId == null) {
						$page->create_textbox_hidden("action","AggiungiAutista");
					} else {
						$page->create_textbox_hidden("action","ModificaAutista");
						$page->create_textbox_hidden("AutistaId", $AutistaId);
					}
					
					$page->create_textbox($dizionario['generale']['cognome'].":", "Cognome", "Autista[Cognome]", (isset($autista['Cognome']))? $autista['Cognome'] : "", 1, "brain_campoForm", array("class"=>"'required'"));
					$page->create_textbox($dizionario['generale']['nome'].":", "Nome", "Autista[Nome]", (isset($autista['Nome']))? $autista['Nome'] : "", 1, "brain_campoForm", array("class"=>"'required'"));
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_textbox($dizionario['generale']['telefono'].":", "Telefono", "Autista[Telefono]", (isset($autista['Telefono']))? $autista['Telefono'] : "", 0, "brain_campoForm", null);
					$page->create_textbox($dizionario['generale']['cell'].":", "Cellulare", "Autista[Cellulare]", (isset($autista['Cellulare']))? $autista['Cellulare'] : "", 0, "brain_campoForm", null);
					$page->create_textbox($dizionario['generale']['email'].":", "Email", "Autista[Email]", (isset($autista['Email']))? $autista['Email'] : "", 0, "brain_campoForm", null);
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_select($dizionario['generale']['sesso'].":","Autista[SessoId]","SessoId","brain_campoForm",$arr_sesso,(isset($autista['SessoId']))? $autista['SessoId'] : 0,"SessoId","Sesso",null,0);
					$page->create_textbox($dizionario['autista']['data_anscita'].":", "DataDiNascita", "Autista[DataDiNascita]", (isset($autista['DataDiNascita']))? $dt->format($autista['DataDiNascita'], "Y-m-d", "d/m/Y") : "", 0, "brain_campoForm", array("class"=>"'italianDate'"), "", "10");
					$page->create_textbox($dizionario['autista']['comune_nascita'].":", "ComuneDiNascita", "Autista[ComuneDiNascita]", (isset($autista['ComuneDiNascita']))? $autista['ComuneDiNascita'] : "", 0, "brain_campoForm", null);
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_textbox($dizionario['generale']['indirizzo'].":", "Indirizzo", "Autista[Indirizzo]", (isset($autista['Indirizzo']))? $autista['Indirizzo'] : "", 0, "brain_campoForm", null);
					$page->create_textbox($dizionario['generale']['citta'].":", "Citta", "Autista[Citta]", (isset($autista['Citta']))? $autista['Citta'] : "", 0, "brain_campoForm", null);
					$page->create_textbox($dizionario['comune']['cap'].":", "Cap", "Autista[Cap]", (isset($autista['Cap']))? $autista['Cap'] : "", 0, "brain_campoForm", null);
					$page->create_textbox($dizionario['comune']['provincia'].":", "Provincia", "Autista[Provincia]", (isset($autista['Provincia']))? $autista['Provincia'] : "", 0, "brain_campoForm", null);
					$page->create_textbox($dizionario['autista']['nazionalita'].":", "Nazionalita", "Autista[Nazionalita]", (isset($autista['Nazionalita']))? $autista['Nazionalita'] : "", 0, "brain_campoForm", null);
					$page->create_textbox($dizionario['generale']['codice_fiscale'].":", "CodiceFiscale", "Autista[CodiceFiscale]", (isset($autista['CodiceFiscale']))? $autista['CodiceFiscale'] : "", 0, "brain_campoForm", null);
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_select($dizionario['generale']['stato'], "Autista[Stato]", "Stato", "brain_campoForm", $arr_stato, (isset($autista['Stato']))? $autista['Stato'] : 1, "StatoId", "Stato", array("class"=>"'required'"), 1);
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

function editAccount($AutistaId) {
	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();

	$page = new Form();
	$dt = new DT();
	include_once("autisti_validator.php");

	$autista = array();

	$autistaObj = new Autista($AutistaId);
	$autistaObj->conn = $db;
	$autistaObj->inizializzaDatiGenerali();
	$autista = $autistaObj->DatiGenerali;

	$HtmlCommon->html_titolo_pagina($dizionario['autista']['pagina_account'], 1, "rt_autisti", "autisti.php");
	$HtmlCommon->html_titolo_box ($dizionario['autista']['pagina_account']);
	
	$arr_permesso[]= array("PermessoId" => '0',"Permesso" => $dizionario['generale']['no']);
	$arr_permesso[]= array("PermessoId" => '1',"Permesso" => $dizionario['generale']['si']);

	$clients = $autistaObj->getPin($AutistaId);

	$arr_operatori = $autistaObj->getAllOperatoriForSelect();
	?>
   

   
   
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero wizart">
			<form id="application_form" class="brain_formModifica" name="application_form" method="post" action="#">
				<div class="brain_formModifica">
					<div class="brain_data-content"><?
						$page->create_textbox_hidden("action","ModificaAccount");
						$page->create_textbox_hidden("AutistaId", $AutistaId);
						
						print("<h2>".$dizionario['autista']['autista_selezionato'].":".$autista['Nome']." ".$autista['Cognome']."</h2>");
						print("<h1><h1/>");
						
						
						$page->create_textbox($dizionario['autista']['username'].":", "Username", "Autista[Username]", (isset($autista['Username']))? $autista['Username'] : "", 0, "brain_campoForm", null);
						?>
						
						<div class="brain_campoForm">
							<label for="Autista[Password]"> <?php echo $dizionario['autista']['password'];?>: </label>
							<br>
							<input id="Password" class="valid" type="password" name="Autista[Password]" maxlength="255" size="20" value=""/>
						</div>
						<?php 
						print("<br style=\"clear:both;\"/>");
						
						$page->create_select($dizionario['autista']['utente_associato'].":","Autista[OperatoreId]","OperatoreId","brain_campoForm",$arr_operatori,(isset($autista['OperatoreId']))? $autista['OperatoreId'] : "-1","OperatoreId","Operatore",null,0);
						
						print("<br style=\"clear:both;\"/>");
						
						$page->create_select($dizionario['autista']['permesso_visualizza'].":","Autista[AppVisualizzaCorse]","PermessoId","brain_campoForm",$arr_permesso,(isset($autista['AppVisualizzaCorse']))? $autista['AppVisualizzaCorse'] : 0,"PermessoId","Permesso",null,0);
						
						print("<br style=\"clear:both;\"/>");
						
						$page->create_select($dizionario['autista']['permesso_bigliettazione'].":","Autista[AppBigliettazioneABordo]","PermessoId","brain_campoForm",$arr_permesso,(isset($autista['AppBigliettazioneABordo']))? $autista['AppBigliettazioneABordo'] : 0,"PermessoId","Permesso",null,0);

						print("<br style=\"clear:both;\"/>");
						
					?>
					</div>
					<div class="divSubmit"><?
						$page->create_button("Salva","Salva",$dizionario['autista']['mod_credenziali'],"brain_salva","submit");
					?></div>
					<br style="clear:both;"/>
					<br style="clear:both;"/>
					<div class="GestoreSedeAdd">
						<a class="brain_add" href="#"
							onclick="javascript:ExternalLoad('rt_autisti','autisti.php?do=addPin&AutistiId=<?=$AutistaId?>');"
							title="aggiungi operazione contabile"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['autista']['aggiungi_pin']?>
						</a>
					</div>
					<br />
					<table width="50%" cellspacing="0" cellpadding="0" border="0" id="gestoreElencoAule">
						<tbody>
							<tr class="rowIntestazione">
								<td><?=$dizionario['autista']['num_pin']?></td>
								<td><?=$dizionario['autista']['pin']?></td>
								<td><?=$dizionario['autista']['elimina']?></td>
							</tr>
						
							
								<?php 
									foreach ($clients as $chiave => $valore){
										?>
										<tr>
										<td><?php echo $chiave+1;?></td>
										<td><?php echo $valore['Pin'];?></td>
										<td style="text-align:center; width:5%;">
											<a href="#" onclick="javascript:CancellaPin(<?=$valore['ClientAppId']?>);" title="<?=$dizionario['autista']['elimina']?>">
												<img alt="cancella pin" title="cancella pin" src=/images/remove-icon.png />
											</a>
										</td>
										</tr>
										<?php
									}
								?>
								
							
						</tbody>
					</table>
				</div>
			</form>
			
		</div>
	</div>
<?	
}

function addPin($AutistaId){
	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();
	
	$page = new Form();
	$dt = new DT();
	include_once("autisti_validator.php");
	
	$autista = array();
	
	$autistaObj = new Autista($AutistaId);
	$autistaObj->conn = $db;
	
	$HtmlCommon->html_titolo_pagina($dizionario['autista']['pagina_pin'], 0, "rt_autisti", "autisti.php");
	$HtmlCommon->html_titolo_box ($dizionario['autista']['pagina_pin']);
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero">
			<form id="application_form" name="application_form" method="post" action="#">
				<div class="brain_formModifica">
					<div class="brain_data-content"><?
						$page->create_textbox_hidden("action","AggiungiPin");
						$page->create_textbox_hidden("AutistaId", $AutistaId);
	
						print("<h1>".$dizionario['autista']['inserisci_pin']."<h1/>");
	
						?>
							<div class="brain_campoForm">
								<label for="pin"> <?=$dizionario['autista']['pin']?>: </label>
								<br>
								<input id="Pin" type="text" value="" name="pin" maxlength="4" size="20">
							</div>
						<?php 
						print("<br style=\"clear:both;\"/>");	
						?>
					</div>
					<div class="divSubmit"><?
						$page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
					?></div>
					<br style="clear:both;"/>
				</div>
			</form>
		</div>
	</div>
	<?php 
}
?>