<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.DT.php");
include_once($classespath_ . "class.Autisti.php");
include_once($classespath_ . "class.Coupon.php");

global $ModuloId;
global $user;

$ModuloId = 45; // modulo base mediazione

if (is_object($user)) {

	/*  ID - FUNZIONE
		1   Lista
		2   Aggiunta
		3   Cancellazione
		4   Modifica
		5   Esportazione
		6   Importazione
		7   Stampa
	*/

	$permessi = $user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi) > 0) {
		$do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';

		switch ($do) {
			case "add":
				$FunzioneId = 2;
				$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
				if (sizeof($permesso))
					add();
				else
					$errore->stampa_errore(2);
				break;

			case "edit":
				$FunzioneId = 4;
				$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
				if (sizeof($permesso))
					add($_GET['CouponId']);
				else
					$errore->stampa_errore(2);
				break;

			default:
				$FunzioneId = 1;
				$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
				if (sizeof($permesso))
					show_list();
				else
					$errore->stampa_errore(2);
				break;
		}
	} else {
		$errore->stampa_errore(1);
	}
} else {
	// se l'utente non è loggato
	header("Location: /logout.php");
}

function show_list()
{
	global $user, $HtmlCommon, $ModuloId, $db, $dizionario;

	$HtmlCommon->html_titolo_pagina($dizionario['coupon']['titolo_gestione'], 0, "", "");
	$HtmlCommon->html_titolo_box($dizionario['coupon']['titolo_gestione']);

	$db = new Database();
	$db->connect();

	include_once("coupon_datatable.php");
	include_once("coupon_validator.php");
	$aggiungi = $user->ControllModuloFunzionePermesso($ModuloId, 2);
	if (sizeof($aggiungi)) {
		?>
		<div class="brain_servicebar">
			<a class="brain_aggiungi est" href="javascript:void(0);" onclick="javascript:ExternalLoad('rt_coupon','coupon.php?do=add');" title="aggiungi Coupon di Sconto"><i class="fa fa-plus" aria-hidden="true"></i> <?=$dizionario['coupon']['aggiungi']?></a>
		</div>
		<?php
	}
	?>
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
			<tr class="brain_tabellaTr">
				<th width="10%"><?=$dizionario['generale']['stato']?></th>
				<th width="10%"><?=$dizionario['coupon']['descrizione']?></th>
				<th width="10%"><?=$dizionario['coupon']['codice']?></th>
				<th width="10%"><?=$dizionario['coupon']['sconto']?> (&euro;/%)</th>
				<th width="10%"><?=$dizionario['coupon']['max_utilizzi']?></th>
				<th width="10%"><?=$dizionario['coupon']['utilizzi']?></th>
				<th width="10%"><?=$dizionario['coupon']['valore']?> (&euro;/%)</th>
				<th width="10%"><?=$dizionario['coupon']['vendita']?></th>
				<th width="10%"><?=$dizionario['coupon']['info']?></th>
				<th width="5%"><?=$dizionario['generale']['edita']?></th>
			</tr>
			<tr class="brain_tabellaFilter">
				<th>
					<select id="filter_stato" class="brain_filter_select">
						<option value=""><?=$dizionario['generale']['tutti']?></option>
						<option value="<?=$dizionario['generale']['attivo']?>"><?=$dizionario['generale']['attivo']?></option>
						<option value="<?=$dizionario['generale']['disattivo']?>"><?=$dizionario['generale']['disattivo']?></option>
					</select>
				</th>
				<th><input type="text" /></th>
				<th><input type="text" /></th>
				<th><input type="text" /></th>
				<th><input type="text" /></th>
				<th><input type="text" /></th>
				<th><input type="text" /></th>
				<th>
					<select id="filter_da_vendere" class="brain_filter_select">
						<option value=""><?=$dizionario['generale']['tutti']?></option>
						<option value="<?=$dizionario['generale']['si']?>"><?=$dizionario['generale']['si']?></option>
						<option value="<?=$dizionario['generale']['no']?>"><?=$dizionario['generale']['no']?></option>
					</select>
				</th>
				<th><input type="hidden" /></th>
				<th><input type="hidden" /></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="10" class="dataTables_empty"><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="10"></td>
			</tr>
		</tfoot>
	</table>
	<?php
	$db->close();
}

