<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include($basepath.'/main_include.php');

$PageTitle = $dizionario['ticket']['titolo'];
$PageDescription = $dizionario['ticket']['descr'];
$PageKeywords = $dizionario['ticket']['key'];

$page_title = "Home";

$LinkActive = 1;

//inizializzo DB
$db = new Database();
$db->connect();
getGestoreByCode();

if($_SESSION['gestore'] != -1) {
	$codiceAzienda = $_SESSION['gestore']['CodiceAzienda'];
	$codiceAziendaNome = $_SESSION['gestore']['RagioneSociale'];
} else {
	$codiceAzienda = '';
	$codiceAziendaNome = 'Bertoldi Boats';
}
//recupero elenco prefissi telefono
$prefissoObj = new PrefissoTelefono($db);
$arr_prefisso = $prefissoObj->getAllForSelect();

//verifico se l'utente è connesso ed inizializzo i campi dai valori dell'utente connesso
if(isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];
	
	if(!isset($_SESSION['nome']) || !isset($_SESSION['tel']) || !isset($_SESSION['mail']) || !isset($_SESSION['prefisso'])) {
		$_SESSION['nome'] = $user['CognomeRagioneSociale']." ".$user['Nome'];
		$_SESSION['mail'] = $user['Email'];
		$stringaTemp = ltrim($user['Telefono'], '+');
		foreach ($arr_prefisso as $prefisso) {
			if (strpos($stringaTemp, $prefisso["Prefisso"]) === 0) {
				$_SESSION['prefisso'] = $prefisso['Prefisso'];
				$lunghezzaPrefisso = strlen($prefisso["Prefisso"]);
				$_SESSION['tel'] = substr($stringaTemp, $lunghezzaPrefisso);
				break;
			}
		}
	}
} else {
	$user = null;
}



