<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include($basepath.'/main_include.php');

$PageTitle = $dizionario['ticket']['titolo'];
$PageDescription = $dizionario['ticket']['descr'];
$PageKeywords = $dizionario['ticket']['key'];

$page_title = "Home";

$LinkActive = 1;

if(isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];
} else {
	$user = null;
}

$db = new Database();
$db->connect();

if(!isset($_SESSION['tipo_tour'])) {
	// Effettua il redirect alla pagina index.php
	header("Location: /index.php");
	exit; // Termina lo script per evitare l'esecuzione di ulteriori istruzioni
}

$DataPartenza = date('Y-m-d');
$datacorrente = Date('d/m/Y');
$CorsaId = 0; 
if (isset($_SESSION['PrenotazioneId'])) {
	$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = ".$_SESSION['PrenotazioneId']." AND Direzione = 'A'";
	$resultRow = $db->query_first($sql);
	$_SESSION['porto_partenza'] = $resultRow['ComuneSalitaId'];
	$_SESSION['porto_destinazione'] = $resultRow['ComuneDiscesaId'];
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$_SESSION['PrenotazioneId'];
	$resultRow = $db->query_first($sql);
	$_SESSION['tipo_viaggio'] = $resultRow['TipoViaggioId'];
}


if (isset($_POST['CorsaId']))
	$CorsaId = $_POST['CorsaId'];

if (isset($_POST['DataPartenza'])) {
	$dt = new DT();
	$post_dal = $_POST['DataPartenza'];
	$DataPartenza = $_POST['DataPartenza'];
	$dateTime = new DateTime($DataPartenza);
	$datacorrente = $dateTime->format('d/m/Y');
}

if (isset($_POST['porto_partenza'])) {
	$porto_partenza = $_POST['porto_partenza'];
	$_SESSION['porto_partenza'] = $porto_partenza;
}
if (isset($_POST['porto_destinazione'])) {
	$porto_destinazione = $_POST['porto_destinazione'];
	$_SESSION['porto_destinazione'] = $porto_destinazione;
}
if (isset($_POST['tipo_viaggio'])) {
	$tipo_viaggio = $_POST['tipo_viaggio'];
	$_SESSION['tipo_viaggio'] = $tipo_viaggio;
}

$tipoTour = $_SESSION['tipo_tour'];
$selezionaLesperienza = $_SESSION['seleziona_lesperienza'];

$sqlPortoPartenza = "SELECT f.FermataId, f.FermataNome, c.Comune, c.ComuneId, o.CorsaId, MIN(o.Orario) AS Orario, o.GiorniAggiuntivi, f.TrattaId FROM RT_Fermata f LEFT JOIN RT_Orario o ON f.FermataId = o.FermataId LEFT JOIN Comune c ON c.ComuneId = f.ComuneId LEFT JOIN Provincia p ON c.provincia = p.ProvinciaId LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId WHERE t.LineaId = $selezionaLesperienza AND o.Orario IS NOT NULL AND o.Orario <> '' AND o.Stato = 1 AND o.Cancella = 0 AND f.Stato = 1 AND f.Cancella = 0 AND f.IsPickup = 1 AND c.ComuneId IS NOT NULL AND f.WebSelling = 1 GROUP BY c.ComuneId ORDER BY c.Comune ASC";
$arrPortoPartenza = $db->fetch_array($sqlPortoPartenza);
foreach ($arrPortoPartenza as &$item) {
    if (isset($item['Comune'])) {
        $item['Comune'] = rimuoviParentesiComune($item['Comune']);
    }
}

$sqlPortoDestinazione = "SELECT f.FermataId, f.FermataNome, c.Comune, c.ComuneId, o.CorsaId, MIN(o.Orario) AS Orario, o.GiorniAggiuntivi, f.TrattaId FROM RT_Fermata f LEFT JOIN RT_Orario o ON f.FermataId = o.FermataId LEFT JOIN Comune c ON c.ComuneId = f.ComuneId LEFT JOIN Provincia p ON c.provincia = p.ProvinciaId LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId WHERE t.LineaId = $selezionaLesperienza AND o.Orario IS NOT NULL AND o.Orario <> '' AND o.Stato = 1 AND o.Cancella = 0 AND f.Stato = 1 AND f.Cancella = 0 AND f.IsDropOff = 1 AND c.ComuneId IS NOT NULL AND f.WebSelling = 1 GROUP BY c.ComuneId ORDER BY c.Comune ASC";
$arrPortoDestinazione = $db->fetch_array($sqlPortoDestinazione);
foreach ($arrPortoDestinazione as &$item) {
    if (isset($item['Comune'])) {
        $item['Comune'] = rimuoviParentesiComune($item['Comune']);
    }
}

