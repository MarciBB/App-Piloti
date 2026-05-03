<?php
// Imposta il percorso base del progetto
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include_app.php");

// Carica la configurazione
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

// Inizializza la gestione degli errori
$errors = new Errors();

// Includi le classi necessarie
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

// Abilita la visualizzazione degli errori per debugging
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$ModuloId = 1;
global $db, $autista, $user;

// Connessione al database
$db = new Database();
$db->connect();

// Controllo login: se non loggato, reindirizza al login
if (!isset($_SESSION['autista'])) {
	header('Location: ./login.php');
}
$autista = $_SESSION['autista'];

// Imposta cookie cross-site
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');

/**
 * Funzione principale per la creazione/modifica di un ticket
 */
function nuovaTicket()
{
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<title>Bertoldi Boats - Nuovo Ticket</title>

		<!-- Manifest e font -->
		<link rel="manifest" href="./manifest.json">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="./css/new-ticket.css" />

		<!-- jQuery UI e plugin vari -->
		<link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="/js/jquery.min.js"></script>
		<script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
		<script type="text/javascript" src="/js/jquery.maskedinput-1.1.4.js"></script>
		<script type="text/javascript" src="/js/menu_hover.js"></script>
		<script type="text/javascript" language="javascript" src="/js/dialogbox.js?v=3"></script>
		<script type="text/javascript" language="javascript" src="/js/validate/jquery.validate.js"></script>
		<script type="text/javascript" language="javascript" src="/js/jquery.form.track.changes.js"></script>
		<script type="text/javascript" language="javascript" src="/js/ui.multiselect.js"></script>

		<!-- Librerie esterne per QR code e Vue.js -->
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
	</head>

	<?php
	// Variabili di controllo e inizializzazione
	$codiceControllo = 'gC_6XHrZvC9$avW!';
	global $user, $HtmlCommon, $dizionario, $autista;

	// Connessione al database
	$db = new Database();
	$db->connect();

	// Recupera dati operatore (mock, da sostituire con dati reali)
	$user = new Operatore();
	$user->OperatoreId = 1;
	$user->OdcId = 1;
	$user->IsAdmin = 1;
	$user->GestoreId = 1;

	// Gestione parametri in ingresso
	if (isset($_REQUEST['DataPartenza'])) {
		$DataPartenza = $_REQUEST['DataPartenza'];
	}
	if (isset($_REQUEST['CorsaId'])) {
		$CorsaId = $_REQUEST['CorsaId'];
	}

	// Imposta la data di partenza e la data corrente
	$DataPartenza = date('Y-m-d');
	$datacorrente = Date('d/m/Y');
	$CorsaId = 0;

	// Seleziona corsa se presente in POST
	if (isset($_POST['CorsaId'])) {
		$CorsaId = $_POST['CorsaId'];
		$sqlLinea = "SELECT * FROM RT_Corsa WHERE CorsaId = $CorsaId";
		$rowTemp = $db->query_first($sqlLinea);
		$LineaId = $rowTemp['LineaId'];
	}

	// Gestione data partenza da POST
	if (isset($_POST['DataPartenza'])) {
		$dt = new DT();
		$post_dal = $_POST['DataPartenza'];
		$DataPartenza = $_POST['DataPartenza'];
		$dateTime = new DateTime($DataPartenza);
		$datacorrente = $dateTime->format('d/m/Y');
		$_SESSION['ticket_date'] = $_POST['DataPartenza'];
	}

	// Salva dati prenotazione in sessione
	if (isset($_POST['PrenotazioneId'])) {
		$_SESSION['PrenotazioneId'] = $_POST['PrenotazioneId'];
		$_SESSION['CorsaId'] = $_POST['CorsaId'];
		$_SESSION['DataPartenza'] = $_POST['DataPartenza'];
		$_SESSION['ticket_date'] = $_POST['DataPartenza'];
		$_SESSION['tipo_tour'] = $_POST['tipo_tour'];
		$_SESSION['prenotazione_libera'] = isset($_POST['prenotazione_libera']) ? $_POST['prenotazione_libera'] : null;
		$_SESSION['TitoloLibera'] = isset($_POST['TitoloLibera']) ? $_POST['TitoloLibera'] : null;
		$_SESSION['OrarioPartenzaLibera'] = isset($_POST['OrarioPartenzaLibera']) ? $_POST['OrarioPartenzaLibera'] : null;
		$_SESSION['OrarioArrivoLibera'] = isset($_POST['OrarioArrivoLibera']) ? $_POST['OrarioArrivoLibera'] : null;
		$_SESSION['BarcaLibera'] = isset($_POST['BarcaLibera']) ? $_POST['BarcaLibera'] : null;
		$_SESSION['LiberaImporto'] = isset($_POST['LiberaImporto']) ? $_POST['LiberaImporto'] : null;
	}

	// Salva esperienza selezionata in sessione
	if (isset($_POST['seleziona_lesperienza'])) {
		$seleziona_lesperienza = $_POST['seleziona_lesperienza'];
		$_SESSION['seleziona_lesperienza'] = $seleziona_lesperienza;
	}

	// Gestione gestore selezionato
	if (isset($_POST['seleziona_gestore'])) {
		$gestoreIdRef = $_POST['seleziona_gestore'];
		$_SESSION['GestoreIdRef'] = $gestoreIdRef;
	} else {
		if (isset($_SESSION['GestoreIdRef'])) {
			$gestoreIdRef = $_SESSION['GestoreIdRef'];
		} else {
			$gestoreIdRef = '143';
			$_SESSION['GestoreIdRef'] = $gestoreIdRef;
		}
	}

	// Gestione tipo tour
	if (isset($_SESSION['tipo_tour']) && !isset($_POST['tipo_tour'])) {
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

	// Query per recuperare i tour disponibili
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

	//recupero la linea di prenotazione libera
	if($tipoTour == 1) {
		$sql = "SELECT * FROM RT_Linea WHERE LineaId = 14 AND Stato = 1 AND Cancella = 0";
		$rowLinea = $db->query_first($sql);
		$arr_tour[] = $rowLinea;
	}

	// Query per recuperare la lista dei gestori
	$slqGestore = "SELECT 
			GestoreId, RagioneSociale as Gestore
		FROM
			Gestore
		WHERE
			Stato = 1 AND Cancella = 0
		ORDER BY RagioneSociale ASC";
	$arr_gestore = $db->fetch_array($slqGestore);
	?>

	<body class="main-bg" id="nuova-prenotazione">
		<!-- Top menu con logo e logout -->
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
				<!-- Barra info con titolo e data -->
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
						<form id="change-date-form" method="post" style="display:inline;">
							<input type="hidden" name="DataPartenza" id="DataPartenza_hidden" value="<?= isset($_SESSION['ticket_date']) ? date('d/m/Y', strtotime($_SESSION['ticket_date'])) : date('d/m/Y') ?>">
							<span class="date" id="date-display" style="cursor:pointer; text-decoration:underline;">
								<?= date('M d, Y', strtotime($_SESSION['ticket_date'])) ?>
							</span>
						</form>
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
					<!-- Form di creazione/modifica ticket -->
					<div class="form-container">
						<div class="card-cta">
							<p>Compila i campi e conferma la prenotazione!</p>
						</div>
						<!-- Form per selezione tipo tour (submit automatico al cambio) -->
						<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="tipo_tour_form" method="post">
							<input type="hidden" name="tipo_tour" class="selected_tipo">
						</form>
						<!-- Form principale per il ticket -->
						<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="ticket_form">
							<div class="form-group">
								<label for="seleziona_gestore">Seleziona Azienda / Hotel che emette il biglietto</label>
								<select name="seleziona_gestore" id="seleziona_gestore">
									<?php
									if (count($arr_gestore) > 0) {
										foreach ($arr_gestore as $t) {
									?>
											<option data-description="<?= $t['GestoreId'] ?>" <?= isset($_SESSION['GestoreIdRef']) && $_SESSION['GestoreIdRef'] === $t['GestoreId'] ? 'selected' : ''; ?> value="<?= $t['GestoreId']; ?>"> <?= $t['Gestore']; ?> </option>
									<?php
										}
									}
									?>
								</select>
							</div>

							<div class="form-group">
								<label for="tipo_tour">Tipo Tour</label>
								<select name="tipo_tour" id="tipo_tour">
									<option <?= $tipoTour == 0 ? 'selected' : ''; ?> value="0">Tour di gruppo </option>
									<option <?= $tipoTour == 1 ? 'selected' : ''; ?> value="1">Tour privato </option>
								</select>
							</div>

							<div class="form-group">
								<label for="seleziona_lesperienza">Seleziona l'esperienza</label>
								<select name="seleziona_lesperienza" id="seleziona_lesperienza">
									<?php
									if (count($arr_tour) > 0) {
										foreach ($arr_tour as $t) {
									?>
											<option data-description="<?= $t['LineaDescrizione'] ?>" <?= isset($_SESSION['seleziona_lesperienza']) && $_SESSION['seleziona_lesperienza'] === $t['LineaId'] ? 'selected' : ''; ?> value="<?= $t['LineaId']; ?>"> <?= $t['LineaNome']; ?> </option>
									<?php
										}
									}
									?>
								</select>
								<div id="note_tour" style="margin-top:10px;font-size: 14px;"></div>
							</div>
							<button class="btn btn-primary w-full btn-big" id="continua" type="submit">
								CONTINUA
							</button>
						</form>
					</div>
				<?php } ?>
			</div>
		</div>

		<?php include_once("app.php"); ?>

		<!-- Script JS per gestione interfaccia e invio dati -->
		<script type="text/javascript">
			$(document).ready(function () {
				// Logout
				$('#logout').click(function () {
					var formData = {
						action: "logoutBrowser",
					};
					$.ajax({
						url: '<?php echo Config::$UrlMobile; ?>',
						type: "POST",
						data: formData,
						dataType: 'json',
						success: function (responce) {
							window.location = "./login.php";
						}
					});
				});

				// Datepicker per cambio data
				$('#date-display').click(function () {
					// Crea input temporaneo per il datepicker
					if ($('#datepicker-temp').length === 0) {
						var offset = $(this).offset();
						var $input = $('<input type="text" id="datepicker-temp" style="position:absolute;z-index:9999;width:120px;">');
						$input.val($('#DataPartenza_hidden').val());
						$('body').append($input);
						$input.css({ top: offset.top + $(this).height() + 5, left: offset.left });
						$input.datepicker({
							dateFormat: 'dd/mm/yy',
							defaultDate: $input.val(),
							onClose: function (dateText) {
								var oldVal = $('#DataPartenza_hidden').val();
								if (dateText && dateText !== oldVal) {
									// Conversione da dd/mm/yyyy a yyyy-mm-dd
									var parts = dateText.split('/');
									if(parts.length === 3) {
										var isoDate = parts[2] + '-' + parts[1].padStart(2, '0') + '-' + parts[0].padStart(2, '0');
										$('#DataPartenza_hidden').val(isoDate);
									} else {
										$('#DataPartenza_hidden').val(dateText);
									}
									$('#change-date-form').submit();
								}
								$input.remove();
							}
						}).datepicker('show');
					}
				});

				// Pulsante indietro
				$('#back').click(function () {
					<?php if (isset($_POST['PrenotazioneId'])) { ?>
						window.location.href = "./show-ticket.php?PrenotazioneId=<?= $_POST['PrenotazioneId'] ?>&CorsaId=<?= $_POST['CorsaId'] ?>&DataPartenza=<?= $_POST['DataPartenza'] ?>";
					<?php } else if (isset($_SESSION['PrenotazioneId'])) { ?>
						window.location.href = "./show-ticket.php?PrenotazioneId=<?= $_SESSION['PrenotazioneId'] ?>&CorsaId=<?= $_SESSION['CorsaId'] ?>&DataPartenza=<?= $_SESSION['DataPartenza'] ?>";
					<?php } else { ?>
						window.location = "./index.php";
					<?php } ?>
				});

				// Cambio tipo tour: invia form per aggiornare la lista esperienze
				$('#tipo_tour').on('change', function () {
					var data = $('#tipo_tour').val();
					$(".selected_tipo").val(data);
					$("#tipo_tour_form").submit();
				});

				// Invio form ticket via AJAX
				$("#ticket_form").submit(function (e) {
					e.preventDefault();
					$.ajax({
						type: 'POST',
						url: "<?php echo $_SERVER['REQUEST_URI']; ?>",
						data: $('#ticket_form').serialize(),
						success: function (response) {
							window.location = "./choose-seats.php";
						}
					});
				});

				// Aggiorna note esperienza al cambio selezione
				$("#seleziona_lesperienza").change(function () {
					var description = $(this).find('option:selected').data('description');
					if (description != '') {
						$('#note_tour').html('<u>Note:</u> ' + description);
					} else {
						$('#note_tour').html('');
					}
				});

				// Mostra note esperienza selezionata all'avvio
				var description = $("#seleziona_lesperienza").find('option:selected').data('description');
				if (description != '') {
					$('#note_tour').html('<u>Note:</u> ' + description);
				} else {
					$('#note_tour').html('');
				}
			});
		</script>
	</body>
	</html>
	<?php
}

// Flusso principale: se l'autista è loggato, mostra la pagina ticket
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

	// Gestione azioni (estendibile)
	$do = isset($_REQUEST['do']) ? $_REQUEST['do'] : '';
	switch ($do) {
		default:
			nuovaTicket();
			break;
	}
} else {
	// Se non loggato, reindirizza al login
	header('Location: ./login.php');
}
?>