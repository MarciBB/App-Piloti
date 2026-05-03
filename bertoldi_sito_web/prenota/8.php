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
if (isset($_SESSION['ticket_date'])) {
	$dt = new DT();
	$DataPartenza = $_SESSION['ticket_date'];
	$dateTime = new DateTime($DataPartenza);
	$datacorrente = $dateTime->format('d/m/Y');
}

$tipoTour = $_SESSION['tipo_tour'];
$seleziona_lesperienza = $_SESSION['seleziona_lesperienza'];

$sqlServiziExtra = "SELECT t.*, s.Prezzo, s.Limite FROM RT_TipologiaBiglietto t LEFT JOIN RT_ListinoServizi s on s.BigliettoId = t.TipologiaBigliettoId WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = 1 AND t.OccupaPosto = 0 ORDER BY t.TipologiaBigliettoPeso";
$arrServiziExtra = $db->fetch_array($sqlServiziExtra);


$_SESSION['pagamentoId'] = $_SESSION['pagamento'] == 'contanti' ? 1 : 3;

$coupon = $_SESSION['coupon'];
$coupon_importo = $_SESSION['coupon_importo'];


// Recupera info pagamento Paypal (id 1) e Stripe (id 22)
$sqlPaypal = "SELECT * FROM RT_PagamentoTipo WHERE PagamentoTipoId = 5";
$paypalInfo = $db->query_first($sqlPaypal);

$sqlStripe = "SELECT * FROM RT_PagamentoTipo WHERE PagamentoTipoId = 22";
$stripeInfo = $db->query_first($sqlStripe);

// Calcola supplementi
$paypalSupplemento = 0;
$paypalMsg = '';
if ($paypalInfo['SupplementoFisso'] != 0 || $paypalInfo['SupplementoPercentuale'] != 0) {
	$paypalSupplemento = $_SESSION['totalprice'] * ($paypalInfo['SupplementoPercentuale'] / 100) + $paypalInfo['SupplementoFisso'];
	$paypalMsg = " + " . number_format($paypalSupplemento, 2, ',', '.') . "€ " . $dizionario['prenota']['supplemento_pagamento'];
}

$stripeSupplemento = 0;
$stripeMsg = '';
if ($stripeInfo['SupplementoFisso'] != 0 || $stripeInfo['SupplementoPercentuale'] != 0) {
	$stripeSupplemento = $_SESSION['totalprice'] * ($stripeInfo['SupplementoPercentuale'] / 100) + $stripeInfo['SupplementoFisso'];
	$stripeMsg = " + " . number_format($stripeSupplemento, 2, ',', '.') . "€ " . $dizionario['prenota']['supplemento_pagamento'];
}

?>
<!DOCTYPE html>
<html lang="it-IT">
<head>
<?php 
include_once($basepath."/include/meta.php");
?>
</head>

