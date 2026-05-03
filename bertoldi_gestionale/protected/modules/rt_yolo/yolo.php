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

$ModuloId=56;// modulo base mediazione


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

			case "show":
				$FunzioneId=2;
				$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
				if (sizeof($permesso))
					show($_REQUEST['YoloId']);
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
	
	$HtmlCommon->html_titolo_pagina($dizionario['yolo']['titolo_gestione'], 0, "", "");
	$HtmlCommon->html_titolo_box($dizionario['yolo']['titolo_gestione']);

	$db= new Database();
	$db->connect();
	
	include_once("yolo_validator.php");
	include_once("yolo_datatable.php");
 
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
		/* Nascondi la seconda colonna (DataA) */
		table td:nth-child(2), table th:nth-child(2) {
		  display: none;
		}
	</style>
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
    	<thead>
            <tr class="brain_tabellaTr">
            	<th width="20%"><?=$dizionario['yolo']['data']?></th>
				<th width="20%"><?=$dizionario['yolo']['data']?></th>
				<th width="40%"><?=$dizionario['yolo']['descrizione']?></th>
				<th width="20%"><?=$dizionario['yolo']['numero_righe']?></th>
				<th width="20%"><?=$dizionario['yolo']['esito']?></th>
                <th width="20%"><?=$dizionario['yolo']['azioni']?></th>
			</tr>
            
			<tr class="brain_tabellaFilter">
            	<th>Da<input type="date" class="searchDataDa"/><br>A<input class="searchDataA" type="date" /></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody> 
			<tr>
				<td colspan="5" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['caricamento_in_corso']?></td>
			</tr>
		</tbody>
	</table>
	<?php   
	$db->close();
}