//verifica se non ci sono porti da selezionare
$noPorti = false;
if(count($arrPortoPartenza) == 0 || count($arrPortoDestinazione) == 0) {
	$noPorti = true;
}

if($selezionaLesperienza == 1) {
	$_SESSION['tipo_viaggio'] = 2;
} else {
	$_SESSION['tipo_viaggio'] = 1;
}

//verifica se sono in tour di gruppo e il porto da selezionare è uno solo per andata e ritorno
if(count($arrPortoPartenza) == 1 && count($arrPortoDestinazione) == 1 && $tipoTour == 0) {
	//seleziono i porti in automatico e vado allo step successivo se non sono ancora selezionati
	$_SESSION['porto_partenza'] = $arrPortoPartenza[0]['ComuneId'];
	$_SESSION['porto_destinazione'] = $arrPortoDestinazione[0]['ComuneId'];
	$_SESSION['Skip3Step'] = 2;
	// Costruzione del redirect URL
	$urlStepNext = "/prenota/4.php";
	if ($sessionId != '') {
		$urlStepNext .= "?session_id=" . $sessionId;
	}

	// Redirect alla pagina specificata
	header("Location: $urlStepNext");

} else {
	$_SESSION['Skip3Step'] = 0;
}

//verifico se sono in tour privato per fare in modo che gestisca i porti con una sola select

	$selectPortoAR = array();
	foreach ($arrPortoPartenza as $portoPar) {
		foreach ($arrPortoDestinazione as $portoDes) {
			if(trim(strtoupper($portoPar['Comune'])) ==  trim(strtoupper($portoDes['Comune']))) {
				$selectPortoAR[$portoPar['ComuneId'].'_'.$portoDes['ComuneId']] = $portoPar['Comune'];
			}
		}
	}


?>
<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>
</head>

