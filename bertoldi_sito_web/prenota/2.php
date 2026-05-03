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
if(!isset($_SESSION['tipo_tour']) || !isset($_SESSION['seleziona_lesperienza'])) {
	// Effettua il redirect alla pagina index.php
	header("Location: /index.php");
	exit; // Termina lo script per evitare l'esecuzione di ulteriori istruzioni
}

$DataPartenza = date('Y-m-d');
    $datacorrente = Date('d/m/Y');
    $LineaId = 0; //$arr_corse[0]['CorsaId'];
    if (isset($_SESSION['seleziona_lesperienza'])) {
        $LineaId = $_SESSION['seleziona_lesperienza'];
	}
    if (isset($_SESSION['ticket_date'])) {
        $dt = new DT();
        $DataPartenza = $_SESSION['ticket_date'];
        $dateTime = new DateTime($DataPartenza);
        $datacorrente = $dateTime->format('d/m/Y');
    }
	$tipoTour = $_SESSION['tipo_tour'];
	$selezionaLesperienza = $_SESSION['seleziona_lesperienza'];

	if (isset($_SESSION['PrenotazioneId']) && !isset($_SESSION['load_passeggeri'])) {
		$sql = "SELECT * 
					FROM RT_PrenotazioneBiglietto b
					LEFT JOIN RT_TipologiaBiglietto t on t.TipologiaBigliettoId = b.TipologiaBigliettoId
					WHERE PrenotazioneId = ".$_SESSION['PrenotazioneId']." AND t.OccupaPosto = 1";
		$resultRows = $db->fetch_array($sql);
		foreach ($resultRows as $value) {
			$_SESSION['BigliettoTipologiaPax'][$value['TipologiaBigliettoId']] = $value['NumeroPax'];
			$_SESSION['TipoBigliettoId'][$value['TipologiaBigliettoId']] = $value['TipologiaBiglietto'];
			$_SESSION['passangerId'][$value['TipologiaBigliettoId']] = $value['TipologiaBigliettoId'];
		}
		$_SESSION['load_passeggeri'] = 1;
	}

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        unset($_SESSION['BigliettoTipologiaPax']);
        unset($_SESSION['TipoBigliettoId']);
        unset($_SESSION['passangerId']);
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
    }

    //$sqlCorse = "SELECT * FROM RT_TipologiaBiglietto t WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = $tipoTour AND t.OccupaPosto = 1 ORDER BY t.TipologiaBigliettoPeso";
	$sqlCorse = "SELECT * 
				FROM RT_TipologiaBiglietto t 
				LEFT JOIN RT_ValiditaBigliettoDettaglio vb ON t.TipologiaBigliettoId = vb.BigliettoId
				LEFT JOIN RT_ValiditaBiglietto v ON v.ValiditaBigliettoId = vb.ValiditaBigliettoId
				LEFT JOIN RT_Corsa c ON c.CorsaId = v.CorsaId
				WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = $tipoTour AND t.OccupaPosto = 1 
				AND c.LineaId = $LineaId AND v.Dal <= '$DataPartenza' AND v.Al >= '$DataPartenza'
				AND TipologiaBigliettoId <> 11
				GROUP BY t.TipologiaBigliettoId
				ORDER BY t.TipologiaBigliettoPeso";
	
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
						
						<?php if (count($arr_tour) > 0) { ?>
						
							<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" class="position-relative" id="ticket_form">

								<div class="form-group form-icon">
									<label for="data"><?=$dizionario['prenota']['passeggeri']?></label>
									<?php if($tipoTour == 1) { ?>
									<select name="tipo_biglietto" id="tipo_biglietto">
											<option value="">-- <?=$dizionario['prenota']['seleziona']?> --</option>
										<?php foreach ($arr_tour as $k => $list) { ?>
											<option data-description="<?=$list['TipologiaBigliettoDescr']?>" value="BigliettoTipologiaPax[<?= $list['TipologiaBigliettoId'] . '_' . $list['TipologiaBiglietto']; ?>]" <?= isset($_SESSION['BigliettoTipologiaPax'][$list['TipologiaBigliettoId']]) ? 'selected' : 0; ?>><?= $list['TipologiaBiglietto'] ?> </option>
										<?php } ?>
									</select>
									<div id="note_biglietti" style="margin-top:10px;"></div>
									<?php } ?>
									
									<?php foreach ($arr_tour as $k => $list) { ?>
										<div class="control-group border-bottom" <?php if($tipoTour == 1) { echo "style='display:none'";}?>>
											<label for="tipo_tour d-flex align-items-center"><span class="title" <?= (isset($list['TipologiaBigliettoDescr'])) ? "style='margin-bottom: 0px;'" : "" ?>>
												<?= $list['TipologiaBiglietto'] ?></span>
												<?php if (!empty($list['TipologiaBigliettoDescr'])): ?>
													<div class="note_tour" style="margin-top:10px; margin-bottom: 32px;"><?= htmlspecialchars($list['TipologiaBigliettoDescr']) ?></div>
												<?php endif; ?>
											</label>
											
											<div class="number-input">
												<button type="button" class="btn btn-secondary btn-decrement" aria-label="Decrement">-</button>
												<input type="number" min="0" <?= ($tipoTour == 1)? 'max="1"' : ''?> name="BigliettoTipologiaPax[<?= $list['TipologiaBigliettoId'] . '_' . $list['TipologiaBiglietto']; ?>]" value="<?= isset($_SESSION['BigliettoTipologiaPax'][$list['TipologiaBigliettoId']]) ? $_SESSION['BigliettoTipologiaPax'][$list['TipologiaBigliettoId']] : 0; ?>" id="ticket-<?= $k ?>" class="border number-ticket">
												<button type="button" class="btn btn-secondary btn-increment" aria-label="Increment">+</button>
											</div>
											
											<input type="hidden" name="TipoBigliettoId<?= $k ?>" id="TipoBigliettoId<?= $k ?>" value="<?= $list['TipologiaBigliettoId'] . '_' . $list['TipologiaBiglietto']; ?>">
											<input type="hidden" name="passangerId<?= $k ?>" id="passangerId<?= $k ?>" value="<?= $list['TipologiaBigliettoId'] ?>">
										</div>
									<?php } ?>
								</div>
								<div class="px-5">
									<button class="btn btn-primary w-full btn-big mt-5 rounded-pill" id="continua" type="submit">
										<?=$dizionario['prenota']['continua']?>
									</button>
								</div>
							</form>
						<?php } else { ?>
							<div class="available-item" style="text-align:center;">
								<?=$dizionario['prenota']['no_tour']?><br>
								<?=$dizionario['prenota']['torna_allo']?> <a style="display:contents" href="/prenota/1.php"><?=$dizionario['prenota']['step_precedente']?></a> <?=$dizionario['prenota']['o_alla']?> <a style="display:contents" href="/index.php<?= $_SESSION['code_gestore'] ?>">home</a> <?=$dizionario['prenota']['per_selezionare_data']?>.
							</div>
						<?php } ?>
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
				$('.btn-decrement').click(function() {
					var input = $(this).next('.number-ticket');
					var currentValue = parseInt(input.val());
					if (currentValue > parseInt(input.attr('min'))) {
						input.val(currentValue - 1);
						input.change();
					}
				});

				$('.btn-increment').click(function() {
					var input = $(this).prev('.number-ticket');
					var currentValue = parseInt(input.val());
					if (input.attr('max') === undefined || currentValue < parseInt(input.attr('max'))) {
						input.val(currentValue + 1);
						input.change();
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

                $('#back').click(function() {
					<?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
						window.location.href = "/index.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
					<?php } else { ?>
						window.location = "/prenota/1.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
					<?php } ?>
                });

				$('.number-ticket').change(function(){
					if($(this).val() < 0) {
						$(this).val(0); 
					}
					
					<?php if($tipoTour == 1) {?>
						if($(this).val() > 1) {
							$(this).val(1); 
						}
						if ($(this).val() == 1) {
							// Imposta il valore degli altri input number a 0
							$('input[type="number"]').not(this).val(0);
						}
					<?php } ?>
					
				});

                $("#ticket_form").submit(function(e) {
                    var inputs = document.querySelectorAll('input[type="number"]');
                    var atLeastOneFilled = Array.from(inputs).some(function(input) {
                        return input.value !== '0';
                    });
                    e.preventDefault();
                    
                    if (!atLeastOneFilled) {
                        alert('Inserisci almeno un passeggero.');
                        e.preventDefault(); // Prevent form submission
                    } else {
                        // Invio evento a dataLayer
                        if (typeof dataLayer !== 'undefined') {
                            dataLayer.push({
                                'event': 'click_select_people'
                            });
                        }
                        
                        $.ajax({
                            type: 'POST',
                            url: "<?php echo $_SERVER['REQUEST_URI']; ?>", // Change to the actual filename of this page
                            data: $('#ticket_form').serialize(),
                            success: function(response) {
                                window.location = "/prenota/3.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
                            }
                        });
                    }
                });
				
				$("#tipo_biglietto").change(function(){
					var value = $("#tipo_biglietto").val();
					$('.number-ticket').val(0);
					$('input[name="'+value+'"]').val(1);
					if(value != '') {
					var description = $(this).find('option:selected').data('description');
						$('#note_biglietti').html('<u>Note:</u> '+description);
					} else {
						$('#note_biglietti').html('');
					}
				});

            });
        </script>

</html>