function show($yoloId = null) {
	global $HtmlCommon, $db, $dizionario;

	$db = new Database();
	$db->connect();

	// Recupera le righe del file selezionato e info file
	$righe = [];
	$nomeFile = '';
	$dataFile = '';
	if ($yoloId) {
		$sql = "SELECT * FROM RT_YoloRiga WHERE YoloId = " . intval($yoloId) . " ORDER BY Progressivo ASC";
		$righe = $db->fetch_array($sql);

		// Recupera info file
		$sqlFile = "SELECT Nome, Data FROM RT_Yolo WHERE YoloId = " . intval($yoloId);
		$fileInfo = $db->query_first($sqlFile);
		if ($fileInfo) {
			$nomeFile = $fileInfo['Nome'];
			$dataFile = $fileInfo['Data'];
		}
	}

	// Titolo con nome e data file
	$titoloDettaglio = $dizionario['yolo']['dettaglio_file'];
	if ($nomeFile) {
		$titoloDettaglio .= " - " . htmlspecialchars($nomeFile);
	}
	if ($dataFile) {
		$titoloDettaglio .= " (" . date('d/m/Y', strtotime($dataFile)) . ")";
	}

	$HtmlCommon->html_titolo_pagina($titoloDettaglio, 1, "rt_yolo", "yolo.php");
	$HtmlCommon->html_titolo_box($titoloDettaglio);

	// Recupera le righe del file selezionato
	$righe = [];
	if ($yoloId) {
		$sql = "SELECT * FROM RT_YoloRiga WHERE YoloId = " . intval($yoloId) . " ORDER BY Progressivo ASC";
		$righe = $db->fetch_array($sql);
	}

	?>
	<style>
		#brain_datatables thead th {
			text-align: center;
			padding: 8px 12px;
			white-space: normal;
			word-wrap: break-word;
			overflow-wrap: break-word;
			word-break: break-word;
		}
		#brain_datatables tbody td {
			padding: 8px 12px;
		}
	</style>
	<table cellpadding="0" cellspacing="0" border="0" class="display" id="brain_datatables">
		<thead>
			<tr class="brain_tabellaTr">
				<th><?=$dizionario['yolo']['data_acquisto']?></th>
				<th><?=$dizionario['yolo']['codice_fascia']?></th>
				<th><?=$dizionario['yolo']['numero_pratica']?></th>
				<th><?=$dizionario['yolo']['progressivo']?></th>
				<th><?=$dizionario['yolo']['nominativo']?></th>
				<th><?=$dizionario['yolo']['codice_fiscale']?></th>
				<th><?=$dizionario['yolo']['data_prenotazione']?></th>
				<th><?=$dizionario['yolo']['data_inizio']?></th>
				<th><?=$dizionario['yolo']['data_fine']?></th>
				<th><?=$dizionario['yolo']['data_annullamento']?></th>
				<th><?=$dizionario['yolo']['valore_soggiorno']?></th>
				<th><?=$dizionario['yolo']['destinazione']?></th>
				<th><?=$dizionario['yolo']['premio']?></th>
				<th><?=$dizionario['yolo']['tipo_operazione']?></th>
			</tr>
		</thead>
		<tbody>
			<?php if (!empty($righe)) {
				foreach ($righe as $riga) { ?>
					<tr>
						<td><?php
							// DataAcquisto
							$val = $riga['DataAcquisto'];
							echo (preg_match('/^\d{8}$/', $val)) ? substr($val,0,2).'/'.substr($val,2,2).'/'.substr($val,4,4) : htmlspecialchars($val);
						?></td>
						<td><?=htmlspecialchars($riga['Codice Fascia'])?></td>
						<td><?=htmlspecialchars($riga['NumeroPratica'])?></td>
						<td><?=htmlspecialchars($riga['Progressivo'])?></td>
						<td><?=htmlspecialchars($riga['Nominativo'])?></td>
						<td><?=htmlspecialchars($riga['Codice Fiscale'])?></td>
						<td><?php
							// DataPrenotazioneSoggiorno
							$val = $riga['DataPrenotazioneSoggiorno'];
							echo (preg_match('/^\d{8}$/', $val)) ? substr($val,0,2).'/'.substr($val,2,2).'/'.substr($val,4,4) : htmlspecialchars($val);
						?></td>
						<td><?php
							// DataInizioSoggiorno
							$val = $riga['DataInizioSoggiorno'];
							echo (preg_match('/^\d{8}$/', $val)) ? substr($val,0,2).'/'.substr($val,2,2).'/'.substr($val,4,4) : htmlspecialchars($val);
						?></td>
						<td><?php
							// DataFineSoggiorno
							$val = $riga['DataFineSoggiorno'];
							echo (preg_match('/^\d{8}$/', $val)) ? substr($val,0,2).'/'.substr($val,2,2).'/'.substr($val,4,4) : htmlspecialchars($val);
						?></td>
						<td>
							<?php
							$val = $riga['DataAnnulamento'];
							if ($val === '01010001') {
								echo '-';
							} elseif (preg_match('/^\d{8}$/', $val)) {
								echo substr($val,0,2).'/'.substr($val,2,2).'/'.substr($val,4,4);
							} else {
								echo htmlspecialchars($val);
							}
							?>
						</td>
						<td>
							<?php
							$val = $riga['ValoreDelSoggiorno'];
							echo is_numeric($val) ? number_format($val / 100, 2, ',', '.') . '€' : htmlspecialchars($val);
							?>
						</td>
						<td><?=htmlspecialchars($riga['Destinazione'])?></td>
						<td>
							<?php
							$val = $riga['Premio'];
							echo is_numeric($val) ? number_format($val / 100, 2, ',', '.') . '€' : htmlspecialchars($val);
							?>
						</td>
						<td>
							<?php
							switch ($riga['TipoOperazione']) {
								case 'N':
									echo $dizionario['yolo']['nuovo'];
									break;
								case 'V':
									echo $dizionario['yolo']['variazione'];
									break;
								case 'C':
									echo $dizionario['yolo']['annullamento_con_rimborso'];
									break;
								case 'D':
									echo $dizionario['yolo']['annullamento_senza_rimborso'];
									break;
								default:
									echo htmlspecialchars($riga['TipoOperazione']);
							}
							?>
						</td>
					</tr>
				<?php }
			} else { ?>
				<tr>
					<td colspan="14" class="dataTables_empty"><i class='fa fa-spinner grey-dark' aria-hidden='true'></i><br><?=$dizionario['generale']['nessun_risultato']?></td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php
	// --- Esito file di output ---
	$nomeInput = $nomeFile;
	$nomeOutput = 'BB_OUT_' . $nomeInput;
	$dbOut = new Database();
	$dbOut->connect();
	$sqlOut = "SELECT YoloId, Nome FROM RT_Yolo WHERE Tipo = 'O' AND Nome = '" . addslashes($nomeOutput) . "'";
	$resOut = $dbOut->query_first($sqlOut);

	if (!$resOut) {
		echo '<div style="margin-top:20px;font-weight:bold;">'.$dizionario['yolo']['da_elaborare'].'</div>';
	} else {
		$yoloOutId = $resOut['YoloId'];
		$sqlRigaOut = "SELECT * FROM RT_YoloRigaOut WHERE YoloId = " . intval($yoloOutId) . " ORDER BY YoloRigaOutId ASC";
		$righeOut = $dbOut->fetch_array($sqlRigaOut);

		$esito = 'Errore';
		$colore = 'red';
		if (!empty($righeOut) && preg_match('/^0+TUTTO OK$/i', trim($righeOut[0]['Record']))) {
			$esito = 'OK';
			$colore = 'green';
			echo '<div style="margin-top:20px;font-weight:bold;color:green;">'.$dizionario['yolo']['file_di_output'].' ' . htmlspecialchars($nomeOutput) . ': '.$dizionario['yolo']['elaborato_con_successo'].'</div>';
		} else {
			echo '<div style="margin-top:20px;font-weight:bold;color:red;">'.$dizionario['yolo']['file_di_output'].' ' . htmlspecialchars($nomeOutput) . ': '.$dizionario['yolo']['errore_elaborazione'].'</div>';
			// Tabella dettagli errori con stesso stile della tabella precedente
			if (!empty($righeOut)) {
				echo '<table cellpadding="0" cellspacing="0" border="0" class="display" style="margin-top:10px;width:100%;">';
				echo '<thead>';
				echo '<tr class="brain_tabellaTr"><th colspan="2" style="text-align:left;">'.$dizionario['yolo']['record'].'</th></tr>';
				echo '</thead>';
				echo '<tbody style="background:#fff;">';
				foreach ($righeOut as $rigaOut) {
					echo '<tr>';
					echo '<td colspan="2" style="font-family:monospace;text-align:left;">' . htmlspecialchars($rigaOut['Record']) . '</td>';
					echo '</tr>';
					echo '<tr>';
					echo '<td style="width:200px;text-align:left;"><b>'.$dizionario['yolo']['codice_errore'].'</b><br>' . htmlspecialchars($rigaOut['CodiceErrore']) . '</td>';
					echo '<td style="text-align:left;"><b>'.$dizionario['yolo']['descrizione_errore'].'</b><br>' . htmlspecialchars($rigaOut['DescrizioneErrore']) . '</td>';
					echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
			}
		}
	}
	$dbOut->close();
	?>
	<?php
	$db->close();
}