<body class="main-bg" id="nuova-prenotazione">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>  

	
    <!-- Wrapper
	================================================== -->
	<div id="page" class="hfeed site fullwidth">
    	
        
        <div class="main-container">
			<div class="content">
				<div style="margin-bottom:10px;" class="benvenuto-plugin">
					<h2><?=$dizionario['prenota']['prenota_il_tuo_tour']?></h2>
					<p><?=$dizionario['prenota']['segui_passaggi']?></p>
				</div>
                <div class="info-bar">
                    <button class="btn btn-rounded btn-primary" id="back">
                        <svg height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
					<?php
					if(isset($_SESSION['gestore']) && $_SESSION['gestore'] != -1){
						echo '<div class="center-info">';
						echo '<br>'.$dizionario['prenota']['prenotazione_gestita_da'].'<b>'.$_SESSION['gestore']['RagioneSociale']."</b>";
						echo '</div>';
					}
					?>
                    <div class="info">
                        <?php if (isset($_POST['PrenotazioneId']) || isset($_SESSION['PrenotazioneId'])) { ?>
							<span><?=$dizionario['prenota']['modifica_ticket']?></span>
						<?php } else { ?>
							<span><?=$dizionario['prenota']['nuovo_ticket']?></span>							
						<?php } ?>
                        <div class="date"><?= date('M d, Y', strtotime($_SESSION['ticket_date'])) ?></div>
                    </div>
                </div>
				<?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
					<div class="form-container" style="padding:20px 0px;">
						<div class="available-item" style="text-align:center;">
							<?=$dizionario['prenota']['no_modifica_data_trascorsa']?>
							<br>
							<?=$dizionario['prenota']['torna_alla']?> <a style="display:contents" href="/index.php<?= $_SESSION['code_gestore'] ?>">home</a> <?=$dizionario['prenota']['per_altre_operazioni']?>
						</div>
					</div>
				<?php }  else if($noPorti) { ?>
					<div class="form-container" style="padding:20px 0px;">
						<div class="available-item" style="text-align:center;">
							<?=$dizionario['prenota']['no_porti']?><br>
							<?=$dizionario['prenota']['torna_allo']?> <a style="display:contents" href="/prenota/1.php"><?=$dizionario['prenota']['step_precedente']?></a> <?=$dizionario['prenota']['o_alla']?> <a style="display:contents" href="/index.php<?= $_SESSION['code_gestore'] ?>">home</a> <?=$dizionario['prenota']['per_selezionare_data']?>.
						</div>
					</div>
				<?php }  else { ?>
				
					<div class="form-container">
						<div class="card-cta">
							<p><?=$dizionario['prenota']['compila_campi']?></p>
						</div>
						
						<form method="post" id="ticket_form">
							
								<div class="form-group" >
									<label for="porto_partenza"><?=$dizionario['prenota']['porto_partenza_destinazione']?></label>
									<select name="porto_partenza_destinazione" id="porto_partenza_destinazione">
										<?php
										if (count($selectPortoAR) > 0) {
											foreach ($selectPortoAR as  $id => $tempPorto) {
										?>

												<option value="<?= $id ?>" <?= (isset($_SESSION['porto_partenza']) && $_SESSION['porto_partenza']."_".$_SESSION['porto_destinazione'] == $id) ? 'selected' : '' ?>><?= $tempPorto ?> </option>
										<?php
											}
										}
										?>
									</select>

								</div>
							
							
							<div class="form-group"  style='display:none;' >
								<label for="porto_partenza"><?=$dizionario['prenota']['porto_partenza']?> </label>
								<select name="porto_partenza" id="porto_partenza">
									<?php
									if (count($arrPortoPartenza) > 0) {
										foreach ($arrPortoPartenza as  $list) {
									?>

											<option value="<?= $list['ComuneId'] ?>" <?= (isset($_SESSION['porto_partenza']) && $_SESSION['porto_partenza'] == $list['ComuneId']) ? 'selected' : '' ?>><?= $list['Comune'] ?> </option>
									<?php
										}
									}
									?>
								</select>
							</div>
							<div class="form-group" style='display:none;'>
								<label for="porto_destinazione"><?=$dizionario['prenota']['porto_destinazione']?> </label>
								<select name="porto_destinazione" id="porto_destinazione">
									<?php
									if (count($arrPortoDestinazione) > 0) {
										foreach ($arrPortoDestinazione as  $list) {
									?>

											<option value="<?= $list['ComuneId'] ?>" <?= (isset($_SESSION['porto_destinazione']) && $_SESSION['porto_destinazione'] == $list['ComuneId']) ? 'selected' : '' ?>><?= $list['Comune'] ?> </option>
									<?php
										}
									}
									?>
								</select>
								<!-- <input type="text" value="<?= $list['CorsaId'] ?>"> -->
							</div>
							<?php if ($tipoTour == 0) : ?>
								<!--<div class="form-group">
									<label for="tipo_viaggio">Tipo Viaggio </label>
									<select name="tipo_viaggio" id="tipo_viaggio">
										<option <?= (isset($_SESSION['tipo_viaggio']) && $_SESSION['tipo_viaggio'] == 1) ? 'selected' : ''; ?> value="1">Corsa Singola </option>
										<option <?= (isset($_SESSION['tipo_viaggio']) && $_SESSION['tipo_viaggio'] == 2) ? 'selected' : ''; ?> value="2">Andata e Ritorno </option>
									</select>
								</div>
								-->
								<input type="hidden" name="tipo_viaggio" id="tipo_viaggio" value="2">
							<?php else: ?>
								<input type="hidden" name="tipo_viaggio" id="tipo_viaggio" value="1">
							<?php endif; ?>

							<div class="px-5">
								<button type="submit" class="btn btn-primary w-full btn-big mt-5 rounded-pill" id="continua">
									<?=$dizionario['prenota']['continua']?>
								</button>
							</div>
						</form>


					</div>
				<?php } ?>
            </div>
		</div>
        
        
        
            

		<!-- Bottom
		================================================== -->
		<?php include_once($basepath."/include/bottom.php"); ?>
        	 
        
        <!-- Footer
        ================================================== -->
        <?php include_once($basepath."/include/footer.php"); ?>    
        
    </div>
    <!-- #page -->
  
   	<?php include_once($basepath."/include/html_close.php"); ?>   

</body>
<script type="text/javascript">
            $(document).ready(function() {
               

                $('#back').click(function() {
					<?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
						window.location.href = "/index.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
					<?php } else { ?>
						window.location = "/prenota/2.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
					<?php } ?>
                });


                $("#ticket_form").submit(function(e) {
                    e.preventDefault();
                    $.ajax({
                        type: 'POST',
                        url: "<?php echo $_SERVER['REQUEST_URI']; ?>", // Change to the actual filename of this page
                        data: $('#ticket_form').serialize(),
                        success: function(response) {
                            // console.log(response);
                            window.location = "/prenota/4.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
                        }
                    });
                });


				$('#porto_partenza_destinazione').change(function(){ 
					var porti = $('#porto_partenza_destinazione').val().split("_");

					$('#porto_partenza').val(porti[0]);
					$('#porto_destinazione').val(porti[1]);

				});

            });
        </script>
</html>