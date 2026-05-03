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
include_once($classespath_."class.Spesa.php");
include_once($classespath_."class.Fornitore.php");

global $ModuloId;
global $user;

$ModuloId=53;// modulo base mediazione


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
					add($_GET['SpesaId']);
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
	
	$HtmlCommon->html_titolo_pagina($dizionario['spese']['titolo_gestione'], 0, "", "");
	$HtmlCommon->html_titolo_box($dizionario['spese']['titolo_gestione']);

	$db= new Database();
	$db->connect();
	
	include_once("spese_validator.php");
	include_once("spese_datatable.php");
 
	$aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);
	if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_spese','spese.php?do=add',$dizionario['spese']['aggiungi']);
	?> 
<script type="text/javascript"> 
    $(document).ready(function() {
        
	   // Datepicker
		$(function() {
			$( "#DataDa" ).datepicker({
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
	<style>
		table td:nth-child(2) {
		  display: none;
		}
	</style>
	<div class="brain_servicebar">
		<a id="esportaSpese" style="color: #333;
		    float: right;
		    font-size: 13px;
		    padding: 3px 12px 6px 12px;
		    text-decoration: underline;" 
    href="#" title="<?php echo $dizionario['spese']['esporta']?>"><?php echo $dizionario['spese']['esporta']?></a>
	</div>
	<script>
		$(document).ready(function(){
			$('#esportaSpese').click(function(){
				self.location.href = 'protected/modules/rt_spese/exportSpeseExcel.php?sSearch_0='+$('.searchDataDa').val()
				+'&sSearch_1='+$('.searchDataA').val()
				+'&sSearch_2='+$('.searchDescrizione input').val()
				+'&sSearch_3='+$('.searchCosto input').val()
				+'&sSearch_4='+$('.searchTipoSpesa select').val()
				+'&sSearch_5='+$('.searchFornitore select').val()
				+'&sSearch_6='+$('.searchCategoria select').val()
				+'&sSearch_7='+$('.searchDestinazione select').val()
				+'&sSearch_8='+$('.searchPagamento select').val()
				+'&sSearch_9='+$('.searchPagato select').val();
			});
			
		});
	</script>
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    	<thead>
            <tr class="brain_tabellaTr">
            	<th width="10%"><?=$dizionario['spese']['data']?></th>
				<th width="10%" style="display:none;"><?=$dizionario['spese']['data']?></th>
				<th width="10%"><?=$dizionario['spese']['descrizione']?></th>
				<th width="10%"><?=$dizionario['spese']['costo']?></th>
				<th width="10%"><?=$dizionario['spese']['tipo_spesa']?></th>
				<th width="10%"><?=$dizionario['spese']['fornitore']?></th>
				<th width="10%"><?=$dizionario['spese']['categoria']?></th>
				<th width="10%"><?=$dizionario['spese']['destinazione']?></th>
				<th width="10%"><?=$dizionario['spese']['metodo_pagamento']?></th>
				<th width="10%"><?=$dizionario['spese']['pagato']?></th>
                <th width="5%"><?=$dizionario['spese']['azioni']?></th>
			</tr>
            
			<tr class="brain_tabellaFilter">
            	<th>Da<input type="date" class="searchDataDa"/><br>A<input class="searchDataA" type="date" /></th>
				<th style="display:none;"></th>
            	<th><span class="searchDescrizione"></span><input type="hidden" /></th> 
				<th><span class="searchCosto"></span><input type="hidden" /></th>
				<th><span class="searchTipoSpesa"></span><input type="hidden" /></th>
            	<th><span class="searchFornitore"></span><input type="hidden" /></th> 
				<th><span class="searchCategoria"></span><input type="hidden" /></th>  
				<th><span class="searchDestinazione"></span><input type="hidden" /></th>
            	<th><span class="searchPagamento"></span><input type="hidden" /></th> 
				<th><span class="searchPagato"></span><input type="hidden" />  				
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
				<td colspan="11" id="footerCosti"></td>
			</tr> 
		</tfoot> 
	</table>
	<?php   
	$db->close();
}

function add($SpesaId = null) {
	
	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();
	
	$page = new Form();
	$dt = new DT();
	
	$fornitore = array();
	$fornitoreObj = new Fornitore();
	$fornitoreObj->conn = $db;
	
	$spesa = array();
	$spesaObj = new Spesa();
	$spesaObj->conn = $db;
	if(isset($SpesaId)) {
		$spesaObj = new Spesa($SpesaId);
		$spesaObj->conn = $db;
		$spesaObj->inizializzaDatiGenerali();
		$spesa = $spesaObj->DatiGenerali;
		$HtmlCommon->html_titolo_pagina($dizionario['spese']['titolo_modifica'], 1, "rt_spese", "spese.php");
		$HtmlCommon->html_titolo_box ($dizionario['spese']['titolo_modifica']);
	} else {
		$HtmlCommon->html_titolo_pagina($dizionario['spese']['titolo_inserisci'], 1, "rt_spese", "spese.php");
		$HtmlCommon->html_titolo_box ($dizionario['spese']['titolo_inserisci']);
	}
	
	$arr_tipo[]= array("TipoId" => '0', "Tipo" => $dizionario['spese']['senza_tipo']);
	$arr_tipo[]= array("TipoId" => '1', "Tipo" => $dizionario['spese']['fissi']);
	$arr_tipo[]= array("TipoId" => '2', "Tipo" => $dizionario['spese']['variabili']);
	
	$arr_pagato[]= array("PagatoId" => '0', "Pagato" => $dizionario['generale']['no']);
	$arr_pagato[]= array("PagatoId" => '1', "Pagato" => $dizionario['generale']['si']);

	$arr_categorie = $spesaObj->getCategorie();

	$arr_destinazioni = $spesaObj->getDestinazioni();
		
	$arr_pagamenti = $spesaObj->getPagamenti();
	
	$arr_fornitori = $fornitoreObj->getAllForSelect();
	 
	include_once("spese_validator.php");
	?>
	<script type="text/javascript"> 
    $(document).ready(function() {
        
	   // Datepicker
		$(function() {
			$( "#Data" ).datepicker({
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

				<div class="brain_data-content"><?php
					if($SpesaId == null) {
						$page->create_textbox_hidden("action","AggiungiSpesa");
					} else {
						$page->create_textbox_hidden("action","ModificaSpesa");
						$page->create_textbox_hidden("SpesaId", $SpesaId);
					}
					
					$page->create_textbox($dizionario['spese']['data'].":", "Data", "Spesa[Data]", (isset($spesa['Data']))? $dt->format($spesa['Data'], "Y-m-d", "d/m/Y") : "", 0, "brain_campoForm", array("class"=>"'italianDate'"), "", "10");

					$page->create_textbox($dizionario['spese']['descrizione'].":", "Descrizione", "Spesa[Descrizione]", (isset($spesa['Descrizione']))? $spesa['Descrizione'] : "", 1, "brain_campoForm", array("class"=>"'required'"));
					
					$page->create_textbox($dizionario['spese']['costo'].":", "Costo", "Spesa[Costo]", (isset($spesa['Costo']))? $spesa['Costo'] : "", 1, "brain_campoForm", array("class"=>"'required numberDE'"));
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_select($dizionario['spese']['tipo_spesa'], "Spesa[TipoSpesa]", "TipoSpesa", "brain_campoForm", $arr_tipo, (isset($spesa['TipoSpesa']))? $spesa['TipoSpesa'] : 0, "TipoId", "Tipo", array("class"=>"'required'"), 1);
					
					$page->create_select($dizionario['spese']['fornitore'], "Spesa[FornitoreId]", "FornitoreId", "brain_campoForm", $arr_fornitori, (isset($spesa['FornitoreId']))? $spesa['FornitoreId'] : '', "FornitoreId", "Fornitore", array("class"=>"'required'"), 1);
					
					$page->create_select($dizionario['spese']['categoria'], "Spesa[CategoriaId]", "CategoriaId", "brain_campoForm", $arr_categorie, (isset($spesa['CategoriaId']))? $spesa['CategoriaId'] : 999, "CategoriaId", "Categoria", array("class"=>"'required'"), 1);
					
					
					$page->create_select($dizionario['spese']['destinazione'], "Spesa[DestinazioneId]", "DestinazioneId", "brain_campoForm", $arr_destinazioni, (isset($spesa['DestinazioneId']))? $spesa['DestinazioneId'] : 999, "DestinazioneId", "Destinazione", array("class"=>"'required'"), 1);
					
					
					$page->create_select($dizionario['spese']['metodo_pagamento'], "Spesa[MetodoPagamentoId]", "MetodoPagamentoId", "brain_campoForm", $arr_pagamenti, (isset($spesa['MetodoPagamentoId']))? $spesa['MetodoPagamentoId'] : 999, "PagamentoId", "Pagamento", array("class"=>"'required'"), 1);
					
					$page->create_select($dizionario['spese']['pagato'], "Spesa[Pagato]", "Pagato", "brain_campoForm", $arr_pagato, (isset($spesa['Pagato']))? $spesa['Pagato'] : 1, "PagatoId", "Pagato", array("class"=>"'required'"), 1);
										
				?>
				</div>
				<div class="divSubmit"><?php
					$page->create_button("Salva","Salva",$dizionario['generale']['salva'],"brain_salva","submit");
				?></div>
			</form>
		</div>
	</div>
<?php	
}

?>