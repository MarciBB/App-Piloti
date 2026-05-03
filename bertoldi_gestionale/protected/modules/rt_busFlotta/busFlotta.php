
<!-- Pagina gestione bus/flotta: include CSS -->
<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<?php 
// Includi file e classi necessarie
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load(); 
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.DT.php");
include_once($classespath_ . "class.Flotta.php");
include_once($classespath_ . "class.TipologiaBus.php");

global $ModuloId, $user;
$ModuloId = 36; // ID modulo gestione flotta

// Controllo utente loggato
if (is_object($user)) {
	/*
		ID Funzione:
		1 - Lista
		2 - Aggiunta
		3 - Cancellazione
		4 - Modifica
		5 - Esportazione
		6 - Importazione
		7 - Stampa
	*/
	$permessi = $user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi) > 0) {
		$do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';
		// Routing azioni
		switch ($do) {
			case "add":
				$FunzioneId = 2;
				$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
				if (sizeof($permesso))
					add(); // Mostra form aggiunta
				else
					$errore->stampa_errore(2); // Permesso negato
				break;
			case "edit":
				$FunzioneId = 4;
				$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
				if (sizeof($permesso))
					add($_GET['BusId']); // Mostra form modifica
				else
					$errore->stampa_errore(2); // Permesso negato
				break;
			default:
				$FunzioneId = 1;
				$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
				if (sizeof($permesso))
					show_list(); // Mostra lista bus
				else
					$errore->stampa_errore(2); // Permesso negato
				break;
		}
	} else {
		$errore->stampa_errore(1); // Nessun permesso modulo
	}
} else {
	// Utente non loggato: redirect logout
	header("Location: /logout.php");
}

// Funzione: mostra la lista dei bus/flotta
function show_list() {
	global $user, $HtmlCommon, $ModuloId, $db, $dizionario;
	// Titolo pagina e box
	$HtmlCommon->html_titolo_pagina($dizionario['flotta']['titolo_gesione'], 0, "", "");
	$HtmlCommon->html_titolo_box($dizionario['flotta']['titolo_gesione']);

	$db = new Database();
	$db->connect();
	// Include datatable JS/HTML
	include_once("busFlotta_datatable.php");

	// Mostra tasto aggiungi se permesso
	$aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);
	if (sizeof($aggiungi))
		$HtmlCommon->html_tasto_lista('brain_aggiungi est', 'rt_busFlotta', 'busFlotta.php?do=add', $dizionario['flotta']['aggiungi']);
	?>
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
			<tr class="brain_tabellaTr">
				<th width="10%"><?=$dizionario['flotta']['tipo']?></th>
				<th width="10%"><?=$dizionario['flotta']['modello']?></th>
				<th width="10%"><?=$dizionario['flotta']['targa']?></th>
				<th width="10%"><?=$dizionario['generale']['cell']?></th>
				<th width="10%"><?=$dizionario['flotta']['noleggiato']?></th>
				<th width="10%"><?=$dizionario['flotta']['tipo_noleggio']?></th>
				<th width="10%"><?=$dizionario['flotta']['area_lavoro']?></th>
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
				<th><input type="hidden" /></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="8" class="dataTables_empty">
					<i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br>
					<?=$dizionario['generale']['caricamento_in_corso']?>
				</td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="8"></td>
			</tr>
		</tfoot>
	</table>
	<?php
	$db->close();
}