if(!isset($_SESSION['tipo_tour'])) {
	// Effettua il redirect alla pagina index.php
	header("Location: /index.php");
	exit; // Termina lo script per evitare l'esecuzione di ulteriori istruzioni
} 

	$DataPartenza = date('Y-m-d');
    $datacorrente = Date('d/m/Y');
    $CorsaId = 0; //$arr_corse[0]['CorsaId'];
    if (isset($_POST['CorsaId']))
        $CorsaId = $_POST['CorsaId'];

    if (isset($_POST['DataPartenza'])) {
        $dt = new DT();
        $post_dal = $_POST['DataPartenza'];
        $DataPartenza = $_POST['DataPartenza'];
        $dateTime = new DateTime($DataPartenza);
        $datacorrente = $dateTime->format('d/m/Y');
    }

	if (isset($_SESSION['PrenotazioneId']) && !isset($_SESSION['nome'])) { 
		$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$_SESSION['PrenotazioneId'];
		$rowTemp = $db->query_first($sql);
		$_SESSION['nome'] = $rowTemp['ClienteNome'];
		$_SESSION['tel'] = $rowTemp['ClienteCellulare'];
		$_SESSION['mail'] = $rowTemp['ClienteEmail'];
		$_SESSION['prefisso'] = $rowTemp['ClienteCellularePrefisso'];
		$_SESSION['prenotazione'] = $rowTemp;
		$sql = "SELECT * FROM `RT_PrenotazioneMovimento` WHERE PrenotazioneId = ".$_SESSION['PrenotazioneId']." AND TipoMovimento = 'I'";
		$rowTemp = $db->query_first($sql);
		if($rowTemp['PagamentoTipoId'] == 1) {
			$_SESSION['pagamento'] = 'paypal';
		} else { 
			$_SESSION['pagamento'] = 'stripe';
		}
	}

	$_SESSION['totalprice'] = $_SESSION['totalprice'] + (isset($_SESSION['coupon_importo']) ? $_SESSION['coupon_importo'] : 0);
	$_SESSION['coupon_importo'] = '';
	$_SESSION['coupon'] = '';
	$privacyError = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Store the form input values in the session
        $_SESSION['nome'] = isset($_POST['nome']) ? $_POST['nome'] : '';
        $_SESSION['tel'] = isset($_POST['tel']) ? $_POST['tel'] : '';
        $_SESSION['mail'] = isset($_POST['mail']) ? $_POST['mail'] : '';
        $_SESSION['pagamento'] = isset($_POST['pagamento']) ? $_POST['pagamento'] : '';
		$_SESSION['prefisso'] = isset($_POST['prefisso']) ? $_POST['prefisso'] : '';
		$_SESSION['scontrinoInvio'] = isset($_POST['scontrinoInvio']) ? $_POST['scontrinoInvio'] : '';
		$_SESSION['coupons'] = json_decode($_POST['coupons'], true) ?: [];
		$totale_sconto = 0;
		foreach ($_SESSION['coupons'] as $c) $totale_sconto += floatval($c['importo']);
		$_SESSION['totalprice'] = $_SESSION['totalprice'] - $totale_sconto;

		$consenso_privacy = isset($_POST['consenso_privacy']) ? 1 : 0;
		$consenso_marketing = isset($_POST['consenso_marketing']) ? 1 : 0;
		$consenso_profilazione = isset($_POST['consenso_profilazione']) ? 1 : 0;
		$_SESSION['consenso_privacy'] = $consenso_privacy;
		$_SESSION['consenso_marketing'] = $consenso_marketing;
		$_SESSION['consenso_profilazione'] = $consenso_profilazione;
		if ($consenso_privacy !== 1) {
			$privacyError = $dizionario['prenota']['consenso_privacy_required_error'];
		} else {
			header("Location: /prenota/8.php".(($sessionId != '') ? '?session_id='.$sessionId : ''));
			exit();
		}
    }

    $tipoTour = $_SESSION['tipo_tour'];
	$tipo_viaggio = $_SESSION['tipo_viaggio'];
    $seleziona_lesperienza = $_SESSION['seleziona_lesperienza'];
	$orarioPartenza = $_SESSION['start_time'];

    $sqlServiziExtra = "SELECT t.*, s.Prezzo, s.Limite FROM RT_TipologiaBiglietto t LEFT JOIN RT_ListinoServizi s on s.BigliettoId = t.TipologiaBigliettoId WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = 1 AND t.OccupaPosto = 0 ORDER BY t.TipologiaBigliettoPeso";
    $arrServiziExtra = $db->fetch_array($sqlServiziExtra);

	$totalPrice = $_SESSION['totalprice'];

	$daConfermare = (isset($_SESSION['da_confermare']) ? $_SESSION['da_confermare'] : false) || (isset($_SESSION['da_confermareR']) ? $_SESSION['da_confermareR'] : false);
	
	//imposto valore di default del prefisso su Italia
	if(!isset($_SESSION['prefisso'])) {
		$_SESSION['prefisso'] = 39;
	}

	if(isset($_SESSION['gestore']) && $_SESSION['gestore'] != -1) {
		$gestore = $_SESSION['gestore']['GestoreId'];
	} else {
		$gestore = -1;
	}


	// Recupera info pagamento Paypal (id 1) e Stripe (id 22)
	$sqlPaypal = "SELECT * FROM RT_PagamentoTipo WHERE PagamentoTipoId = 5";
	$paypalInfo = $db->query_first($sqlPaypal);

	$sqlStripe = "SELECT * FROM RT_PagamentoTipo WHERE PagamentoTipoId = 22";
	$stripeInfo = $db->query_first($sqlStripe);

	// Calcola supplementi
	$paypalSupplemento = 0;
	$paypalMsg = '';
	if ($paypalInfo['SupplementoFisso'] != 0 || $paypalInfo['SupplementoPercentuale'] != 0) {
		$paypalSupplemento = $totalPrice * ($paypalInfo['SupplementoPercentuale'] / 100) + $paypalInfo['SupplementoFisso'];
		$paypalMsg = " + " . number_format($paypalSupplemento, 2, ',', '.') . "€ " . $dizionario['prenota']['supplemento_pagamento'];
	}

	$stripeSupplemento = 0;
	$stripeMsg = '';
	if ($stripeInfo['SupplementoFisso'] != 0 || $stripeInfo['SupplementoPercentuale'] != 0) {
		$stripeSupplemento = $totalPrice * ($stripeInfo['SupplementoPercentuale'] / 100) + $stripeInfo['SupplementoFisso'];
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

<body class="main-bg" id="nuova-prenotazione-3-1">

	<!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>  

	
    <!-- Wrapper
	================================================== -->
	<div id="page" class="hfeed site fullwidth">
    	
        
        <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" class="new-ticket">

            <div class="main-container">
                <div class="content">
					<div style="margin-bottom:10px;" class="benvenuto-plugin">
						<h2><?=$dizionario['prenota']['prenota_il_tuo_tour']?></h2>
					<p><?=$dizionario['prenota']['segui_passaggi']?></p>
					</div>
                    <div class="info-bar">
                        <button type="button" class="btn btn-rounded btn-primary" id="back">
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
						<div class="title iframe-none">
							<span><?=$dizionario['prenota']['informazioni_di_contatto']?></span>
							<div class="dashes"></div>
						</div>
						<div>
							<div class="form-group">
								<label for="nome"><?=$dizionario['prenota']['inserisci_nome_cognome']?></label>
								<input id="nome" type="text" name="nome" value="<?php echo isset($_SESSION['nome']) ? $_SESSION['nome'] : ''; ?>" required />
							</div>
							<div class="form-group">
								<label><?=$dizionario['prenota']['recapito_telefonico']?></label>
								<div class="row">
								<div class="col-md-3">
									<label for="prefisso"><?=$dizionario['prenota']['prefisso']?></label>
									<select name="prefisso" id="prefisso" required>
										<option selected="" value="">-</option>
										<?php foreach($arr_prefisso as $pref) {
											if(isset($_SESSION['prefisso']) && $_SESSION['prefisso'] == $pref['Prefisso']) {
												echo "<option selected='selected' value='".$pref['Prefisso']."'>".$pref['Descrizione']."</option>";
											} else {
												echo "<option value='".$pref['Prefisso']."'>".$pref['Descrizione']."</option>";
											}
										} ?>
									</select>
								</div>
								<div class="col-md-9">
									<label for="tel"><?=$dizionario['prenota']['numero_telefono']?></label>
									<input id="tel" type="text" name="tel" value="<?php echo isset($_SESSION['tel']) ? $_SESSION['tel'] : ''; ?>" required />
								</div>
								</div>
							</div>
							<div class="form-group">
								<label for="mail"><?=$dizionario['prenota']['email']?></label>
								<input id="mail" type="text" name="mail" value="<?php echo isset($_SESSION['mail']) ? $_SESSION['mail'] : ''; ?>" required />
							</div>
						</div>
						<div class="title iframe-none">
							<span><?=$dizionario['prenota']['metodo_pagamento']?></span>
							<div class="dashes"></div>
						</div>
						<?php if(!$daConfermare) { ?>
							<div>
								<div class="form-group">
									<label for="mail iframe-show"><?=$dizionario['prenota']['metodo_pagamento']?></label>
									<div class="checkbox-control">
										<label for="paypal">Paypal
											<?php if($paypalMsg) { ?>
												<span class="supplemento-msg" style="color:#c00;font-size:12px;"><?= $paypalMsg ?></span>
											<?php } ?>
										</label>
										<input type="radio" name="pagamento" value="paypal" id="paypal" <?php echo (isset($_SESSION['pagamento']) && $_SESSION['pagamento'] == 'paypal') ? 'checked' : ''; ?> required />
									</div>
									<div class="checkbox-control">
										<label for="stripe"><?= $dizionario['coupon']['stripe_carta'] ?>
											<?php if($stripeMsg) { ?>
												<span class="supplemento-msg" style="color:#c00;font-size:12px;"><?= $stripeMsg ?></span>
											<?php } ?>
										</label>
										<input type="radio" name="pagamento" value="stripe" id="stripe" <?php echo (isset($_SESSION['pagamento']) && $_SESSION['pagamento'] == 'stripe') ? 'checked' : ''; ?> required />
									</div>
								</div>
							</div>
						<?php } else { ?>
							<div>
								<div class="form-group">
									<label for="mail iframe-show"><?=$dizionario['prenota']['metodo_pagamento']?></label>
									<input type="hidden" name="pagamento" value="da_confermare" id="da_confermare">
									<b class="note-pagemento"><u><i><?=$dizionario['prenota']['pagamento_da_confermare']?></i></u></b>
								</div>
							</div>
						<?php } ?>
						<div class="title iframe-none">
							<span><?=$dizionario['conferma']['inserisci_coupon']?></span>
							<div class="dashes"></div>
						</div>
						<div>
						<input type="hidden" id="comunePartenzaId" value="<?= $_SESSION['porto_partenza'] ?>">
						<input type="hidden" id="comuneArrivoId" value="<?= $_SESSION['porto_destinazione'] ?>">
						<input type="hidden" id="dataAndata" value="<?= $DataPartenza ?>">
						<input type="hidden" id="dataRitorno" value="<?= $DataPartenza ?>">
							
						
						
							<div class="form-group">
								<label for="mail iframe-show"><?=$dizionario['conferma']['inserisci_coupon_input']?></label>
								<div class="row">
									<div class="col-md-3">
										<label for="codice_generale"><?=$dizionario['conferma']['codice_generale']?></label>
										<input name="codice_generale" id="codice_generale" value="<?=$codiceAzienda?>"  type="text" class="form-control" />
										<div class="codice_agenzia_coupon"></div>
									</div>
									<div class="col-md-3" style="padding: 15px;">
										<button type="button" id="applica_codice_generale" class="btn btn-primary" style="margin-left:5px;"><?=$dizionario['conferma']['applica_codice']?></button>
									</div>
									<div class="col-md-3">
										<label for="codice_generale_risultato"><?=$dizionario['conferma']['codice_agenzia_risultato']?></label>
										<br>
										<span id="codice_generale_risultato" style="font-size: 15pt; font-weight: bold;"><?=$codiceAziendaNome?></span>
										<?php if($_SESSION['gestore'] != -1) { ?>
											<a href="#" id="disattiva_codice_agenzia" title="<?=$dizionario['conferma']['disattiva_codice_agenzia']?>" style="margin-left:8px;color:#c00;font-weight:bold;font-size:18px;text-decoration:none;">&times;</a>
										<?php } ?>
										<br>
										<span id="codice_generale_risultato_dettagli" style="font-size: 12pt;"></span>
									</div>
									<div class="col-md-3">
										<label for="valore_coupon"><?=$dizionario['conferma']['valore_coupon']?></label>
										<br>
										<div class="lista_coupon_applicati" style="font-size: 15pt; font-weight: bold;"></div>
										<input type="hidden" id="coupons" name="coupons" value=''>
									</div>
								</div>
							</div>
						</div>
						
						<div class="title iframe-none">
							<span><?=$dizionario['prenota']['ricevuta_fattura']?></span>
							<div class="dashes"></div>
						</div>
						<div>
							<div class="form-group">
								<label for="scontrinoInvio"><?=$dizionario['prenota']['dettaglio_ricevuta_fattura']?></label>
								<div class="row">
									<div class="col-md-3">
										<select name="scontrinoInvio" id="scontrinoInvio" required>
											<option selected="" value="0"><?=$dizionario['prenota']['ricevuta']?></option>
											<!-- <option value="1">FATTURA</option> -->
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="title iframe-none">
							<span><?=$dizionario['prenota']['consensi_a_procedere']?></span>
							<div class="dashes"></div>
						</div>
						<div id="consensi">
							<div class="form-group">
								<div class="checkbox-control">
									<input type="checkbox" name="consenso_privacy" id="consenso_privacy" value="1" <?php echo (isset($_SESSION['consenso_privacy']) && $_SESSION['consenso_privacy']==1) ? 'checked' : ''; ?> required />
									<label for="consenso_privacy"><?= $dizionario['prenota']['consenso_privacy_label'] ?> <a href="<?= $dizionario['Privacy-Url'] ?>" target="_blank"><?= $dizionario['Privacy-Url'] ?></a></label>
									<?php if (!empty($privacyError)) { ?>
										<div style="color:#c00;font-size:12px;"><?= $privacyError ?></div>
									<?php } ?>
								</div>
								<div class="checkbox-control">
									<input type="checkbox" name="consenso_marketing" id="consenso_marketing" value="1" <?php echo (isset($_SESSION['consenso_marketing']) && $_SESSION['consenso_marketing']==1) ? 'checked' : ''; ?> />
									<label for="consenso_marketing"><?= $dizionario['prenota']['consenso_marketing_label'] ?></label>
								</div>
								<div class="checkbox-control">
									<input type="checkbox" name="consenso_profilazione" id="consenso_profilazione" value="1" <?php echo (isset($_SESSION['consenso_profilazione']) && $_SESSION['consenso_profilazione']==1) ? 'checked' : ''; ?> />
									<label for="consenso_profilazione"><?= $dizionario['prenota']['consenso_profilazione_label'] ?></label>
								</div>
							</div>
						</div>
					<?php } ?>
                </div>
            </div>

            <div class="sticky action-bottombar">
                <div class="info">
				<input type="hidden" id="prezzo_totale_iniziale" name="prezzo_totale_iniziale" value="<?php echo $totalPrice; ?>" />
					<?php if(!isset($_SESSION['prenotazione'])) { ?>
						<span><?=$dizionario['prenota']['prezzo_totale']?></span>
						<div class="user">
							<img src="/images/user-primary.png" alt="" />
							<span id="prezzo_totale_formattato"><?= number_format($totalPrice, 2, ',', '.'); ?>€</span>
							<input type="hidden" id="prezzo_totale" name="prezzo_totale" value="<?php echo $totalPrice; ?>" />
						</div>
					<?php } else if(isset($_SESSION['prenotazione']) && $totalPrice <= $_SESSION['prenotazione']['TotalePrenotazione']) {  ?>
						<span><?=$dizionario['prenota']['prezzo_totale']?></span>
						<div class="user">
							<img src="/images/user-primary.png" alt="" />
							<span id="prezzo_totale_formattato"><?= number_format(0, 2, ',', '.'); ?>€</span> 
							<input type="hidden" id="prezzo_totale" name="prezzo_totale" value="0" />
						</div>
						<span><?=$dizionario['prenota']['no_variazione_modifica']?> <?=$_SESSION['prenotazione']['CodicePrenotazione']?></span>
					<?php } else if(isset($_SESSION['prenotazione']) && $totalPrice > $_SESSION['prenotazione']['TotalePrenotazione']) { ?>
						<span><?=$dizionario['prenota']['prezzo_totale']?></span>
						<div class="user">
							<img src="/images/user-primary.png" alt="" />
							<span><?= number_format($totalPrice, 2, ',', '.'); ?>€</span> 
						</div>
						<span><?=$dizionario['prenota']['importo_pagato_per']?> <?=$_SESSION['prenotazione']['CodicePrenotazione']?></span>
						<div class="user">
							<img src="/images/user-primary.png" alt="" />
							<span><?= number_format($_SESSION['prenotazione']['TotalePrenotazione'], 2, ',', '.'); ?>€</span> 
						</div>
						<span><?=$dizionario['prenota']['residuo']?></span>
						<div class="user">
							<img src="/images/user-primary.png" alt="" />
							<span id="prezzo_totale_formattato"><?= number_format($totalPrice - $_SESSION['prenotazione']['TotalePrenotazione'], 2, ',', '.'); ?>€</span> 
						</div>
						<input type="hidden" id="prezzo_totale" name="prezzo_totale" value="<?php echo $totalPrice - $_SESSION['prenotazione']['TotalePrenotazione']; ?>" />
					<?php } ?>
                </div>
                <div>
                    <input type="submit" value="<?=$dizionario['prenota']['continua']?>" class="btn btn-big btn-primary continua-ticket" id="continua" />
                </div>
            </div>
        </form>
        
        
        
            

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

		var coupons = [];
		<?php if(!empty($_SESSION['coupons'])): ?>
			coupons = <?=json_encode($_SESSION['coupons'])?>;
		<?php endif; ?>

		function aggiornaListaCoupon() {
			var html = '';
			var totaleSconto = 0;
			coupons.forEach(function(c, idx) {
				totaleSconto += parseFloat(c.importo);
				html += '<div class="coupon_item">'
					+ '<span class="codice_coupon_attivo">'+c.codice+'</span> - <span class="valore_coupon_num">'+parseFloat(c.importo).toFixed(2).replace('.', ',')+'&euro;</span>'
					+ ' <a href="#" class="rimuovi_coupon" data-idx="'+idx+'" style="color:#c00;font-weight:bold;font-size:18px;text-decoration:none;margin-left:8px;">&times;</a>'
					+ '</div>';
			});
			if(html === '') html = '-';
			$('.lista_coupon_applicati').html(html);
			$('#coupons').val(JSON.stringify(coupons));
			// Aggiorna totale
			var totaleIniziale = parseFloat($('#prezzo_totale_iniziale').val());
			var modificaImporto = parseFloat($('#modificaImporto').val());
			if(isNaN(modificaImporto)) modificaImporto = 0;
			var modificaPenale = parseFloat($('#modificaPenale').val());
			if(isNaN(modificaPenale)) modificaPenale = 0;
			var residuo = totaleIniziale - totaleSconto - modificaImporto + modificaPenale;
			if(residuo < 0) residuo = 0;
			$("#prezzo_totale_formattato").html(parseFloat(residuo).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.') + "€");
			$("#prezzo_totale").val(parseFloat(residuo));
		}

		// Inizializza lista se già presenti
		aggiornaListaCoupon();
		
		$('#back').click(function() {
			<?php if($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
				window.location.href = "/index.php";
			<?php } else { ?>
				
				<?php if($tipoTour == 0) { ?>
					<?php if($tipo_viaggio == 2) { ?>
						window.location = "/prenota/5.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
					<?php } else { ?>
						window.location = "/prenota/4.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
					<?php } ?>
				<?php } else { ?>
					window.location = "/prenota/6.php<?= ($sessionId != '') ? '?session_id='.$sessionId : ''?>";
				<?php } ?>

			<?php } ?>
		});
		
		
		var form = $('#contactForm');
        var telInput = $('#tel');
        var mailInput = $('#mail');
        var telError = $('#telError');
        var mailError = $('#mailError');

        telInput.on('input', function() {
			var telValue = telInput.val();
			var phonePattern = /^\+?(?:[0-9] ?){6,14}[0-9]$/;

			if (!phonePattern.test(telValue)) {
                telInput[0].setCustomValidity('Inserisci un numero di telefono valido, compreso il prefisso internazionale (es. +393461122333).');
            } else {
                telInput[0].setCustomValidity('');
            }
        });

        mailInput.on('input', function() {
			var mailValue = mailInput.val();
			var mailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			
           if (!mailPattern.test(mailValue)) {
                mailInput[0].setCustomValidity('Inserisci un indirizzo email valido.');
            } else {
                mailInput[0].setCustomValidity('');
            }
        });

        form.on('submit', function(event) {
            var isValid = true;
			var phonePattern = /^\+(?:[0-9] ?){6,14}[0-9]$/;
			var telValue = telInput.val();

             if (!phonePattern.test(telValue)) {
                telInput[0].setCustomValidity('Inserisci un numero di telefono valido, compreso il prefisso internazionale (es. +393460636551).');
                isValid = false;
             }

			var mailValue = mailInput.val();
			var mailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			if (!mailPattern.test(mailValue)) {
                mailError.text('Inserisci un indirizzo email valido.');
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
            }
        });

		// Gestione campo codice_generale (agenzia/coupon) con pulsante
		$('#applica_codice_generale').click(function(){
			var codiceVal = $('#codice_generale').val();
			if(!codiceVal) return;
			// Verifica se già inserito
			if(coupons.some(function(c){return c.codice.toLowerCase() === codiceVal.toLowerCase();})) {
				alert('Coupon già inserito');
				return;
			}
			// Prima verifica come codice agenzia
			$.ajax({
				type: "POST",
				url: "/gestione_utente.php",
				data: { action: 'checkCodiceAgenzia', codiceAgenzia: codiceVal },
				dataType: "json",
				success: function(dataAgenzia){
					var currentUrl = window.location.href.split('?')[0];
					if(dataAgenzia.result == true && dataAgenzia.agenzia) {
						window.location.href = currentUrl + "?code=" + codiceVal;
					} else {
						// Se non è codice agenzia, prova come coupon
						var formData = {
							action: 'checkCoupon',
							codiceCoupon: codiceVal,
							comunePartenzaId: $('#comunePartenzaId').val(),
							comuneArrivoId: $('#comuneArrivoId').val(),
							dataAndata: $('#dataAndata').val(),
							dataRitorno: $('#dataRitorno').val(),
							totale: $('#prezzo_totale_iniziale').val(),
							lineaId: <?= $seleziona_lesperienza ?>,
							gestoreId: <?= $gestore ?>,
							orarioCorsaAndata: '<?= $orarioPartenza ?>',
							orarioCorsaRitorno: '<?= $orarioPartenza ?>'
						};
						$.ajax({
							type: "POST",
							url: "/gestione_utente.php",
							data: formData,
							dataType: "json",
							success: function(data){
								if(data.result == true){
									var importo;
									if($.type(data.importo) === "string"){
										importo = parseFloat(data.importo.replace(',','.'));
									} else {
										importo = parseFloat(data.importo);
									}
									var percentuale = data.percentuale;
									if(importo <= 0){
										importo = parseFloat($('#prezzo_totale_iniziale').val())*percentuale/100;
										importo = Math.floor(importo*10)/10; 
									}
									var v = parseFloat(Math.round(importo * 100) / 100).toFixed(2);
									coupons.push({codice: codiceVal, importo: v});
									aggiornaListaCoupon();
									$('#codice_generale').val('');
								} else {
									var html = "Ops, Coupon non valido!";
									if(typeof data.andata !== "undefined" && data.andata != 'OK') {
										html += " Andata: "+data.andata;
									}
									//if(typeof data.ritorno !== "undefined" &&  data.ritorno != 'OK' && data.ritorno != 'NO') {
									//	html += " Ritorno: "+data.ritorno;
									//}
									alert(html);
								}
							},
							failure: function(errMsg) {
								alert(errMsg);
							}
						});
					}
				},
				failure: function(errMsg) {
					alert(errMsg);
				}
			});
		});

		// Rimuovi coupon
		$(document).delegate('.rimuovi_coupon', 'click', function(e){
			e.preventDefault();
			var idx = $(this).data('idx');
			coupons.splice(idx,1);
			aggiornaListaCoupon();
		});

		// Disattiva codice agenzia (X vicino a codice_generale_risultato)
		$('#disattiva_codice_agenzia').click(function(e){
			e.preventDefault();
			var currentUrl = window.location.href.split('?')[0]; // Remove query parameters
			window.location.href = currentUrl; // Reload without the parameter
		});

		$('form.new-ticket').submit(function(e) {
    // Invio evento a dataLayer
    if (typeof dataLayer !== 'undefined') {
        dataLayer.push({
            event: "begin_checkout",
            ecommerce: {
                currency: "EUR",
                value: parseFloat($('#prezzo_totale').val()),
                <?php if(isset($_SESSION['coupon']) && $_SESSION['coupon'] != '') { ?>
                coupon: "<?= addslashes($_SESSION['coupon']) ?>",
                <?php } ?>
                items: [{
                    item_id: "BOAT_<?= $_SESSION['tipo_tour'] == 1 ? 'PRIVATE' : 'GROUP' ?>_<?= $_SESSION['seleziona_lesperienza'] ?>",
                    item_name: "<?php 
                        $sql = "SELECT LineaNome FROM RT_Linea WHERE LineaId = " . $_SESSION['seleziona_lesperienza'];
                        $linea = $db->query_first($sql);
                        echo addslashes($linea['LineaNome']);
                    ?>",
                    affiliation: "Bertoldi Boats",
                    <?php if(isset($_SESSION['coupon']) && $_SESSION['coupon'] != '') { ?>
                    coupon: "<?= addslashes($_SESSION['coupon']) ?>",
                    discount: parseFloat($('#importo_coupon').val()),
                    <?php } ?>
                    item_brand: "Bertoldi Boats",
                    item_category: "Tour Barca",
                    item_category2: "<?= $_SESSION['tipo_tour'] == 1 ? 'Tour Privato' : 'Tour Gruppo' ?>",
                    item_category3: "<?php
                        if($_SESSION['tipo_viaggio'] == 1) {
                            echo 'Solo Andata';
                        } else if($_SESSION['tipo_viaggio'] == 2) {
                            echo 'Andata e Ritorno';
                        } else {
                            echo 'Tour';
                        }
                    ?>",
                    item_variant: "<?= date('d/m/Y', strtotime($_SESSION['ticket_date'])) ?>",
                    location_id: "lago_garda",
                    price: parseFloat($('#prezzo_totale_iniziale').val()),
                    quantity: <?= isset($_SESSION['totalpersone']) ? intval($_SESSION['totalpersone']) : 1 ?>,
                    custom_parameters: {
                        tour_date: "<?= $_SESSION['ticket_date'] ?>",
                        tour_type: "<?= $_SESSION['tipo_tour'] == 1 ? 'private' : 'group' ?>",
                        travel_type: "<?= $_SESSION['tipo_viaggio'] ?>",
                        porto_partenza: "<?= $_SESSION['porto_partenza'] ?>",
                        porto_destinazione: "<?= $_SESSION['porto_destinazione'] ?>",
                        passengers: <?= isset($_SESSION['totalpersone']) ? intval($_SESSION['totalpersone']) : 1 ?>,
                        <?php if(isset($_SESSION['gestore']) && $_SESSION['gestore'] != -1) { ?>
                        agency_code: "<?= addslashes($_SESSION['gestore']['CodiceAzienda']) ?>",
                        agency_name: "<?= addslashes($_SESSION['gestore']['RagioneSociale']) ?>"
                        <?php } ?>
                    }
                }]
            }
        });
    }
});
	});
</script>
</html>
