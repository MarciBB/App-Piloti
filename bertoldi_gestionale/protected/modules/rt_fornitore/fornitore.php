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
include_once($classespath_."class.Fornitore.php");

global $ModuloId;
global $user;

$ModuloId=55;// modulo base mediazione


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
					add($_GET['FornitoreId']);
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
	
	$HtmlCommon->html_titolo_pagina($dizionario['fornitore']['titolo_gestione'], 0, "", "");
	$HtmlCommon->html_titolo_box($dizionario['fornitore']['titolo_gestione']);

	$db= new Database();
	$db->connect();
	      
	include_once("fornitore_datatable.php");
 
	$aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);
	if(sizeof($aggiungi)) $HtmlCommon->html_tasto_lista('brain_aggiungi est','rt_fornitore','fornitore.php?do=add',$dizionario['fornitore']['aggiungi']);
	?>   
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    	<thead>
            <tr class="brain_tabellaTr">
            	<th width="1%"><?=$dizionario['generale']['stato']?></th>
				<th width="10%"><?=$dizionario['generale']['nome']?></th>
				<th width="10%"><?=$dizionario['fornitore']['categoria']?></th>
                <th width="5%"><?=$dizionario['generale']['edita']?></th>
			</tr>
            
			<tr class="brain_tabellaFilter">
            	<th><input type="hidden" /></th> 
            	<th><input type="text" /></th> 
				<th><input type="hidden" /></th> 
				<th><input type="hidden" /></th>  
			</tr>
		</thead>
		<tbody> 
			<tr>
				<td colspan="4" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		<tfoot> 
			<tr> 
				<td colspan="4"></td>
			</tr> 
		</tfoot> 
	</table>
	<?php   
	$db->close();
}

function add($FornitoreId = null) {
	
	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();
	
	$page = new Form();
	$dt = new DT();
	
	$fornitore = array();
	$fornitoreObj = new Fornitore();
	$fornitoreObj->conn = $db;
	if(isset($FornitoreId)) {
		$fornitoreObj = new Fornitore($FornitoreId);
		$fornitoreObj->conn = $db;
		$fornitoreObj->inizializzaDatiGenerali();
		$fornitore = $fornitoreObj->DatiGenerali;
		$HtmlCommon->html_titolo_pagina($dizionario['fornitore']['titolo_modifica'], 1, "rt_fornitore", "fornitore.php");
		$HtmlCommon->html_titolo_box ($dizionario['fornitore']['titolo_modifica']);
	} else {
		$HtmlCommon->html_titolo_pagina($dizionario['fornitore']['titolo_inserisci'], 1, "rt_fornitore", "fornitore.php");
		$HtmlCommon->html_titolo_box ($dizionario['fornitore']['titolo_inserisci']);
	}

	$arr_stato[]= array("StatoId" => '0', "Stato" => $dizionario['generale']['non_attivo']);
	$arr_stato[]= array("StatoId" => '1', "Stato" => $dizionario['generale']['attivo']);
	
	$arr_categorie = $fornitoreObj->getCategorieFornitore();
	
	include_once("fornitore_validator.php");
	?>
   
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero wizart">
			<form id="application_form" class="brain_formModifica" name="application_form" method="post" action="#">

				<div class="brain_data-content"><?php
					if($FornitoreId == null) {
						$page->create_textbox_hidden("action","AggiungiFornitore");
					} else {
						$page->create_textbox_hidden("action","ModificaFornitore");
						$page->create_textbox_hidden("FornitoreId", $FornitoreId);
					}
					
					$page->create_textbox($dizionario['fornitore']['nome'].":", "Nome", "Fornitore[Nome]", (isset($fornitore['Nome']))? $fornitore['Nome'] : "", 1, "brain_campoForm", array("class"=>"'required'"));
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_select($dizionario['fornitore']['categoria'], "Fornitore[Categoria]", "Categoria", "brain_campoForm", $arr_categorie, (isset($fornitore['Categoria']))? $fornitore['Categoria'] : 999, "FornitoriCategoriaId", "FornitoriCategoria", array("class"=>"'required'"), 1);
					
					print("<br style=\"clear:both;\"/>");
					
					$page->create_select($dizionario['generale']['stato'], "Fornitore[Stato]", "Stato", "brain_campoForm", $arr_stato, (isset($fornitore['Stato']))? $fornitore['Stato'] : 1, "StatoId", "Stato", array("class"=>"'required'"), 1);
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