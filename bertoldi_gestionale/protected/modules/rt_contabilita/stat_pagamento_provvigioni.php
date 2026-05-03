<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<link rel="stylesheet" type="text/css" href="/css/print_stat_mediazione.css" media="print" />
<link rel="stylesheet" href="/css/home.css" type="text/css" />

<?php 
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

$config = new Config();
$run = $config->load(); 
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();

include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Nazione.php");
include_once($classespath_ . "class.Regione.php");
include_once($classespath_ . "class.Comune.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.PagamentoTipo.php");

$ModuloId = 12;

function show_list() {
	global $user, $dizionario;

	$datacorrente = Date('d/m/Y');

	$db = new Database();
	$db->connect();
	$page = new Form();
	$gestore = new Gestore();
	$gestore->conn = $db;

	$ges = $user->GestoreId;
	if (($user->GestoreId == 1) || ($user->GestoreId == 2)) {
		$ges = 1;
	}

	$gestore->getGestoreAll($ges);
	$arr_gestore = $gestore->ArrGestore;
	$arr_gestore = array_merge(
		array(
			array('GestoreId' => -1, 'RagioneSociale' => 'Tutte le agenzie'),
			array('GestoreId' => -2, 'RagioneSociale' => 'Tutte le agenzie e Bertoldi Boats')
		),
		$arr_gestore
	);

	$sede = new Sede();
	$sede->conn = $db;
	$gestorecorrente = $user->GestoreId;
	$arr_sedi = $sede->getSediByGestori($user->GestoreId);

	$pagamentoObj = new PagamentoTipo();
	$pagamentoObj->conn = $db;
	$arr_pagamenti = $pagamentoObj->getAllForContabilita();

	$arr_tipo_titolo = array(
		array('TipoId' => 'entrate', 'Tipo' => 'Incasso'),
		array('TipoId' => 'uscite', 'Tipo' => 'Rimborso'),
	);

	$arr_ricevuta = array(
		array('RicevutaId' => '0', 'Ricevuta' => 'Tutti'),
		array('RicevutaId' => '1', 'Ricevuta' => 'Emessa'),
		array('RicevutaId' => '2', 'Ricevuta' => 'Non Emessa'),
		array('RicevutaId' => '3', 'Ricevuta' => 'Annullata'),
	);

	$arr_tipo_data = array(
		array('TipoDataId' => 'pagamento', 'TipoData' => 'Data Pagamento'),
		array('TipoDataId' => 'scontrino', 'TipoData' => 'Data Scontrino')
	);

	include_once("stat_pagamento_provvigioni_validator.php");  
?>

<div>
	<div class="brainFiltri">
		<form id="application_form" name="application_form" method="post" action="#">
			<?php
			$page->create_select(
				$dizionario['generale']['agenzia'] . ":",
				"GestoreId",
				"GestoreId",
				"rowForm",
				$arr_gestore,
				$gestorecorrente,
				"GestoreId",
				"RagioneSociale",
				array("onChange" => "'javascript:getSediByGestore(this);'"),
				1
			);

			$page->create_select(
				$dizionario['conto']['rivendita'] . ":",
				"SedeId",
				"SedeId",
				"rowForm",
				$arr_sedi,
				$user->SedeId,
				"SedeId",
				"Sede",
				null,
				1
			);

			$page->create_select(
				$dizionario['conto']['pagamenti'] . ":",
				"PagamentoTipoId",
				"PagamentoTipoId",
				"rowForm",
				$arr_pagamenti,
				0,
				"PagamentoTipoId",
				"PagamentoTipo",
				null,
				1
			);

			$page->create_select(
				$dizionario['conto']['tipo_titolo'] . ":",
				"TipoTitoloId",
				"TipoTitoloId",
				"rowForm",
				$arr_tipo_titolo,
				0,
				"TipoId",
				"Tipo",
				null,
				1
			);

			

			?>

			<div class="rowForm">
				<label for="tipo_report"><?= $dizionario['conto']['tipo_report'] ?></label>
				<select name="tipo_report">
					<option value="1" selected><?= $dizionario['conto']['totali'] ?></option>
				</select>
			</div>
			<?php

				$page->create_select(
					$dizionario['conto']['tipo_data'] . ":",
					"TipoData",
					"TipoData",
					"rowForm",
					$arr_tipo_data,
					'pagamento',
					"TipoDataId",
					"TipoData",
					null,
					1
				);

				$page->create_select(
					$dizionario['conto']['ricevuta'] . ":",
					"RicevutaId",
					"RicevutaId",
					"rowForm",
					$arr_ricevuta,
					0,
					"RicevutaId",
					"Ricevuta",
					null,
					1
				);
			?>

			<div class="rowForm">
				<label for="Dal"><?= $dizionario['generale']['dal'] ?></label>
				<input class="required" type="text" value="<?= $datacorrente ?>" id="Dal" name="Dal" maxlength="255" size="10">
				<label for="dataAl"><?= $dizionario['generale']['al'] ?></label>
				<input class="required" type="text" value="<?= $datacorrente ?>" id="Al" name="Al" maxlength="255" size="10">
			</div>

			<div class="rowForm">
				<input name="applica" type="submit" value="<?= $dizionario['generale']['genera'] ?>" />
			</div>
			<br style="clear:both;" />
		</form>
	</div>

	<div id="risultato_report"></div>
</div>

<?php
}

if (is_object($user)) {
	$db = new Database();
	$db->connect();
	$user->conn = $db;
	$permessi = $user->get_permessi_modulo($ModuloId);

	$do = '';
	if(isset($_REQUEST['do'])) {
		$do = $_REQUEST['do'];
	} else {
		$do = '';
	}

	switch ($do) {
		default:
			$FunzioneId = 1;
			$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);

			if (sizeof($permesso)) {
				show_list();
			}
			break;
	}
} else {
	header("Location: /logout.php");
}
?>