// Funzione: mostra form aggiunta/modifica bus/flotta
function add($BusId = null) {
	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();

	$page = new Form();
	$dt = new DT();

	$autista = array();
	if (isset($BusId)) {
		// Modifica bus: recupera dati
		$busObj = new Flotta($BusId);
		$busObj->conn = $db;
		$busObj->inizializzaDatiGenerali();
		$busDB = $busObj->DatiGenerali;
		$HtmlCommon->html_titolo_pagina($dizionario['flotta']['titolo_modifica'], 1, "rt_busFlotta", "busFlotta.php");
		$HtmlCommon->html_titolo_box($dizionario['flotta']['titolo_modifica']);
	} else {
		// Nuovo bus
		$HtmlCommon->html_titolo_pagina($dizionario['flotta']['titolo_inserisci'], 1, "rt_busFlotta", "busFlotta.php");
		$HtmlCommon->html_titolo_box($dizionario['flotta']['titolo_inserisci']);
	}

	// Array per select noleggio
	$arr_noleggio[] = array("NoleggioId" => '0', "Noleggio" => $dizionario['generale']['no']);
	$arr_noleggio[] = array("NoleggioId" => '1', "Noleggio" => $dizionario['generale']['si']);

	// Array per select tipo noleggio
	$arr_tipo_noleggio[] = array("TipoNoleggioId" => '0', "TipoNoleggio" => $dizionario['flotta']['noleggio_con_conducente']);
	$arr_tipo_noleggio[] = array("TipoNoleggioId" => '1', "TipoNoleggio" => $dizionario['flotta']['noleggio_con_licenza']);
	$arr_tipo_noleggio[] = array("TipoNoleggioId" => '2', "TipoNoleggio" => $dizionario['flotta']['exlusive']);

	// Array per select area lavoro
	$arr_area_lavoro[] = array("AreaLavoroId" => '0', "AreaLavoro" => $dizionario['flotta']['sirmione']);
	$arr_area_lavoro[] = array("AreaLavoroId" => '1', "AreaLavoro" => $dizionario['flotta']['desenzano']);

	// Recupera tipologie bus per select
	$tipologiaBus = new TipologiaBus();
	$tipologiaBus->conn = $db;
	$arr_tipologiaBus = $tipologiaBus->getAllForSelect();

	include_once("busFlotta_validator.php");
	?>
	<div id="brain_form_content" class="brain_row brain_contenuto">
		<div class="brain_boxIntero wizart">
			<form id="application_form" class="brain_formModifica" name="application_form" method="post" action="#">
				<div class="brain_data-content">
					<?php
					// Hidden per azione
					if ($BusId == null) {
						$page->create_textbox_hidden("action", "AggiungiAutobus");
					} else {
						$page->create_textbox_hidden("action", "ModificaAutobus");
						$page->create_textbox_hidden("BusId", $BusId);
					}
					// Select tipologia bus
					$page->create_select($dizionario['flotta']['autobus'], "Autobus[TipologiaBusId]", "TipologiaBusId", "brain_campoForm", $arr_tipologiaBus, $busDB['TipologiaBusId'], "TipologiaBusId", "TipologiaBus", array("class" => "'required'"), 1);
					print("<br style=\"clear:both;\"/>");
					// Modello
					$page->create_textbox($dizionario['flotta']['modello'], "Modello", "Autobus[Modello]", $busDB['Modello'], 0, "brain_campoForm", array("class" => "'required'"));
					print("<br style=\"clear:both;\"/>");
					// Targa
					$page->create_textbox($dizionario['flotta']['targa'], "Targa", "Autobus[Targa]", $busDB['Targa'], 0, "brain_campoForm", array("class" => "'required'"));
					print("<br style=\"clear:both;\"/>");
					// Cellulare
					$page->create_textbox($dizionario['generale']['cell'], "Cellulare", "Autobus[Cellulare]", $busDB['Cellulare'], 0, "brain_campoForm", array("class" => "'required'"));
					print("<br style=\"clear:both;\"/>");
					// Select noleggio
					$page->create_select($dizionario['flotta']['noleggiato'], "Autobus[Noleggio]", "Noleggio", "brain_campoForm", $arr_noleggio, $busDB['Noleggio'], "NoleggioId", "Noleggio", array("class" => "'required'"), 1);
					print("<br style=\"clear:both;\"/>");
					// Select tipo noleggio
					$page->create_select($dizionario['flotta']['tipo_noleggio'], "Autobus[TipoNoleggio]", "TipoNoleggio", "brain_campoForm", $arr_tipo_noleggio, $busDB['TipoNoleggio'], "TipoNoleggioId", "TipoNoleggio", array("class" => "'required'"), 1);
					print("<br style=\"clear:both;\"/>");
					// Select area lavoro
					$page->create_select($dizionario['flotta']['area_lavoro'], "Autobus[AreaLavoro]", "AreaLavoro", "brain_campoForm", $arr_area_lavoro, $busDB['AreaLavoro'], "AreaLavoroId", "AreaLavoro", array("class" => "'required'"), 1);
					print("<br style=\"clear:both;\"/>");

					// Colorpicker per campo Colore
					?>
					<div class="brain_campoForm">
						<label for="Autobus_Colore">
							<span class="required"><span class="hidden">*</span><?= $dizionario['flotta']['colore'] ?></span>
						</label>
						<br>
						<input type="color" name="Autobus[Colore]" id="Autobus_Colore" class="required" value="<?=isset($busDB['Colore']) ? htmlspecialchars($busDB['Colore']) : '#000000'?>" style="width:50px; height:30px; vertical-align:middle;">
					</div>
					<?php
					?>
				</div>
				<div class="divSubmit">
					<?php $page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit"); ?>
				</div>
			</form>
		</div>
	</div>
<?php
}
?>