<body  class="main-bg modal-success-open" id="conferma-prenotazione">

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
							
							<br>
							<?=$dizionario['prenota']['torna_alla']?> <a style="display:contents" href="/index.php<?= $_SESSION['code_gestore'] ?>">home</a> <?=$dizionario['prenota']['per_altre_operazioni']?>
						</div>
					</div>
				<?php }  else { ?>
			
					<form action="/splash_prenota.php<?= ($_SESSION['code_gestore'] != '') ? $_SESSION['code_gestore'] . (($sessionId != '') ? '&session_id=' . $sessionId : '') : (($sessionId != '') ? '?session_id=' . $sessionId : '') ?>" class="new-ticket" id="new-ticket" method="POST">
						<div class="available-list-header">
							<?=$dizionario['prenota']['conferma_prenotazione']?>
						</div>
						<div class="title iframe-none">
							<span><?=$dizionario['prenota']['tour']?></span>
							<div class="dashes"></div>
						</div>
						<?php if($_SESSION['tipo_viaggio'] == 2) { ?>
							<div style="text-align:center;"><span><b><?=$dizionario['prenota']['andata']?></b></span></div>
						<?php } ?>
						<div>
							<div class="travel-info row">
								<div class="departure col-md-5 col-sm-12">
									<div class="time"><?= $_SESSION['start_time'] ?></div>
									<div class="location"><?= $_SESSION['porto_partenza_nome'] ?> - <?= $_SESSION['Pickup'] ?></div>
								</div>
								<div class="duration col-md-2 col-sm-12">
									<div class="time"><?= $_SESSION['time_diffrence'] ?></div>
									<div class="arrow-container">
										<div class="arrow"></div>
									</div>
								</div>
								<div class="arrival col-md-5 col-sm-12">
									<div class="time"><?= $_SESSION['end_time'] ?></div>
									<div class="location"><?= $_SESSION['porto_destinazione_nome'] ?> - <?= $_SESSION['Dropoff'] ?></div>
								</div>
							</div>
						</div>
						<?php if($_SESSION['tipo_viaggio'] == 2) { ?>
							<div style="text-align:center;"><span><b><?=$dizionario['prenota']['ritorno']?></b></span></div>
							<div>
								<div class="travel-info row">
									<div class="departure col-md-5 col-sm-12">
										<div class="time"><?= $_SESSION['start_timeR'] ?></div>
										<div class="location"><?= $_SESSION['porto_destinazione_nome'] ?> - <?= $_SESSION['PickupR'] ?></div>
									</div>
									<div class="duration col-md-2 col-sm-12">
										<div class="time"><?= $_SESSION['time_diffrenceR'] ?></div>
										<div class="arrow-container">
											<div class="arrow"></div>
										</div>
									</div>
									<div class="arrival col-md-5 col-sm-12">
										<div class="time"><?= $_SESSION['end_timeR'] ?></div>
										<div class="location"><?= $_SESSION['porto_partenza_nome'] ?> - <?= $_SESSION['DropoffR'] ?></div>
									</div>
								</div>
							</div>
						<?php } ?>
						
						<div class="title">
							<span><?=$dizionario['prenota']['passeggeri']?></span>
							<div class="dashes iframe-none"></div>
						</div>
						<div>
							<?php
							foreach ($_SESSION['TipoBigliettoId'] as $key => $value) {
							?>
								<div class="action-title">
									<span><?= $_SESSION['BigliettoTipologiaPax'][$key] . 'x ' . $value ?></span>
									<?php $sql = "SELECT * FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = $key";
										$temp = $db->query_first($sql);
										if($temp['OccupaPosto'] == 1) {
										?>
											<a href="/prenota/2.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>" title="Modifica passeggeri"><img src="/images/edit-primary.png" alt="Modifica passeggeri" /></a> 
										<?php } else { ?>
											<a href="/prenota/6.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>" title="Modifica servizi"><img src="/images/edit-primary.png" alt="Modifica servizi" /></a>  
										<?php } ?>
								</div>
							<?php
							}
							?>
						</div>
						<div class="title">
							<span><?=$dizionario['prenota']['contatti']?> </span>
							<div class="dashes iframe-none"></div>
						</div>
						<div class="mb-32">
							<div class="row-info">
								<div class="label"><?=$dizionario['prenota']['nominativo']?></div>
								<div class="info"><?= $_SESSION['nome'] ?></div>
							</div>
							<div class="row-info">
								<div class="label"><?=$dizionario['prenota']['email']?></div>
								<div class="info"><?= $_SESSION['mail'] ?></div>
							</div>
							<div class="row-info">
								<div class="label"><?=$dizionario['prenota']['recapito_telefonico']?></div>
								<div class="info">+<?= $_SESSION['prefisso'] ?> <?= $_SESSION['tel'] ?></div>
							</div>
						</div>
						<div class="title">
							<span><?=$dizionario['prenota']['metodo_pagamento']?></span>
							<div class="dashes iframe-none"></div>
						</div>
						<div>
							<div class="action-title">
								<span style="text-align: left;">
									<?php if($_SESSION['pagamento'] == 'da_confermare') { ?>
										<b><u><i><?=$dizionario['prenota']['pagamento_da_confermare']?></i></u></b>
									<?php } else { ?> 
										<?php if($_SESSION['pagamento'] == 'stripe') { ?>
											<?= strtoupper($dizionario['coupon']['stripe_carta']) ?>
										<?php } else { ?>
											<?= strtoupper($_SESSION['pagamento']) ?>
										<?php } ?>
										<?php if($_SESSION['pagamento'] == 'paypal' && $paypalMsg) { ?>
												<span class="supplemento-msg" style="color:#c00;font-size:12px;"><?= $paypalMsg ?></span>
										<?php } ?>
										<?php if($_SESSION['pagamento'] == 'stripe' && $stripeMsg) { ?>
											<span class="supplemento-msg" style="color:#c00;font-size:12px;"><?= $stripeMsg ?></span>
										<?php } ?>
									<?php } ?>
								</span>	
								<a href="/prenota/7.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>"><img src="/images/edit-primary.png" alt="" /></a>
							</div>
						</div>
						<div class="title">
							<span><?=$dizionario['prenota']['dettaglio_ricevuta_fattura']?></span>
							<div class="dashes iframe-none"></div>
						</div>
						<div>
							<div class="action-title">
								<span><?= ($_SESSION['scontrinoInvio'] == 1) ? $dizionario['prenota']['fattura'] : $dizionario['prenota']['ricevuta']?></span>
								<a href="/prenota/7.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>"><img src="/images/edit-primary.png" alt="" /></a>
							</div>
						</div>
						<?php if (!empty($_SESSION['coupons'])) { ?>
							<div class="title">
								<span><?=$dizionario['conferma']['codice_coupon']?></span>
								<div class="dashes iframe-none"></div>
							</div>
							<div>
								<div class="action-title">
									<ul style="margin-bottom:0; margin-left: 20px;">
										<?php $totale_sconto = 0; foreach ($_SESSION['coupons'] as $c): $totale_sconto += floatval($c['importo']); ?>
											<li><b><?= htmlspecialchars($c['codice']) ?></b> - <?= number_format($c['importo'], 2, ',', '.') ?>€</li>
										<?php endforeach; ?>
									</ul>
									<div><b><?=$dizionario['conferma']['totale_sconto_coupon']?>: </b> <?= number_format($totale_sconto, 2, ',', '.') ?>€</div>
									<a href="/prenota/7.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>"><img src="/images/edit-primary.png" alt="" /></a>
								</div>
							</div>
						<?php } ?>
					</form>
				<?php } ?>
            </div>


            


            <div class="sticky action-bottombar">
                <div class="info">
                    <?php if(!isset($_SESSION['prenotazione'])) { ?>
						<span><?=$dizionario['prenota']['prezzo_totale']?></span>
						<div class="user">
							<img src="/images/user-primary.png" alt="" />
							<span><?= number_format($_SESSION['totalprice'], 2, ',', '.'); ?>€</span> 
						</div>
					<?php } else if(isset($_SESSION['prenotazione']) && $_SESSION['totalprice'] <= $_SESSION['prenotazione']['TotalePrenotazione']) {  ?>
						<span><?=$dizionario['prenota']['prezzo_totale']?></span>
						<div class="user">
							<img src="./img/user-primary.png" alt="" />
							<span><?= number_format(0, 2, ',', '.'); ?>€</span> 
						</div>
						<span><?=$dizionario['prenota']['no_variazione_modifica']?> <?=$_SESSION['prenotazione']['CodicePrenotazione']?></span>
					<?php } else if(isset($_SESSION['prenotazione']) && $_SESSION['totalprice'] > $_SESSION['prenotazione']['TotalePrenotazione']) { ?>
						<span><?=$dizionario['prenota']['prezzo_totale']?></span>
						<div class="user">
							<img src="./img/user-primary.png" alt="" />
							<span><?= number_format($_SESSION['totalprice'], 2, ',', '.'); ?>€</span> 
						</div>
						<span><?=$dizionario['prenota']['importo_pagato_per']?> <?=$_SESSION['prenotazione']['CodicePrenotazione']?></span>
						<div class="user">
							<img src="./img/user-primary.png" alt="" />
							<span><?= number_format($_SESSION['prenotazione']['TotalePrenotazione'], 2, ',', '.'); ?>€</span> 
						</div>
						<span><?=$dizionario['prenota']['residuo']?></span>
						<div class="user">
							<img src="./img/user-primary.png" alt="" />
							<span><?= number_format($_SESSION['totalprice'] - $_SESSION['prenotazione']['TotalePrenotazione'], 2, ',', '.'); ?>€</span> 
						</div>
					<?php } ?>
                </div>
                <div>

                    <button type="submit" class="btn btn-big btn-primary confirm"><?=$dizionario['prenota']['continua']?></button>

                </div>

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
        function submitForm(event) {
            // Submit the form in a new tab
            var form = document.getElementById('search');
            form.target = '_blank';
            form.submit();

            // Redirect the current page after a delay (e.g., 1 second)
            setTimeout(function() {
                window.location.href = 'index.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>';
            }, 1000); // 1000 milliseconds = 1 second
        }
        $(document).ready(function() {

			$("#inviaTicketEmail").click(function() {
				var prenotazioneId = $('#prenotazioneId').val();
				inviaTicketEmail(prenotazioneId);
			});

            $('.prenotazioneId').val(localStorage.getItem('prenotazioneId'));
            $('.corsaId').val(localStorage.getItem('corsaId'));
            

            // BigliettoTipologiaPax
            var passangersData = <?= json_encode($_SESSION['BigliettoTipologiaPax']) ?>;
            var TipoBigliettoIdData = <?= json_encode($_SESSION['TipoBigliettoId']) ?>;
			$('.confirm').on('click', function() {
				$("#new-ticket").submit();
			});

            $('#back').click(function() {
				<?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
					window.location.href = "/index.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
				<?php } else { ?>
					window.location = "/prenota/7.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
				<?php } ?>
            });



        });
    </script>
</html>
