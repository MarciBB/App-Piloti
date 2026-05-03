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

if(!isset($_SESSION['ticket_date'])) {
	// Effettua il redirect alla pagina index.php
	header("Location: /index.php".(($sessionId != '') ? '?session_id='.$sessionId : ''));
	exit; // Termina lo script per evitare l'esecuzione di ulteriori istruzioni
}

if (isset($_REQUEST['DataPartenza'])) {
	$DataPartenza = $_REQUEST['DataPartenza'];
}
if (isset($_REQUEST['CorsaId'])) {
	$CorsaId = $_REQUEST['CorsaId'];
}

$DataPartenza = date('Y-m-d');
$datacorrente = Date('d/m/Y');
$CorsaId = 0; //$arr_corse[0]['CorsaId'];
if (isset($_POST['CorsaId'])) {
	$CorsaId = $_POST['CorsaId'];
	$sqlLinea = "SELECT * FROM RT_Corsa WHERE CorsaId = $CorsaId";
	$rowTemp = $db->query_first($sqlLinea);
	$LineaId = $rowTemp['LineaId']; 
}
if (isset($_POST['DataPartenza'])) {
	$dt = new DT();
	$post_dal = $_POST['DataPartenza'];
	$DataPartenza = $_POST['DataPartenza'];
	$dateTime = new DateTime($DataPartenza);
	$datacorrente = $dateTime->format('d/m/Y');
}

if (isset($_POST['PrenotazioneId'])) {
	$_SESSION['PrenotazioneId'] = $_POST['PrenotazioneId'];
	$_SESSION['CorsaId'] = $_POST['CorsaId'];
	$_SESSION['DataPartenza'] = $_POST['DataPartenza'];
	$_SESSION['ticket_date'] = $_POST['DataPartenza'];
	$_SESSION['tipo_tour'] = $_POST['tipo_tour'];
}

if (isset($_POST['seleziona_lesperienza'])) {
	$seleziona_lesperienza = $_POST['seleziona_lesperienza'];
	$_SESSION['seleziona_lesperienza'] = $seleziona_lesperienza;
}
if(isset($_SESSION['tipo_tour']) && !isset($_POST['tipo_tour'])) {
	$tipoTour = $_SESSION['tipo_tour'];
} else {
	$tipoTour = 0;
	if (isset($_REQUEST['tipo_tour'])) {
		$tipoTour = $_REQUEST['tipo_tour'];
	}
	if (isset($_POST['tipo_tour'])) {
		$tipoTour = $_POST['tipo_tour'];
	}
	$_SESSION['tipo_tour'] = $tipoTour;
}

$sqlCorse = "SELECT l.*
			FROM RT_Linea l
			LEFT JOIN RT_Percorso p ON p.PercorsoId = l.PercorsoId
			WHERE l.Stato = 1 
			  AND l.Cancella = 0 
			  AND l.TipoTour = $tipoTour 
			  AND p.Stato = 1 
			  AND p.Cancella = 0
			  AND l.LineaId <> 14
			  AND l.IsWebSelling = 1
			  AND (CASE 
						WHEN IFNULL(
							  (SELECT COUNT(*) 
							   FROM RT_Tratta t 
							   WHERE t.LineaId = l.LineaId 
								 AND t.TrattaTipoId = 2 
								 AND t.Stato = 1 
								 AND t.Cancella = 0
							   GROUP BY t.LineaId), 0) = 0 
						THEN 0
						ELSE 1
					END) = 0
			ORDER BY p.PercorsoPeso, l.LineaPeso, l.LineaNome;";
$arr_tour = $db->fetch_array($sqlCorse);


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
				<?php }  else { ?>
				
					<div class="form-container">
						<div class="card-cta">
							<p><?=$dizionario['prenota']['compila_campi']?></p>
						</div>
						<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="tipo_tour_form" method="post">
							<input type="hidden" name="tipo_tour" class="selected_tipo">
						</form>
						<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="ticket_form">
							<div class="form-group">
								<label for="tipo_tour">Tipo Tour</label>
								<select name="tipo_tour" id="tipo_tour">
									<option <?= $tipoTour == 0 ? 'selected' : ''; ?> value="0"><?=$dizionario['prenota']['tour_gruppo']?> </option>
									<option <?= $tipoTour == 1 ? 'selected' : ''; ?> value="1"><?=$dizionario['prenota']['tour_privato']?> </option>
								</select>
							</div>
							<div class="form-group">
								<label for="seleziona_lesperienza"><?=$dizionario['prenota']['seleziona_esperienza']?></label>
								<select name="seleziona_lesperienza" id="seleziona_lesperienza">
									<?php
									if (count($arr_tour) > 0) {
										foreach ($arr_tour as $t) {
									?>
											<option data-description="<?=$t['LineaDescrizione']?>" <?= isset($_SESSION['seleziona_lesperienza']) && $_SESSION['seleziona_lesperienza'] === $t['LineaId'] ? 'selected' : ''; ?> value="<?= $t['LineaId']; ?>"> <?= $t['LineaNome']; ?> </option>
									<?php
										}
									}
									?>
								</select>
								<div id="note_tour" style="margin-top:10px;"></div>
							</div>
							<button class="btn btn-primary w-full btn-big" id="continua" type="submit">
								<?=$dizionario['prenota']['continua']?>
							</button>
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
					<?php if (isset($_POST['PrenotazioneId'])) { ?>
						window.location.href = "./show-ticket.php?PrenotazioneId=<?= $_POST['PrenotazioneId'] ?>&CorsaId=<?= $_POST['CorsaId'] ?>&DataPartenza=<?= $_POST['DataPartenza'] ?>";
					<?php } else if (isset($_SESSION['PrenotazioneId'])) { ?>
						window.location.href = "./show-ticket.php?PrenotazioneId=<?= $_SESSION['PrenotazioneId'] ?>&CorsaId=<?= $_SESSION['CorsaId'] ?>&DataPartenza=<?= $_SESSION['DataPartenza'] ?>";
					<?php } else { ?>
						window.location = "/index.php<?= ($_SESSION['code_gestore'] != '') ? $_SESSION['code_gestore'] . (($sessionId != '') ? '&session_id=' . $sessionId : '') : (($sessionId != '') ? '?session_id=' . $sessionId : '') ?>";
					<?php } ?>
                });

				$('#tipo_tour').on('change', function() {
					var data = $('#tipo_tour').val();
					console.log(data);
					$(".selected_tipo").val(data);
					$("#tipo_tour_form").submit();

				});

				$("#ticket_form").submit(function(e) {
					e.preventDefault();
					
					// Invio evento a dataLayer
					if (typeof dataLayer !== 'undefined') {
						dataLayer.push({
							'event': 'click_select_tour'
						});
					}
					
					$.ajax({
						type: 'POST',
						url: "<?php echo $_SERVER['REQUEST_URI']; ?>", // Change to the actual filename of this page
						data: $('#ticket_form').serialize(),
						success: function(response) {
							window.location = "/prenota/2.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
						}
					});
				});
				
				$("#seleziona_lesperienza").change(function(){
					var description = $(this).find('option:selected').data('description');
					if(description != '') {
						$('#note_tour').html('<u>Note:</u> '+description);
					} else {
						$('#note_tour').html('');
					}
				});
				var description = $("#seleziona_lesperienza").find('option:selected').data('description');
				if(description != '') {
					$('#note_tour').html('<u>Note:</u> '+description);
				} else {
					$('#note_tour').html('');
				}
			});
		</script>

</html>
