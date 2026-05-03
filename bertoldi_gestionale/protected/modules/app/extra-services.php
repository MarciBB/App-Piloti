<?php
// Imposta il percorso base e include i file necessari
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include_app.php");

// Inizializzazione configurazione e percorsi
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

// Includi le classi necessarie
$errors = new Errors();
include_once($classespath_ . "class.Form.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "/class.TipologiaBus.php");
include_once($classespath_ . "class.DT.php");
include_once($classespath_ . "Graph/class.LineaGraph.php");
include_once($classespath_ . "Graph/class.GraphUtil.php");
include_once($classespath_ . "class.Comune.php");
include_once($classespath_ . "class.Tratta.php");
include_once($classespath_ . "class.PrenotazioneDettaglio.php");
include_once($classespath_ . "class.Fermata.php");
include_once($classespath_ . "class.GestioneOttimizzataFlotta.php");
include_once($classespath_ . "class.Autisti.php");
include_once($classespath_ . "class.Flotta.php");
include_once($classespath_ . "class.PreparazioneBusAutista.php");
include_once($classespath_ . "class.GestioneOttimizzataModifiche.php");
include_once($classespath_ . "class.Prenotazione.php");

// Abilita la visualizzazione degli errori
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$ModuloId = 1;
global $db, $autista, $user;
$db = new Database();
$db->connect();

// Controllo login autista
if (!isset($_SESSION['autista'])) {
	header('Location: ./login.php');
}
$autista = $_SESSION['autista'];
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');

// Funzione per la creazione di un nuovo ticket
function nuovaTicket()
{
	if (!isset($_SESSION['tipo_tour'])) {
		// Redirect se la sessione non contiene tipo_tour
		header("Location: ./index.php");
		exit;
	}
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<!-- Meta e risorse -->
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Bertoldi Boats - Nuovo Ticket</title>
		<link rel="manifest" href="./manifest.json">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="./css/new-ticket.css" />
		<link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/js/jquery.min.js"></script>
		<script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
		<script type="text/javascript" src="/js/jquery.maskedinput-1.1.4.js"></script>
		<script type="text/javascript" src="/js/menu_hover.js"></script>
		<script type="text/javascript" language="javascript" src="/js/dialogbox.js?v=3"></script>
		<script type="text/javascript" language="javascript" src="/js/validate/jquery.validate.js"></script>
		<script type="text/javascript" language="javascript" src="/js/jquery.form.track.changes.js"></script>
		<script type="text/javascript" language="javascript" src="/js/ui.multiselect.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.1.10/vue.min.js"></script>
		<script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
		<!-- Bootstrap CSS e JS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
		<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
		<script src="https://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
		<title>Bertoldi Boats - Nuovo Ticket</title>
	</head>
	<?php
	// Inizializzazione variabili e oggetti
	$codiceControllo = 'gC_6XHrZvC9$avW!';
	global $user, $HtmlCommon, $dizionario, $autista;
	$db = new Database();
	$db->connect();

	// Simulazione utente amministratore
	$user = new Operatore();
	$user->OperatoreId = 1;
	$user->OdcId = 1;
	$user->IsAdmin = 1;
	$user->GestoreId = 1;

	// Se il tipo tour è 0, resetta i servizi extra e vai avanti
	if ($_SESSION['tipo_tour'] == 0) {
		$_SESSION['extra_services_price'] = array();
		$_SESSION['totalprice'] = $_SESSION['total_price'];
		header("Location: ./passenger-data.php");
		exit;
	}

	// Recupera dati da request o sessione
	if (isset($_REQUEST['DataPartenza'])) {
		$DataPartenza = $_REQUEST['DataPartenza'];
	}
	if (isset($_REQUEST['CorsaId'])) {
		$CorsaId = $_REQUEST['CorsaId'];
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

	// Carica i dati dei passeggeri se necessario
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

	// Gestione POST: aggiorna i servizi extra e i passeggeri
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		foreach ($_POST['extra_services_price'] as $key => $value) {
			if ($value > 0) {
				$_SESSION['extra_services_price'][$key] = $value;
			} else {
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
		if (isset($_SESSION['total_priceR'])) {
			$_SESSION['totalprice'] += $_SESSION['total_priceR'];
		}
	}

	// Query per i servizi extra disponibili in base all'esperienza selezionata
	if ($seleziona_lesperienza <> 14) {
		$sqlServiziExtra = "SELECT t.*, s.Prezzo, s.Limite, s.LimiteMin, s.LimitePerNumPassegeri 
							FROM RT_TipologiaBiglietto t 
							LEFT JOIN RT_ListinoServizi s on s.BigliettoId = t.TipologiaBigliettoId 
							LEFT JOIN RT_ValiditaBigliettoDettaglio vb ON t.TipologiaBigliettoId = vb.BigliettoId
							LEFT JOIN RT_ValiditaBiglietto v ON v.ValiditaBigliettoId = vb.ValiditaBigliettoId
							WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = 1 AND t.OccupaPosto = 0 AND v.CorsaId = $CorsaId
							AND v.Dal <= '$DataPartenza' AND v.Al >= '$DataPartenza'
							ORDER BY t.TipologiaBigliettoPeso";
	} else {
		$sqlServiziExtra = "SELECT t.*, s.Prezzo, s.Limite, s.LimiteMin, s.LimitePerNumPassegeri 
							FROM RT_TipologiaBiglietto t  
							LEFT JOIN RT_ListinoServizi s on s.BigliettoId = t.TipologiaBigliettoId 
							WHERE t.Stato = 1 AND t.Cancella = 0 AND t.TipoTour = 1 AND t.OccupaPosto = 0 
							ORDER BY t.TipologiaBigliettoPeso";
	}
	$arrServiziExtra = $db->fetch_array($sqlServiziExtra);

	// Calcolo delle ore massime di sosta disponibili
	$orarioPartenzaTour = $_SESSION['start_time'];
	$orarioArrivoTour = $_SESSION['end_time'];
	$time1 = new DateTime($orarioArrivoTour);
	$time2 = new DateTime(Config::$orarioMaxSosta);
	$differenza = $time2->diff($time1);
	$oreMaxSosta = $differenza->h;
	?>

	<body class="main-bg" id="nuova-prenotazione">
		<div id="top-menu">
			<a href="/protected/modules/app/">
				<img src="./img/logo.png" class="logo" />
			</a>
			<button class="btn btn-outline" id="logout">
				<span class="nowhitespace">LOG OUT</span>
				<svg height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
				</svg>
			</button>
		</div>

		<div class="main-container">
			<div class="content">
				<div class="info-bar">
					<button class="btn btn-rounded btn-primary" id="back">
						<svg height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
						</svg>
					</button>
					<div class="info">
						<?php if (isset($_POST['PrenotazioneId']) || isset($_SESSION['PrenotazioneId'])) { ?>
							<span>Modifica Ticket</span>
						<?php } else { ?>
							<span>Nuovo Ticket</span>
						<?php } ?>
						<div class="date"><?= date('M d, Y', strtotime($_SESSION['ticket_date'])) ?></div>
					</div>
				</div>
				<?php if ($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
					<!-- Messaggio se la data del biglietto è già trascorsa -->
					<div class="form-container" style="padding:20px 0px;">
						<div class="available-item" style="text-align:center;">
							Non &egrave; possibile modificare il seguente biglietto. La data del tour del biglietto &egrave; gi&agrave; trascorsa.<br>
							Torna alla <a style="display:contents" href="./index.php">home</a> per altre operazioni.
						</div>
					</div>
				<?php } else { ?>
					<div class="form-container">
						<div class="card-cta">
							<p>Compila i campi e conferma la prenotazione!</p>
						</div>
						<form action="" method="post" class="position-relative" id="ticket_form">
							<div class="form-group form-icon">
								<label for="data">Servizi</label>
								<?php
								$total = 0;
								if (count($arrServiziExtra) > 0) :
									foreach ($arrServiziExtra as $k => $list) :
										if (isset($_SESSION['extra_services_price'][$list['TipologiaBigliettoId']])) {
											$total += $_SESSION['extra_services_price'][$list['TipologiaBigliettoId']];
										}
										?>
										<!-- Hidden per identificare il tipo biglietto e passeggero -->
										<input type="hidden" name="TipoBigliettoId<?= $k ?>" id="TipoBigliettoId<?= $k ?>" value="<?= $list['TipologiaBigliettoId'] . '_' . $list['TipologiaBiglietto']; ?>">
										<input type="hidden" name="passangerId<?= $k ?>" id="passangerId<?= $k ?>" value="<?= $list['TipologiaBigliettoId'] ?>">
										<div class="control-group border-bottom row" style="padding-bottom: 32px;">
											<div class="col-md-3 col-xs-6">
												<span class="tipo_tour d-flex align-items-center title text-center title " style="margin-bottom:0px !important;">
													<?= $list['TipologiaBiglietto']; ?>
												</span>
												<span>
													<small class="d-block text-muted"><?= htmlspecialchars($list['TipologiaBigliettoDescr']); ?>
													<?php if ($list['LimitePerNumPassegeri'] > 0) { ?>
														<br><i style="color: red;">1 unit&agrave; &egrave; per <?= $list['LimitePerNumPassegeri'] ?> passeggeri.</i>
													<?php } ?>
													<?php if ($list['TipologiaBigliettoId'] == 23 && $oreMaxSosta > 0) { ?>
														<br><i style="color: red;">Max. <?= $oreMaxSosta ?>h.<br>Per prenotare pi&ugrave; tempo contattare lo staff Bertoldi Boats tramite i canali di supporto.</i>
													<?php } ?>
													</small>
												</span>
											</div>
											<div class="col-md-3 col-xs-6">
												<span class="tipo_tour d-flex align-items-center title text-center">
													<?= $list['Prezzo'] ?: 0; ?>€
												</span>
											</div>
											<?php if ($list['TipologiaBigliettoId'] == 23 && $tipoTour == 1 && $orarioPartenzaTour >= Config::$orarioMaxSosta) { ?>
												<div class="col-md-6 col-xs-6">
													<span style="color: red;">Per prenotare sosta notturna contattare lo staff Bertoldi Boats tramite i canali di supporto.</span>
												</div>
											<?php } else { ?>
												<div class="col-md-3 col-xs-6">
													<div class="number-input">
														<?php
														// Calcolo limite massimo/minimo per input numerico
														$limite = 0;
														if ($list['TipologiaBigliettoId'] == 23) {
															if ($oreMaxSosta > 0 && $oreMaxSosta < $list['Limite']) {
																$limite = $oreMaxSosta;
															} else {
																$limite = $list['Limite'];
															}
														} else {
															if ($list['Limite'] > 0) {
																$limite = $list['Limite'];
															}
														}
														?>
														<button type="button" class="btn btn-secondary btn-decrement" aria-label="Decrement">-</button>
														<input <?= ($limite > 0) ? 'max="' . $limite . '"' : '' ?>
															<?= ($list['LimiteMin'] > 0) ? 'min="' . $list['LimiteMin'] . '"' : 'min="0"' ?>
															type="number" name="BigliettoTipologiaPax[<?= $list['TipologiaBigliettoId'] . '_' . $list['TipologiaBiglietto']; ?>]" value="<?= isset($_SESSION['BigliettoTipologiaPax'][$list['TipologiaBigliettoId']]) ? $_SESSION['BigliettoTipologiaPax'][$list['TipologiaBigliettoId']] : 0; ?>" id="" class="number-ticket">
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
									echo "<i>Nessun servizio disponibile per il tour selezionato</i>";
								endif;
								?>
							</div>
							<input type="hidden" name="total_price" class="services_total_price" value="<?php echo $total; ?>">
							<div class="px-5">
								<button type="submit" name="submit" class="btn btn-primary w-full btn-big mt-5 rounded-pill" id="continua">
									CONTINUA
								</button>
							</div>
						</form>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php include_once("app.php"); ?>
		<script>
			// Funzione per il calcolo dei totali dei servizi extra
			function totaliCalcolo() {
				var total = 0;
				$('.control-group').each(function () {
					var quantity = parseInt($(this).find('input[type="number"]').val());
					var pricePerItem = parseFloat($(this).find('.tipo_tour').eq(1).text()); // Prezzo unitario
					var totalPrice = (isNaN(quantity) ? 0 : quantity) * pricePerItem;
					$(this).find('.total-price').text(totalPrice.toFixed(2) + ' €');
					$(this).find('.extra_total-price').val(totalPrice);
					total += totalPrice;
				});
				$('.services_total_price').val(total);
			}
			$(document).ready(function () {
				// Gestione incremento/decremento quantità
				$('.btn-decrement').click(function () {
					var input = $(this).next('.number-ticket');
					var currentValue = parseInt(input.val());
					if (currentValue > parseInt(input.attr('min'))) {
						input.attr('value', currentValue - 1);
						totaliCalcolo();
					}
				});
				$('.btn-increment').click(function () {
					var input = $(this).prev('.number-ticket');
					var currentValue = parseInt(input.val());
					if (input.attr('max') === undefined || currentValue < parseInt(input.attr('max'))) {
						input.attr('value', currentValue + 1);
						totaliCalcolo();
					}
				});
				// Permetti solo numeri nell'input
				$('input[type="number"]').on('keydown', function (event) {
					var valoreAttuale = $(this).val();
					if ((event.keyCode >= 48 && event.keyCode <= 57) ||
						(event.keyCode >= 96 && event.keyCode <= 105) ||
						event.keyCode === 8 || event.keyCode === 9 ||
						event.keyCode === 13 || event.keyCode === 46 ||
						event.keyCode === 37 || event.keyCode === 39) {
						return true;
					} else {
						event.preventDefault();
						return false;
					}
				});
				$('input[type="number"]').on('input', function () {
					totaliCalcolo();
				});
				// Logout
				$('#logout').click(function () {
					var formData = { action: "logoutBrowser" };
					$.ajax({
						url: '<?php echo Config::$UrlMobile; ?>',
						type: "POST",
						data: formData,
						dataType: 'json',
						success: function (responce) {
							window.location = "./login.php";
						},
						error: function (xhr, ajaxOptions, thrownError) { }
					});
				});
				// Ricerca tipo tour
				$('#tipo_tour').on('keyup', function () {
					var data = $('#tipo_tour').val();
					$("#search_seleziona").submit();
				});
				// Submit form ticket via AJAX
				$("#ticket_form").submit(function (e) {
					console.log(e);
					e.preventDefault();
					$.ajax({
						type: 'POST',
						url: "<?php echo $_SERVER['REQUEST_URI']; ?>",
						data: $(this).serialize(),
						success: function (response) {
							console.log(response);
							window.location = "./passenger-data.php";
						}
					});
				});
				// Gestione pulsante back
				$('#back').click(function () {
					<?php if ($_SESSION['ticket_date'] < date('Y-m-d')) { ?>
						window.location.href = "./index.php";
					<?php } else if ($_SESSION['tipo_viaggio'] == 1) { ?>
						window.location = "./ticket-prices.php";
					<?php } else { ?>
						window.location = "./ticket-prices-return.php";
					<?php } ?>
				});
			});
		</script>
	</body>
	</html>
	<?php
}

// Se l'autista è loggato, mostra la pagina, altrimenti redirect al login
if (is_array($autista)) {
	$db = new Database();
	$db->connect();
	$user = new Operatore();
	$user->OperatoreId = 1;
	$user->OdcId = 1;
	$user->IsAdmin = 1;
	$user->GestoreId = 1;
	$user->conn = $db;
	$permessi = $user->get_permessi_modulo($ModuloId);

	if (!isset($_REQUEST['do'])) {
		$do = '';
	} else {
		$do = $_REQUEST['do'];
	}

	switch ($do) {
		default:
			nuovaTicket();
			break;
	}
} else {
	// Se l'utente non è loggato, redirect al login
	header('Location: ./login.php');
}
?>