function add($CouponId = null)
{
	global $HtmlCommon, $user, $db, $dizionario;

	$db = new Database();
	$db->connect();

	$page = new Form();
	$dt = new DT();

	$autista = array();
	$readonly = false;
	$fasceOrarie = [];

	if (isset($CouponId)) {
		$couponObj = new Coupon($CouponId);
		$couponObj->conn = $db;
		$couponObj->inizializzaDatiGenerali();
		$coupon = $couponObj->DatiGenerali;

		if ($coupon['ValidoDa'] == '0000-00-00') {
			$coupon['ValidoDa'] = null;
		}
		if ($coupon['ValidoA'] == '0000-00-00') {
			$coupon['ValidoA'] = null;
		}

		// Imposta readonly se VenditaStato = 2 e DaVendere = 1
		if (isset($coupon['VenditaStato']) && $coupon['VenditaStato'] == 2 && isset($coupon['DaVendere']) && $coupon['DaVendere'] == 1) {
			$readonly = true;
		}

		// Recupera fasce orarie già salvate
		$sql = "SELECT OraInizio, OraFine FROM RT_CouponFasciaOraria WHERE CouponId = " . intval($CouponId);
    	$fasceOrarie = $db->fetch_array($sql);

		$HtmlCommon->html_titolo_pagina($dizionario['coupon']['titolo_modifica'], 1, "rt_coupon", "coupon.php");
		$HtmlCommon->html_titolo_box($dizionario['coupon']['titolo_modifica']);
	} else {
		$HtmlCommon->html_titolo_pagina($dizionario['coupon']['titolo_inserisci'], 1, "rt_coupon", "coupon.php");
		$HtmlCommon->html_titolo_box($dizionario['coupon']['titolo_inserisci']);
	}

	$arr_stato[] = array("StatoId" => '0', "Stato" => $dizionario['generale']['disattivo']);
	$arr_stato[] = array("StatoId" => '1', "Stato" => $dizionario['generale']['attivo']);

	$arr_da_vendere = array(
		array('DaVendereId' => '0', 'DaVendere' => $dizionario['generale']['no']),
		array('DaVendereId' => '1', 'DaVendere' => $dizionario['generale']['si'])
	);

	$arr_vendita_stato = array(
		array('VenditaStatoId' => '1', 'VenditaStato' => $dizionario['coupon']['in_attesa_pagamento']),
		array('VenditaStatoId' => '2', 'VenditaStato' => $dizionario['coupon']['coupon_emesso'])
	);

	$arr_buono_regalo = array(
		array('BuonoRegaloId' => '0', 'BuonoRegalo' => $dizionario['generale']['no']),
		array('BuonoRegaloId' => '1', 'BuonoRegalo' => $dizionario['generale']['si'])
	);

	$sql = "SELECT c.ComuneId as PickupId, c.Comune as Pickup
			FROM Comune c
			LEFT JOIN RT_Fermata f ON f.ComuneId = c.ComuneId
			WHERE f.Stato = 1 AND f.Cancella = 0 AND IsPickup = 1
			GROUP BY c.ComuneId
			ORDER BY c.Comune";
	$arr_pickup = $db->fetch_array($sql);

	$sql = "SELECT c.ComuneId as DropOffId, c.Comune as DropOff
			FROM Comune c
			LEFT JOIN RT_Fermata f ON f.ComuneId = c.ComuneId
			WHERE f.Stato = 1 AND f.Cancella = 0 AND IsDropOff = 1
			GROUP BY c.ComuneId
			ORDER BY c.Comune";
	$arr_dropoff = $db->fetch_array($sql);

	$sql = "SELECT RagioneSociale as Gestore, GestoreId
			FROM Gestore
			WHERE Stato = 1 AND Cancella = 0
			ORDER BY RagioneSociale";
	$arr_gestore = $db->fetch_array($sql);

	$sql = "SELECT LineaNome as Linea, LineaId
			FROM RT_Linea
			WHERE Stato = 1 AND Cancella = 0
			ORDER BY LineaNome";
	$arr_linea = $db->fetch_array($sql);

	include_once("coupon_validator.php");
	?>
	<script type="text/javascript">
		$(document).ready(function () {
			// Datepicker
			var d = new Date();
			$(function () {
				$("#DataDalId").datepicker({
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

				$("#DataAlId").datepicker({
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
					<div class="brain_data-content">
						<?php
						if ($CouponId == null) {
							$page->create_textbox_hidden("action", "AggiungiCoupon");
						} else {
							$page->create_textbox_hidden("action", "ModificaCoupon");
							$page->create_textbox_hidden("CouponId", $CouponId);
						}

						// Blocca tutti i campi in sola lettura se VenditaStato = 2 e DaVendere = 1, tranne "Stato"
						$readonly_fields = $readonly ? array("readonly" => "readonly", "disabled" => "disabled") : array();

						$page->create_textbox(
							$dizionario['coupon']['descrizione'] . ":", 
							"Descrizione", 
							"Coupon[CouponNome]", 
							(isset($coupon['CouponNome'])) ? $coupon['CouponNome'] : "", 
							1, 
							"brain_campoForm campiformBig", 
							$readonly ? array("class" => "'required'", "readonly" => "readonly", "disabled" => "disabled") : array("class" => "'required'")
						);
						print("<br style=\"clear:both;\"/>");
						if ($CouponId == null) {
							?>
							<fieldset>
								<legend><?=$dizionario['coupon']['coupon_da_creare']?></legend>
								<input style="float:none !important;" type="radio" name="tipoCreazione" value="one" checked="checked"/><?=$dizionario['coupon']['singolo_coupon']?>
								<input style="float:none !important;" type="radio" name="tipoCreazione" value="more"/><?=$dizionario['coupon']['piu_coupon']?>
							</fieldset>
							<?php
							print("<br style=\"clear:both;\"/>");
						}

						$page->create_textbox(
							$dizionario['coupon']['codice'] . ":", 
							"Codice", 
							"Coupon[Codice]", 
							(isset($coupon['Codice'])) ? $coupon['Codice'] : "", 
							1, 
							"brain_campoForm", 
							$readonly ? array("class" => "'required'", "readonly" => "readonly", "disabled" => "disabled") : array("class" => "'required'")
						);
						?>
						<div class="brain_campoForm" style="padding-top: 26px; vertical-align:middle; padding-left:40px">
							<a href="#" id="getCodiceCoupon" <?= $readonly ? 'style="pointer-events:none;color:#ccc;"' : 'style="cursor: pointer;
									font-family: \'Montserrat\',sans-serif;
									color: #ffffff;
									background-color: #00A4AA;
									-moz-border-radius: 3px;
									-webkit-border-radius: 3px;
									border-radius: 3px;
									border: 1px solid #00A4AA;
									padding: 3px;"' ?>><?=$dizionario['coupon']['genera_codice']?></a>
						</div>
						<?php
						if ($CouponId == null) {
							$page->create_textbox($dizionario['coupon']['num_coupon'] . ":", "numeroCoupon", "numeroCoupon", "1", 1, "brain_campoForm", array("class" => "'required number'"));
						}
						print("<br style=\"clear:both;\"/>");
						if ($CouponId == null) {
							?>
							<fieldset>
								<legend><?=$dizionario['coupon']['tipo_coupon']?></legend>
								<input style="float:none !important;" type="radio" name="tipoCoupon" value="fisso" checked="checked"/><?=$dizionario['coupon']['fisso']?>
								<input style="float:none !important;" type="radio" name="tipoCoupon" value="percentuale"/><?=$dizionario['coupon']['percentuale']?>
							</fieldset>
							<?php
							print("<br style=\"clear:both;\"/>");
						}
						if ($CouponId == null || (isset($coupon['Importo']) && $coupon['Importo'] > 0)) {
							$page->create_textbox(
								$dizionario['generale']['importo'] . ":", 
								"Importo", 
								"Coupon[Importo]", 
								(isset($coupon['Importo']) && $coupon['Importo'] > 0) ? $coupon['Importo'] : "", 
								1, 
								"brain_campoForm", 
								$readonly ? array("class" => "'number'", "readonly" => "readonly", "disabled" => "disabled") : array("class" => "'number'")
							);
						}
						if ($CouponId == null || (isset($coupon['Percentuale']) && $coupon['Percentuale'] > 0)) {
							$page->create_textbox(
								$dizionario['coupon']['percentuale'] . ":", 
								"Percentuale", 
								"Coupon[Percentuale]", 
								(isset($coupon['Percentuale']) && $coupon['Percentuale'] > 0) ? $coupon['Percentuale'] : "", 
								1, 
								"brain_campoForm", 
								$readonly ? array("class" => "'number'", "readonly" => "readonly", "disabled" => "disabled") : array("class" => "'number'")
							);
						}
						print("<br style=\"clear:both;\"/>");
						$page->create_textbox(
							$dizionario['coupon']['max_utilizzi'] . ":", 
							"MaxUtilizzi", 
							"Coupon[MaxUtilizzi]", 
							(isset($coupon['MaxUtilizzi'])) ? $coupon['MaxUtilizzi'] : "", 
							1, 
							"brain_campoForm", 
							$readonly ? array("class" => "'required number'", "readonly" => "readonly", "disabled" => "disabled") : array("class" => "'required number'")
						);

						print("<br style=\"clear:both;\"/>");

						if ($CouponId == null) {
							$page->create_textbox($dizionario['generale']['dal'] . ":", "DataDalId", "Coupon[ValidoDa]", "", 0, "brain_campoForm", array("class" => "'italianDate'"), "", "10");
							$page->create_textbox($dizionario['generale']['al'] . ":", "DataAlId", "Coupon[ValidoA]", "", 0, "brain_campoForm", array("class" => "'italianDate'"), "", "10");
						} else {
							$page->create_textbox(
								$dizionario['generale']['dal'] . ":", 
								"DataDalId", 
								"Coupon[ValidoDa]",
								(isset($coupon['ValidoDa'])) ? $dt->format($coupon["ValidoDa"], "Y-m-d", "d/m/Y") : "",
								0, 
								"brain_campoForm", 
								$readonly ? array("class" => "'italianDate'", "readonly" => "readonly", "disabled" => "disabled") : array("class" => "'italianDate'"), 
								"", 
								"10"
							);
							$page->create_textbox(
								$dizionario['generale']['al'] . ":", 
								"DataAlId", 
								"Coupon[ValidoA]",
								(isset($coupon['ValidoA'])) ? $dt->format($coupon["ValidoA"], "Y-m-d", "d/m/Y") : "",
								0, 
								"brain_campoForm", 
								$readonly ? array("class" => "'italianDate'", "readonly" => "readonly", "disabled" => "disabled") : array("class" => "'italianDate'"), 
								"", 
								"10"
							);
						}

						print("<br style=\"clear:both;\"/>");

						// Mostra i campi per inserire le fasce orarie (forzato 24h, senza secondi)
						?>
						<div class="brain_campoForm big">
							<label>Fasce orarie (dalle - alle):</label>
							<div id="fasce-orarie-container">
								<?php
								function select_orario($name, $selected = null) {
									echo '<select name="' . $name . '[ora][]" required style="width:60px; float:none;">';
									for ($h = 0; $h < 24; $h++) {
										$val = str_pad($h, 2, '0', STR_PAD_LEFT);
										$sel = ($selected && substr($selected,0,2)==$val) ? 'selected' : '';
										echo "<option value='$val' $sel>$val</option>";
									}
									echo '</select> : ';
									echo '<select name="' . $name . '[min][]" required style="width:60px; float:none;">';
									for ($m = 0; $m < 60; $m+=5) {
										$val = str_pad($m, 2, '0', STR_PAD_LEFT);
										$sel = ($selected && substr($selected,3,2)==$val) ? 'selected' : '';
										echo "<option value='$val' $sel>$val</option>";
									}
									echo '</select>';
								}
								if (!empty($fasceOrarie)) {
									foreach ($fasceOrarie as $fascia) {
										echo '<div class="fascia-oraria-row">';
										select_orario('FasceOrarie[dalle]', $fascia['OraInizio']);
										echo ' - ';
										select_orario('FasceOrarie[alle]', $fascia['OraFine']);
										echo ' <button type="button" onclick="this.parentNode.remove();"  style="cursor: pointer;
																												font-family: \'Montserrat\',sans-serif;
																												color: #ffffff;
																												background-color: #00A4AA;
																												-moz-border-radius: 3px;
																												-webkit-border-radius: 3px;
																												border-radius: 3px;
																												border: 1px solid #00A4AA;">Rimuovi</button>';
										echo '</div>';
									}
								} else {
									echo '<div class="fascia-oraria-row">';
									select_orario('FasceOrarie[dalle]');
									echo ' - ';
									select_orario('FasceOrarie[alle]');
									echo ' <button type="button" onclick="this.parentNode.remove();"  style="cursor: pointer;
																									font-family: \'Montserrat\',sans-serif;
																									color: #ffffff;
																									background-color: #00A4AA;
																									-moz-border-radius: 3px;
																									-webkit-border-radius: 3px;
																									border-radius: 3px;
																									border: 1px solid #00A4AA;">Rimuovi</button>';
									echo '</div>';
								}
								?>
							</div>
							<button type="button" onclick="aggiungiFasciaOraria();"
								style="cursor: pointer;
										font-family: 'Montserrat',sans-serif;
										color: #ffffff;
										padding: 0px 20px !important;
										background-color: #00A4AA;
										-moz-border-radius: 3px;
										-webkit-border-radius: 3px;
										border-radius: 3px;
										border: 1px solid #00A4AA;
										margin: 5px !important;">Aggiungi fascia oraria</button>
						</div>
						<script>
						function aggiungiFasciaOraria() {
							var container = document.getElementById('fasce-orarie-container');
							var div = document.createElement('div');
							div.className = 'fascia-oraria-row';
							div.innerHTML = `<?php select_orario('FasceOrarie[dalle]'); ?> - <?php select_orario('FasceOrarie[alle]'); ?> <button type="button" onclick="this.parentNode.remove();" style="cursor: pointer;
																																																			font-family: 'Montserrat',sans-serif;
																																																			color: #ffffff;
																																																			background-color: #00A4AA;
																																																			-moz-border-radius: 3px;
																																																			-webkit-border-radius: 3px;
																																																			border-radius: 3px;
																																																			border: 1px solid #00A4AA;">Rimuovi</button>`;
							container.appendChild(div);
						}
						</script>
						<?php
						print("<br style=\"clear:both;\"/>");
						$page->create_select(
							$dizionario['biglietto']['partenza_p'], 
							"CouponPartenzaId[]", 
							"PartenzaId", 
							"brain_campoForm big", 
							$arr_pickup, 
							(isset($coupon['PartenzaId'])) ? $coupon['PartenzaId'] : "", 
							"PickupId", 
							"Pickup", 
							$readonly ? array("multiple" => "multiple", "disabled" => "disabled") : array("multiple" => "multiple"), 
							0
						);

						if (isset($coupon['PartenzaId']) && !empty($coupon['PartenzaId'])) {
							$partenzaIds = explode(',', $coupon['PartenzaId']);
							?>
							<script type="text/javascript">
								$(document).ready(function () {
									var selectedIds = <?= json_encode($partenzaIds); ?>;
									$('#PartenzaId option').each(function () {
										if (selectedIds.includes($(this).val())) {
											$(this).attr('selected', 'selected');
										}
									});
									<?php if ($readonly) { ?> $('#PartenzaId').prop('disabled', true); <?php } ?>
								});
							</script>
							<?php
						}

						$page->create_select(
							$dizionario['pre']['destinazione'], 
							"CouponDestinazioneId[]", 
							"DestinazioneId", 
							"brain_campoForm big", 
							$arr_dropoff, 
							(isset($coupon['DestinazioneId'])) ? $coupon['DestinazioneId'] : "", 
							"DropOffId", 
							"DropOff", 
							$readonly ? array("multiple" => "multiple", "disabled" => "disabled") : array("multiple" => "multiple"), 
							0
						);
						if (isset($coupon['DestinazioneId']) && !empty($coupon['DestinazioneId'])) {
							$destinazioneIds = explode(',', $coupon['DestinazioneId']);
							?>
							<script type="text/javascript">
								$(document).ready(function () {
									var selectedIds = <?= json_encode($destinazioneIds); ?>;
									$('#DestinazioneId option').each(function () {
										if (selectedIds.includes($(this).val())) {
											$(this).attr('selected', 'selected');
										}
									});
									<?php if ($readonly) { ?> $('#DestinazioneId').prop('disabled', true); <?php } ?>
								});
							</script>
							<?php
						}

						print("<br style=\"clear:both;\"/>");

						$page->create_select(
							$dizionario['generale']['linea_esperienza'], 
							"CouponLineaId[]", 
							"LineaId", 
							"brain_campoForm big", 
							$arr_linea, 
							'', 
							"LineaId", 
							"Linea", 
							$readonly ? array("multiple" => "multiple", "disabled" => "disabled") : array("multiple" => "multiple"), 
							0
						);
						print("<br style=\"clear:both;\"/>");

						if (isset($coupon['LineaId']) && !empty($coupon['LineaId'])) {
							$lineaIds = explode(',', $coupon['LineaId']);
							?>
							<script type="text/javascript">
								$(document).ready(function () {
									var selectedIds = <?= json_encode($lineaIds); ?>;
									$('#LineaId option').each(function () {
										if (selectedIds.includes($(this).val())) {
											$(this).attr('selected', 'selected');
										}
									});
									<?php if ($readonly) { ?> $('#LineaId').prop('disabled', true); <?php } ?>
								});
							</script>
							<?php
						}

						$page->create_select(
							$dizionario['gestore']['gestore'], 
							"Coupon[GestoreId]", 
							"GestoreId", 
							"brain_campoForm", 
							$arr_gestore, 
							(isset($coupon['GestoreId'])) ? $coupon['GestoreId'] : "", 
							"GestoreId", 
							"Gestore", 
							$readonly ? array("disabled" => "disabled") : null, 
							0
						);
						print("<br style=\"clear:both;\"/>");

						$page->create_select(
							$dizionario['coupon']['da_vendere'],
							"Coupon[DaVendere]",
							"DaVendere",
							"brain_campoForm",
							$arr_da_vendere,
							(isset($coupon['DaVendere'])) ? $coupon['DaVendere'] : '0',
							"DaVendereId",
							"DaVendere",
							$readonly ? array("id" => "DaVendereSelect", "disabled" => "disabled") : array("id" => "DaVendereSelect"),
							1
						);
						?>
						<div id="vendita_fields" style="display:<?php echo (isset($coupon['DaVendere']) && $coupon['DaVendere'] == '1') ? 'block' : 'none'; ?>;">
							<?php
							$page->create_select(
								$dizionario['coupon']['stato_vendita'],
								"Coupon[VenditaStato]",
								"VenditaStato",
								"brain_campoForm",
								$arr_vendita_stato,
								(isset($coupon['VenditaStato'])) ? $coupon['VenditaStato'] : 2,
								"VenditaStatoId",
								"VenditaStato",
								$readonly ? array("id" => "VenditaStatoSelect", "class" => "'required'", "disabled" => "disabled") : array("id" => "VenditaStatoSelect", "class" => "'required'"),
								1
							);
							
							$page->create_textbox(
								$dizionario['coupon']['email_vendita'],
								"VenditaEmail",
								"Coupon[VenditaEmail]",
								(isset($coupon['VenditaEmail'])) ? $coupon['VenditaEmail'] : "",
								1,
								"brain_campoForm",
								$readonly ? array("id" => "VenditaEmailInput", "class" => "'required email'", "readonly" => "readonly", "disabled" => "disabled") : array("id" => "VenditaEmailInput", "class" => "'email'")
							);
							
							print("<br style=\"clear:both;\"/>");

							$page->create_select(
								$dizionario['coupon']['buono_regalo'],
								"Coupon[VenditaBuonoRegalo]",
								"VenditaBuonoRegalo",
								"brain_campoForm",
								$arr_buono_regalo,
								(isset($coupon['VenditaBuonoRegalo'])) ? $coupon['VenditaBuonoRegalo'] : '0',
								"BuonoRegaloId",
								"BuonoRegalo",
								$readonly ? array("id" => "VenditaBuonoRegaloSelect", "disabled" => "disabled") : array("id" => "VenditaBuonoRegaloSelect"),
								1
							);
							?>

							<!-- Campi per Buono Regalo -->
							<div id="buono_regalo_fields" style="display:<?php echo (isset($coupon['VenditaBuonoRegalo']) && $coupon['VenditaBuonoRegalo'] == '1') ? 'block' : 'none'; ?>;">
								<?php
								print("<br style=\"clear:both;\"/>");
								
								$page->create_textbox(
									$dizionario['coupon']['email_destinatario'],
									"VenditaEmailDestinatario",
									"Coupon[VenditaEmailDestinatario]",
									(isset($coupon['VenditaEmailDestinatario'])) ? $coupon['VenditaEmailDestinatario'] : "",
									1,
									"brain_campoForm",
									$readonly ? array("id" => "VenditaEmailDestinatarioInput", "class" => "'email'", "readonly" => "readonly", "disabled" => "disabled") : array("id" => "VenditaEmailDestinatarioInput", "class" => "'email'")
								);
								
								print("<br style=\"clear:both;\"/>");
								
								// Textarea per messaggio destinatario
								?>
								<div class="brain_campoForm">
									<label for="VenditaMessaggioDestinatarioTextarea"><?= $dizionario['coupon']['messaggio_destinatario'] ?>:</label>
									<textarea 
										name="Coupon[VenditaMessaggioDestinatario]" 
										id="VenditaMessaggioDestinatarioTextarea"
										class="brain_campoFormBig"
										rows="6"
        								cols="200"
										<?= $readonly ? 'readonly disabled' : '' ?>
									><?= isset($coupon['VenditaMessaggioDestinatario']) ? htmlspecialchars($coupon['VenditaMessaggioDestinatario']) : '' ?></textarea>
								</div>
							</div>
						</div>
						<?php
						print("<br style=\"clear:both;\"/>");

						// Stato deve essere sempre modificabile
						$page->create_select(
							$dizionario['generale']['stato'],
							"Coupon[Stato]",
							"Stato",
							"brain_campoForm",
							$arr_stato,
							(isset($coupon['Stato'])) ? $coupon['Stato'] : 1,
							"StatoId",
							"Stato",
							array("class" => "'required'"),
							1
						);
						?>
					</div>
					<div class="divSubmit">
						<?php
						$page->create_button("Salva", "Salva", $dizionario['generale']['salva'], "brain_salva", "submit");
						?>
					</div>
				</div>
			</form>
		</div>
	</div>
	<?php
}
?>
