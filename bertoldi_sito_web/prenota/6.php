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

	if($_SESSION['tipo_tour'] == 0) {
		$_SESSION['extra_services_price'] = array();
		$_SESSION['totalprice'] = $_SESSION['total_price'];
		header("Location: /prenota/7.php".(($sessionId != '') ? '?session_id='.$sessionId : ''));
		exit;
	}

	$DataPartenza = date('Y-m-d');
    $datacorrente = Date('d/m/Y');
    $CorsaId = 0; 
    if (isset($_SESSION['CorsaId'])) {
        $CorsaId = $_SESSION['CorsaId'];
	}

    if (isset($_SESSION['ticket_date'])) {
        $dt = new DT();
        $DataPartenza = $_SESSION['ticket_date'];
        $dateTime = new DateTime($DataPartenza);
        $datacorrente = $dateTime->format('d/m/Y');
    }

    $tipoTour = $_SESSION['tipo_tour'];
    $seleziona_lesperienza = $_SESSION['seleziona_lesperienza'];

	if($_SESSION['tipo_tour'] == 1) {
		foreach ($_SESSION['BigliettoTipologiaPax'] as $idBiglietto => $quantita) {
			$sql = "SELECT * 
					FROM RT_TipologiaBiglietto 
					WHERE TipologiaBigliettoId = ".$idBiglietto." AND OccupaPosto = 1";
			$resultRows = $db->query_first($sql);
			if(isset($resultRows['TipologiaBigliettoId'])) {
				$tipologiaBiglietto = $resultRows['TipologiaBiglietto']; // "Da 1 a 5 passeggeri" o "Da 1 a 5 passeggeri exclusive"

				// Usa una regex per estrarre i numeri
				if (preg_match('/Da\s+(\d+)\s+a\s+(\d+)/i', $tipologiaBiglietto, $matches)) {
					$minPasseggeri = (int)$matches[1];
					$maxPasseggeri = (int)$matches[2]; 
				} else {
					// Nessuna corrispondenza trovata
					$minPasseggeri = 0;
					$maxPasseggeri = 0;
				}
			}
		}
	}
	
	if (isset($_SESSION['PrenotazioneId']) && !isset($_SESSION['load_extra'])) {
		$sql = "SELECT * 
				FROM RT_PrenotazioneBiglietto b
				LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
				WHERE PrenotazioneId = ".$_SESSION['PrenotazioneId']." AND t.OccupaPosto = 0";
		$resultRows = $db->fetch_array($sql);
		foreach ($resultRows as $value) {
			$_SESSION['BigliettoTipologiaPax'][$value['TipologiaBigliettoId']] = $value['NumeroPax'];
			$_SESSION['TipoBigliettoId'][$value['TipologiaBigliettoId']] = $value['TipologiaBiglietto'];
			$_SESSION['passangerId'][$value['TipologiaBigliettoId']] = $value['TipologiaBigliettoId'];
		}
		$_SESSION['load_extra'] = 1;
	}
	
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        foreach ($_POST['extra_services_price'] as $key => $value) {
            if ($value > 0) {
                $_SESSION['extra_services_price'][$key] = $value;
            }else{
                 unset($_SESSION['extra_services_price'][$key]);
            }
        }

        foreach ($_POST['BigliettoTipologiaPax'] as $key => $value) {

            if ($value > 0) {

                list($tipologiaBigliettoId, $tipologiaBiglietto) = explode('_', $key);

                $_SESSION['BigliettoTipologiaPax'][$tipologiaBigliettoId] = $value;
                $_SESSION['TipoBigliettoId'][$tipologiaBigliettoId] = $tipologiaBiglietto;
                $_SESSION['passangerId'][$tipologiaBigliettoId] = $tipologiaBigliettoId;
            } else {
                list($tipologiaBigliettoId, $tipologiaBiglietto) = explode('_', $key);
                unset($_SESSION['BigliettoTipologiaPax'][$tipologiaBigliettoId]);
                unset($_SESSION['TipoBigliettoId'][$tipologiaBigliettoId]);
                unset($_SESSION['passangerId'][$tipologiaBigliettoId]);
            }
        }
        if (count($_POST['BigliettoTipologiaPax']) > 0) {
            $_SESSION['totalprice'] = $_SESSION['total_price'] + $_POST['total_price'];
		} else {
			$_SESSION['totalprice'] = $_SESSION['total_price'];
		}
		
		if(isset($_SESSION['total_priceR'])) {
			$_SESSION['totalprice'] += $_SESSION['total_priceR'];
		}
    }
		
	

    $sqlServiziExtra = "SELECT t.*, s.Prezzo, s.Limite, s.LimiteMin, s.LimitePerNumPassegeri  
							FROM RT_TipologiaBiglietto t 
							LEFT JOIN RT_ListinoServizi s on s.BigliettoId = t.TipologiaBigliettoId 
							LEFT JOIN RT_ValiditaBigliettoDettaglio vb ON t.TipologiaBigliettoId = vb.BigliettoId
							LEFT JOIN RT_ValiditaBiglietto v ON v.ValiditaBigliettoId = vb.ValiditaBigliettoId
							WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = 1 AND t.OccupaPosto = 0 AND v.CorsaId = $CorsaId
							AND v.Dal <= '$DataPartenza' AND v.Al >= '$DataPartenza'
							ORDER BY t.TipologiaBigliettoPeso";
    $arrServiziExtra = $db->fetch_array($sqlServiziExtra);

	//dati per il controllo del blocco vendita della sosta
	$orarioPartenzaTour = $_SESSION['start_time'];
	$orarioArrivoTour = $_SESSION['end_time'];
	// differenza tra orario di fine corsa e orario max sosta
	$time1 = new DateTime($orarioArrivoTour);
	$time2 = new DateTime(Config::$orarioMaxSosta);
	$differenza = $time2->diff($time1);
	$oreMaxSosta = $differenza->h;
	
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

						<form action="" method="post" class="position-relative" id="ticket_form">

							<div class="form-group form-icon">
								<label for="data"><?=$dizionario['prenota']['servizi']?></label>
								<?php
								if (count($arrServiziExtra) > 0) :
									$total = 0;
									foreach ($arrServiziExtra as $k => $list) :
										if(isset($_SESSION['extra_services_price'][$list['TipologiaBigliettoId']])) {
											$total +=  $_SESSION['extra_services_price'][$list['TipologiaBigliettoId']];
										}
									?>

										<input type="hidden" name="TipoBigliettoId<?= $k ?>" id="TipoBigliettoId<?= $k ?>" value="<?= $list['TipologiaBigliettoId'] . '_' . $list['TipologiaBiglietto']; ?>">
										<input type="hidden" name="passangerId<?= $k ?>" id="passangerId<?= $k ?>" value="<?= $list['TipologiaBigliettoId'] ?>">


										<div class="control-group border-bottom row" style="padding-bottom: 32px;">
											<div class="col-md-3 col-xs-6">
												<span class="tipo_tour d-flex align-items-center title text-center title " style="margin-bottom:0px;">
													<?= $list['TipologiaBiglietto']; ?>
												</span>
												<span>
													<small class="d-block text-muted" ><?= htmlspecialchars($list['TipologiaBigliettoDescr']); ?>
														<?php if($list['LimitePerNumPassegeri'] > 0) { ?>
															<br><i style="color: red;"><?=$dizionario['prenota']['1_unita_e']?> <?= $list['LimitePerNumPassegeri'] ?> <?=$dizionario['prenota']['passeggeri_min']?>.</i>
														<?php } ?>
														<?php if($list['TipologiaBigliettoId'] == 23 && $oreMaxSosta > 0) { ?>
															<br><i style="color: red;">Max. <?= $oreMaxSosta ?>h.<br><?=$dizionario['prenota']['avviso_sosta']?></i>
														<?php } ?>
													</small>
												</span>
											</div>
											<div class="col-md-3 col-xs-6">
												<span class="tipo_tour d-flex align-items-center title text-center">
													<?= $list['Prezzo'] ?: 0; ?>€
												</span>
											</div>
											<?php if ( $list['TipologiaBigliettoId'] == 23 && $tipoTour == 1 && $orarioPartenzaTour >= Config::$orarioMaxSosta ) { ?>
												<div class="col-md-6 col-xs-6">
													<span style="color: red;"><?=$dizionario['prenota']['avviso_sosta_notturna']?></span>
												</div>
											<?php } else { ?>
												<div class="col-md-3 col-xs-6">
													<div class="number-input">
														<?php
														$limite = 0;
														if($list['TipologiaBigliettoId'] == 23) {
															if($oreMaxSosta > 0 && $oreMaxSosta < $list['Limite']) {
																$limite = $oreMaxSosta;
															} else {
																$limite = $list['Limite'];
															}
														} else {
															if($list['Limite'] > 0) {
																$limite = $list['Limite'];
															}
														} ?>
														<button type="button" class="btn btn-secondary btn-decrement" aria-label="Decrement">-</button>
														<?php 
														// Calcola il limite in base a LimitePerNumPassegeri
														if($list['LimitePerNumPassegeri'] > 0) {
															$limiteCalcolato = ceil($maxPasseggeri / $list['LimitePerNumPassegeri']);
															// Se esiste già un limite, usa il minore tra i due
															if($limite > 0) {
																$limite = min($limite, $limiteCalcolato);
															} else {
																$limite = $limiteCalcolato;
															}
														}
														?>
														<input <?=($limite > 0) ? 'max="'.$limite.'"' : ''?> 
															<?=($list['LimiteMin'] > 0) ? 'min="'.$list['LimiteMin'].'"' : 'min="0"'?>
														type="number" name="BigliettoTipologiaPax[<?= $list['TipologiaBigliettoId'] . '_' . $list['TipologiaBiglietto']; ?>]" value="<?= isset($_SESSION['BigliettoTipologiaPax'][$list['TipologiaBigliettoId']]) ? $_SESSION['BigliettoTipologiaPax'][$list['TipologiaBigliettoId']] : 0; ?>" id="" class="border number-ticket">
														<button type="button" class="btn btn-secondary btn-increment" aria-label="Increment">+</button>
													</div> 
												</div>
												<div class="col-md-3 col-xs-6">
													<span class="tipo_tour d-flex align-items-center title text-center total-price">

													<?= isset($_SESSION['extra_services_price'][$list['TipologiaBigliettoId']]) ? number_format($_SESSION['extra_services_price'][$list['TipologiaBigliettoId']], 2, '.', '') : " 0,00"; ?>€
													</span>
													<input type="hidden" name="extra_services_price[<?= $list['TipologiaBigliettoId'] ?>]" value="<?= isset($_SESSION['extra_services_price'][$list['TipologiaBigliettoId']]) ? $_SESSION['extra_services_price'][$list['TipologiaBigliettoId']] : 0; ?>" id="" class="border extra_total-price">
												</div>
											<?php } ?>
											
										</div>
								<?php
									endforeach;
								else:
									echo "<i>".$dizionario['prenota']['no_servizi']."</i>";
								endif;
								?>
							</div>
							<input type="hidden" name="total_price" class="services_total_price" value="<?php echo $total; ?>">
							<div class="px-5">
								<button type="submit" name="submit" class="btn btn-primary w-full btn-big mt-5 rounded-pill" id="continua">
									<?= $dizionario['prenota']['continua'] ?>
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

