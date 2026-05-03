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
include_once($classespath_."Graph/class.LineaGraph.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.PrenotazioneDettaglio.php");

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
	include_once("gestioneottimizzata_validator.php");
	
	global $user, $HtmlCommon, $ModuloId, $db;
	
	$HtmlCommon->html_titolo_pagina("Gestione Ottimizzata Viaggio", 0, "", "");
	$HtmlCommon->html_titolo_box("Gestione Ottimizzata Viaggio");

	$db= new Database();
	$db->connect();
	
	$lineaId = 1;
	$corsaId = 1;
	$dataPartenza = "2013-10-28";
	
	$gruppo = new LineaGraph($lineaId,$corsaId,$dataPartenza,$db);	
	?>
	
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    	<thead>
            <tr class="brain_tabellaTr">
            	<th width="10%">Comune Partenza</th>
				<th width="10%">Comune Destinazione</th>
				<th width="10%">Tratta</th>
				<th width="10%">Km</th>
                <th width="10%">Totale passeggeri</th>
                <th width="10%">Pulman consigliati</th>
                <th width="5%">Dettagli</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				foreach ($gruppo->graph->edges as $edge){
			?>
			<tr>
				<td>
					<?php
						$comune = new Comune($edge->nodeA);
						$comune->conn = $db;
						$comune->inizializzaDatiGenerali();
						echo $comune->Comune;
					?>
				</td>
				<td>
					<?php
						$comune = new Comune($edge->nodeB);
						$comune->conn = $db;
						$comune->inizializzaDatiGenerali();
						echo $comune->Comune;
					?>
				</td>
				<td>
					<?php
						foreach ($edge->info3 as $key => $value){
							$tratta = new Tratta($value);
							$tratta->conn = $db;
							$tratta->inizializzaDatiGenerali();
							echo $tratta->DatiGenerali['TrattaNome']."<br/>";
						}
					?>
				</td>
				<td>
					<?php echo $edge->peso;?>
				</td>
				<td>
					<?php echo $edge->info2;?>
				</td>
				<td>
					<?php
						$numPulman = intval($edge->info2 / 59);
						$mod = $edge->info2 % 59;
						if($mod > 0){
							$numPulman += 1;
						}
						echo $numPulman;
					?>
				</td>
				<td>
					<?php
						if($edge->info2 > 0) { 
						?>
							<a id="dettaglio<?php echo $edge->nodeA."".$edge->nodeB;?>" href="#">Dettaglio</a>
							<script type="text/javascript">
								$('#dettaglio<?php echo $edge->nodeA."".$edge->nodeB;?>').click(function () {
									var biglietti = new Array();
									<?php
										foreach ($edge->info as $key => $value){
									?>
											biglietti[<?=$key?>] = <?=$value?>;
									<?php 
										}
									?>
									var dataPartenza = "<?php echo $dataPartenza?>";
									$.ajax({
										type: "POST",
										url: "/protected/modules/rt_gestioneottimizzata/gestioneottimizzata_action.php",
										data: {'action': "DettaglioPasseggeri", 'bigliettiId': biglietti, 'lineaId':<?=$lineaId?>, 'corsaId':<?=$corsaId?>, 'dataPartenza': dataPartenza},
										success: function(data) {
											dialog_box();
											$("#brain_listaSelezione").html(data);
											adatta_dialog_box();
										}
									});
								});	
							</script>
						<?php 
						}
						?>
				</td>
			</tr>
			<?php 
				}
			?>
		</tbody>
		<tfoot> 
			<tr> 
				<td colspan="7"></td>
			</tr>
		</tfoot>
	</table>
	<?php
	
	$page = new Form();
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero wizart">
			<form id="application_form" class="brain_formModifica" name="application_form" method="post" action="#">
	
				<div class="brain_data-content"><?
					$page->create_textbox_hidden("action","DettaglioPasseggeroViaggio");
					$page->create_textbox_hidden("corsaId", $corsaId);
					$page->create_textbox_hidden("lineaId", $lineaId);
					$page->create_textbox_hidden("dataPartenza", $dataPartenza);
					
					$arr_passeggeri = array();
					$prenDett = new PrenotazioneDettaglio();
					$prenDett->conn=$db;
					$rows = $prenDett->getPrenotazioniDettaglio($lineaId, $corsaId, $dataPartenza);
					
					foreach ($rows as $key => $value){
						$arr_passeggeri[$key]['PasseggeroId']=$value['PrenotazioneNumero'];
						$arr_passeggeri[$key]['Passeggero']=$value['Cognome']." ".$value["Nome"];
					}
					$page->create_select("Passeggero","passeggero","passeggero","brain_campoForm",$arr_passeggeri,-1,"PasseggeroId","Passeggero",null,1);
				?></div>
				
				<div class="divSubmit"><?
					$page->create_button("dettaglioPasseggero","dettaglioPasseggero","Dettaglio","brain_salva","submit");
				?></div>
			</form>
		</div>
	</div>
	
	
	<?php
	$db->close();
	
}
?>