function totaliCalcolo() {
	var total = 0;
		$('.control-group').each(function() {
			var quantity = parseInt($(this).find('input[type="number"]').val());
			var pricePerItem = parseFloat($(this).find('.tipo_tour').eq(1).text()); // Assuming price is the second .tipo_tour element
			var totalPrice = (isNaN(quantity) ? 0 : quantity) * pricePerItem;
			$(this).find('.total-price').text(totalPrice.toFixed(2) + ' €');
			$(this).find('.extra_total-price').val(totalPrice);
			total += totalPrice;
		});
		$('.services_total_price').val(total);
}
$(document).ready(function() {
	
	$('.btn-decrement').click(function() {
		var input = $(this).next('.number-ticket');
		var currentValue = parseInt(input.val());
		if (currentValue > parseInt(input.attr('min'))) {
			input.attr('value', currentValue - 1);
			totaliCalcolo();
		}
	});

	$('.btn-increment').click(function() {
		var input = $(this).prev('.number-ticket');
		var currentValue = parseInt(input.val());
		if (input.attr('max') === undefined || currentValue < parseInt(input.attr('max'))) {
			input.attr('value', currentValue + 1);
			totaliCalcolo();
		}
	});
	
	$('input[type="number"]').on('keydown', function(event) {
		// Ottieni il valore attuale dell'input
		var valoreAttuale = $(this).val();

		// Controlla se il tasto premuto è un numero o un tasto speciale come backspace o delete
		if ((event.keyCode >= 48 && event.keyCode <= 57) || // numeri sopra la tastiera
			(event.keyCode >= 96 && event.keyCode <= 105) || // numeri sul tastierino numerico
			event.keyCode === 8 || // backspace
			event.keyCode === 9 || // tab
			event.keyCode === 13 || // invio
			event.keyCode === 46 || // delete
			event.keyCode === 37 || // freccia sinistra
			event.keyCode === 39) { // freccia destra
			// Se il tasto premuto è valido, lascia che l'evento si verifichi normalmente
			return true;
		} else {
			// Se il tasto premuto non è valido, previeni l'evento predefinito e non aggiornare il valore dell'input
			event.preventDefault();
			return false;
		}
	});
	
	$('input[type="number"]').on('input', function() {
		// var quantity = parseInt($(this).val());
		// var pricePerItem = parseFloat($(this).closest('.control-group').find('.tipo_tour').eq(1).text()); // Assuming price is the second .tipo_tour element
		// var totalPrice = (isNaN(quantity) ? 0 : quantity) * pricePerItem;
		// $(this).closest('.control-group').find('.total-price').text(totalPrice.toFixed(2) + ' €');
		totaliCalcolo();
	});

	$('#tipo_tour').on('keyup', function() {
		var data = $('#tipo_tour').val();
		$("#search_seleziona").submit();

	});

	$("#ticket_form").submit(function(e) {
		console.log(e);
		e.preventDefault();
		$.ajax({
			type: 'POST',
			url: "<?php echo $_SERVER['REQUEST_URI']; ?>", // Change to the actual filename of this page
			data: $(this).serialize(),
			success: function(response) {
				console.log(response);
				window.location = "/prenota/7.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";

			}
		});
	});

	$('#back').click(function() {
		<?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
			window.location.href = "/index.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
		<?php } else if($_SESSION['tipo_viaggio'] == 1) { ?>
			window.location = "/prenota/4.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
		<?php } else { ?>
			window.location = "/prenota/5.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
		<?php } ?>
	});

});
</script>
</html>
