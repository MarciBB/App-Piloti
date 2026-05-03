<?php
// Includi i file di configurazione e classi necessarie
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include_app.php");

$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;

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

// Funzione principale per la gestione ottimizzata
function gestioneOttimizzata()
{
	// Pulizia variabili di sessione
	unset($_SESSION['PrenotazioneId']);
	unset($_SESSION['CorsaId']);
	unset($_SESSION['DataPartenza']);
	unset($_SESSION['ticket_date']);
	unset($_SESSION['tipo_tour']);
	unset($_SESSION['BigliettoTipologiaPax']);
	unset($_SESSION['TipoBigliettoId']);
	unset($_SESSION['passangerId']);
	unset($_SESSION['porto_partenza']);
	unset($_SESSION['porto_destinazione']);
	unset($_SESSION['load_passeggeri']);
	unset($_SESSION['load_extra']);
	unset($_SESSION['nome']);
	unset($_SESSION['tel']);
	unset($_SESSION['mail']);
	unset($_SESSION['pagamento']);
	unset($_SESSION['prenotazione']);
	unset($_SESSION['GestoreIdRef']);
	unset($_SESSION['OrarioPartenzaLibera']);
	unset($_SESSION['OrarioArrivoLibera']);
	unset($_SESSION['BarcaLibera']);
	unset($_SESSION['total_price']);
	unset($_SESSION['LiberaImporto']);
	unset($_SESSION['prenotazione_libera']);
	unset($_SESSION['start_time']);
	unset($_SESSION['end_time']);
	unset($_SESSION['Pickup']);
	unset($_SESSION['Dropoff']);	
	unset($_SESSION['TitoloLibera']);
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Bertoldi Boats - App Operatore</title>
		<link rel="manifest" href="./manifest.json">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,900&display=swap" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="/css/reset.css" />
		<link rel="stylesheet" type="text/css" href="/css/style.css?v4" />
		<link rel="stylesheet" type="text/css" href="./css/app.css" />
		<link rel="stylesheet" type="text/css" href="./css/style.css" />
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
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" crossorigin="anonymous"></script>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
		<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
		<script src="https://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" crossorigin="anonymous"></script>
		<script>
			$(function() {
				$("#catalog").accordion({ autoHeight: false, navigation: true });
				$("#catalog tr").draggable({ appendTo: "body" });
				$("#catalog1").accordion({ autoHeight: false, navigation: true });
				$(".accordion_bus").accordion({
					autoHeight: false,
					navigation: true,
					collapsible: true,
					header: '.accordion_header'
				});
				$("#catalog1 tr").draggable({ appendTo: "body" });
			});
			// --- FINE GEOLOCALIZZAZIONE ---
		</script>
		<style>
			#accordion_content_custom .ui-accordion .ui-accordion-content {
				padding: 1em 1em !important;
			}
		</style>
		<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
		<link rel="stylesheet" href="/css/home.css" type="text/css" />
		<link rel="stylesheet" href="/css/home_2.css" type="text/css" />
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">
	</head>
	<body class="main-bg" id="home-applicativo">
		<?php
		// Pulizia variabili di sessione aggiuntive
		unset($_SESSION['modal']);
		unset($_SESSION['seleziona_lesperienza']);
		unset($_SESSION['tipo_tour']);
		unset($_SESSION['BigliettoTipologiaPax']);
		unset($_SESSION['TipoBigliettoId']);
		unset($_SESSION['passangerId']);
		unset($_SESSION['porto_partenza']);
		unset($_SESSION['porto_destinazione']);
		unset($_SESSION['tipo_viaggio']);
		unset($_SESSION['CorsaId']);
		unset($_SESSION['total_price']);
		unset($_SESSION['PickupId']);
		unset($_SESSION['DropoffId']);
		unset($_SESSION['start_time']);
		unset($_SESSION['end_time']);
		unset($_SESSION['time_diffrence']);
		unset($_SESSION['location']);
		unset($_SESSION['extra_services_price']);
		unset($_SESSION['total_price']);
		unset($_SESSION['totalprice']);
		unset($_SESSION['nome']);
		unset($_SESSION['tel']);
		unset($_SESSION['mail']);
		unset($_SESSION['pagamento']);
		unset($_SESSION['pagamentoId']);
		unset($_SESSION['pagamento']);

		global $user, $HtmlCommon, $dizionario, $autista;
		include_once("previaggio_validator.php");
		$db = new Database();
		$db->connect();

		$user = new Operatore();
		$user->OperatoreId = 1;
		$user->OdcId = 1;
		$user->IsAdmin = 1;
		$user->GestoreId = 1;

		$_SESSION['OPERATORE'] = serialize($user);

		// Recupero parametri da richiesta
		if (isset($_REQUEST['DataPartenza'])) {
			$DataPartenza = $_REQUEST['DataPartenza'];
		}
		if (isset($_REQUEST['CorsaId'])) {
			$CorsaId = $_REQUEST['CorsaId'];
		}
		$DataPartenza = date('Y-m-d');
		$datacorrente = Date('d/m/Y');
		$CorsaId = 0;
		if (isset($_POST['CorsaId']))
			$CorsaId = $_POST['CorsaId'];
		if (isset($_POST['DataPartenza'])) {
			$dt = new DT();
			$post_dal = $_POST['DataPartenza'];
			$DataPartenza = $_POST['DataPartenza'];
			$dateTime = new DateTime($DataPartenza);
			$datacorrente = $dateTime->format('d/m/Y');
		}
		$_SESSION['ticket_date'] = $DataPartenza;

	// Recupero lista barche attive
	$sqlBarche = "SELECT FlottaId, Modello AS Flotta FROM RT_Flotta WHERE Stato = 1 AND Cancella = 0 ORDER BY Flotta";
	$arr_barche = $db->fetch_array($sqlBarche);

	// Recupera FlottaId da POST (può essere array per multiselect)
	$FlottaId = [];
	if (isset($_POST['FlottaId'])) {
		if (is_array($_POST['FlottaId'])) {
			// Sanitizza array di valori
			$FlottaId = array_map('intval', $_POST['FlottaId']);
			$FlottaId = array_filter($FlottaId, function($id) { return $id > 0; });
		} else {
			// Singolo valore (compatibilità)
			$id = intval($_POST['FlottaId']);
			if ($id > 0) {
				$FlottaId = [$id];
			}
		}
	}

	// Query per la lista delle corse in base alla data selezionata
	$sqlCorse = "select 
						c.`CorsaId` AS `CorsaId`,
						appcal.`AppCalendarioData` AS `AppCalendarioData`,
						date_format(appcal.`AppCalendarioData`, _utf8'%d/%m/%Y') AS `DataPartenzaFormattata`,
						c.`CorsaNome` AS `CorsaNome`,
						c.`LineaId` AS `LineaId`,
						`RT_Linea`.`LineaNome` AS `LineaNome`,
						c.`OrarioPartenza` AS `OrarioPartenza`,
						`RT_Flotta`.Modello AS Flotta,
						RT_TipologiaBus.TotalePosti as `TotalePostiTour`,
						    (select IFNULL((select 
									count(0)
								from
									`RT_PrenotazionePercorso`
									join `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
									join `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
									and `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
									and `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
									join `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
									left join `RT_PrenotazioneNumero` `p` ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
									left join `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
								where
									((`RT_Prenotazione`.`Cancella` = 0)
									and (`RT_PrenotazionePercorso`.`Cancella` = 0)
									and (`RT_PrenotazionePercorso`.`Stato` = 1)
									and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
									and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
									and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
									and (`tb`.`OccupaPosto` = 1))
									and `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
								group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0))  AS `PostiRealmentePrenotati`,
							(select IFNULL((select 
									count(0)
								from
									`RT_PrenotazionePercorso`
									join `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
									join `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
									and `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
									and `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
									join `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
									left join `RT_PrenotazioneNumero` `p` ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
									left join `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
								where
									((`RT_Prenotazione`.`Cancella` = 0)
									and (`RT_PrenotazionePercorso`.`ValidatoMolo` = 1)
									and (`RT_PrenotazionePercorso`.`Cancella` = 0)
									and (`RT_PrenotazionePercorso`.`Stato` = 1)
									and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
									and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
									and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
									and (`tb`.`OccupaPosto` = 1))
									and `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
								group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0))  AS `PostiValidati`,
								`RT_Flotta`.Colore
					from
						`RT_Corsa` c
						join `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
						join `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
						join `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
						join `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
						JOIN `RT_Flotta` ON (c.`FlottaDefaultId` = `RT_Flotta`.`FlottaId`)
						LEFT JOIN `RT_TipologiaBus` ON (`RT_Flotta`.`TipologiaBusId` = `RT_TipologiaBus`.`TipologiaBusId`)
					where appcal.`AppCalendarioData` = '$DataPartenza'
						and c.AttivaDal <= '$DataPartenza' AND  c.AttivaAl >= '$DataPartenza' 
						and c.Stato = 1 and c.Cancella = 0 and `RT_Linea`.Stato = 1 and `RT_Linea`.Cancella = 0
						".((!empty($FlottaId) ? " and c.FlottaDefaultId IN (" . implode(',', $FlottaId) . ")" : ""))."
						and (select IFNULL((select 
									count(0)
								from
									`RT_PrenotazionePercorso`
									join `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
									join `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
									and `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
									and `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
									join `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
									left join `RT_PrenotazioneNumero` `p` ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
									left join `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
								where
									((`RT_Prenotazione`.`Cancella` = 0)
									and (`RT_PrenotazionePercorso`.`Cancella` = 0)
									and (`RT_PrenotazionePercorso`.`Stato` = 1)
									and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
									and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
									and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
									and (`tb`.`OccupaPosto` = 1))
									and `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
								group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0)) > 0
					ORDER BY appcal.`AppCalendarioData`, c.`OrarioPartenza`";
					
		$arr_tour = $db->fetch_array($sqlCorse);

		// Query per la lista di prenotazioni della data selezionata
		$sqlPrenotazioni = "select p.*, pp.CorsaId, pp.CorsaDataPartenza 
							from RT_Prenotazione p	
							left join RT_PrenotazionePercorso pp on pp.PrenotazioneId = p.PrenotazioneId
							where p.PrenotazioneStato = 3
								and p.Stato = 1
								and p.Cancella = 0
								and DATE(p.DataIns) = '$DataPartenza'
								and pp.Direzione = 'A'
								and p.OpeIns = " . $autista['OperatoreId'];
		$arr_scontrini = $db->fetch_array($sqlPrenotazioni);
		?>

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
				<div style="margin-bottom:10px;">Benvenuto <b><?php echo $autista['Nome'] . " " . $autista['Cognome']; ?></b></div>
				<form method="post" action="index.php" id="search">
					<div class="form-group form-icon w-full">
						<label for="data">Tour del Giorno</label>
						<div class="control-group">
							<input type="date" name="DataPartenza" id="DataPartenza" value="<?= $DataPartenza ?>" <?= (isset($autista['AppVisualizzaCorse']) && $autista['AppVisualizzaCorse'] == 1) ? '' : 'disabled=\"true\"' ?>>
							<img src="./css/imgs/nota.png">
						</div>
					</div>
					<button type="submit" class="btn btn-big btn-primary" style="width: 100%;">AGGIORNA E VEDI SCONTRINI</button>
				</form>

				<div class="actions">
					<button class="btn btn-big btn-secondary" id="scansiona" name="scansiona" type="button">VALIDA</button>
					<!-- Overlay per scansione QR -->
					<div id="qr-overlay" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.85);align-items:center;justify-content:center;flex-direction:column;">
							<div style="position:relative;background:#a5d4d6;padding:20px 20px 30px 20px;border-radius:16px;max-width:600px;width:95vw;max-height:95vh;display:flex;flex-direction:column;align-items:center;box-shadow:0 4px 32px rgba(0,0,0,0.25);">
								<button id="close-qr" style="position:absolute;top:10px;right:10px;font-size:2em;background:none;border:none;cursor:pointer;line-height:1;color:#fff;">&times;</button>
							<div class="bg-sea confirm-title" style="width:100%;margin-bottom:18px;text-align:center;padding:10px 0 8px 0;border-radius:8px 8px 0 0;">
								<h2 style="margin:0;font-size:1.6em;color:#fff;">Inquadra QrCode</h2>
								<div class="operator" style="color:#fff;font-size:1.1em;">per validare l’ingresso<br>dei clienti al molo.</div>
							</div>
								<div style="width:420px;max-width:90vw;max-height:340px;display:flex;align-items:center;justify-content:center;">
									<video id="preview" style="width:320px !important;height:320px;max-width:100vw;max-height:320px;object-fit:cover;border-radius:12px;background:#222;"></video>
								</div>
								<button type="button" class="btn btn-secondary btn-big" id="back-qr" style="margin-top:18px;margin-bottom:0;">Torna indietro</button>
								<div id="qr-feedback" style="margin-top:16px;color:#333;font-weight:bold;min-height:28px;text-align:center;"></div>
						</div>
					</div>
					<?php if (isset($autista['AppBigliettazioneABordo']) && $autista['AppBigliettazioneABordo'] == 1) { ?>
						<button class="btn btn-big btn-black" onclick="window.location.href='new-ticket.php'">NUOVO TICKET</button>
					<?php } ?>
				</div>

				<!-- Bottone filtri e pannello filtro barca -->
				<div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 8px;">
					<button id="toggleFiltroBarca" class="btn btn-light" style="border:1px solid #bbb;">
						<i class="fa fa-sliders" aria-hidden="true"></i> Filtri
					</button>
				</div>
				<div id="filtroBarcaPanel" style="display:none; margin-bottom:18px; background:#f7f7f7; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.07); padding:14px 18px 10px 18px;">
					<form method="post" action="index.php" id="filtroBarcaForm" style="margin-bottom:0;">
						<input type="hidden" name="DataPartenza" value="<?= htmlspecialchars($DataPartenza) ?>">
						<div class="form-group form-icon w-full" style="margin-bottom: 8px;">
							<label for="FlottaId" style="font-weight:500;">Filtra per barche (puoi selezionarne più di una)</label>
							<select name="FlottaId[]" id="FlottaId" class="form-control" multiple size="5" style="display:inline-block;">
								<?php foreach($arr_barche as $barca) { ?>
									<option value="<?= $barca['FlottaId'] ?>"<?= (in_array($barca['FlottaId'], $FlottaId) ? ' selected' : '') ?>><?= htmlspecialchars($barca['Flotta']) ?></option>
								<?php } ?>
							</select>
							<div style="margin-top:8px;">
								<button type="submit" class="btn btn-primary" style="margin-right:6px;">Applica Filtro</button>
								<button type="button" class="btn btn-light" id="resetFiltro" style="border:1px solid #ccc;">Reset</button>
							</div>
						</div>
					</form>
				</div>

				<div class="ticket-list">
					   <div class="filters">
						   <span>Tour da fare</span>
						   <button>
							   <i class="fa fa-chevron-up showTourDaFare upArrow" aria-hidden="true"></i>
							   <i class="fa fa-chevron-down showTourDaFare downArrow" aria-hidden="true" style="display:none;"></i>
						   </button>
					   </div>
					   <div class="allTour allTourDaFare">
					   <?php
					   // --- Suddivisione tour SOLO su data e orario di partenza ---
					   $tour_gia_effettuati = [];
					   $tour_da_fare = [];

					   // Orario attuale con timezone e minuti
					   $oraAttuale = new DateTime('now', new DateTimeZone('Europe/Rome'));
					   $plus15 = clone $oraAttuale;
					   $plus15->modify('+15 minutes');

					   foreach ($arr_tour as $t) {
						   // Data e ora partenza effettiva del tour
						   $partenza = DateTime::createFromFormat('Y-m-d H:i:s', $t['AppCalendarioData'] . ' ' . $t['OrarioPartenza']);
						   if (!$partenza) {
							   // fallback se OrarioPartenza non ha i secondi
							   $partenza = DateTime::createFromFormat('Y-m-d H:i', $t['AppCalendarioData'] . ' ' . $t['OrarioPartenza']);
						   }
						   // Tour già effettuati: partenza > ora+15min
						   if ($partenza && $partenza > $plus15) {
							   $tour_da_fare[] = $t;
						   } else {
							   $tour_gia_effettuati[] = $t;
						   }
					   }
					   // --- Mostra Tour da fare ---
					   if (count($tour_da_fare) > 0) {
						   foreach ($tour_da_fare as $t) {
							   $dateTime = new DateTime($t['OrarioPartenza']);
							   $formattedTime = $dateTime->format('H:i'); ?>
							   <a href='tour.php?CorsaId=<?= $t['CorsaId'] ?>&DataPartenza=<?= $t['AppCalendarioData'] ?>' class="link_no_underline">
								   <div class="ticket">
									   <div class="info">
										   <span class="title" style="margin-bottom:0px;display: block;">
											   <?php if (!empty($t['Colore'])) { ?>
													<span style="background:<?= htmlspecialchars($t['Colore']) ?>;color:#fff;padding:2px 10px 2px 10px;border-radius:14px;display:inline-block;font-weight:600;letter-spacing:0.5px;box-shadow:0 1px 4px rgba(0,0,0,0.07);margin-bottom:2px;">
														<?= $t['LineaNome'] ?>, <?= $t['CorsaNome'] ?>
													</span>
												<?php } else { ?>
															<?= $t['LineaNome'] ?>, <?= $t['CorsaNome'] ?>
												<?php } ?>
												<br />
												(Passeggeri: <?=$t['PostiRealmentePrenotati']?> / Validati: <?=$t['PostiValidati']?> / Max: <?=$t['TotalePostiTour']?>)
										   </span>
										   <!--<span class="date"> - Barca <?= $t['Flotta']?>, <?= $t['DataPartenzaFormattata'] ?> ore <?= $formattedTime ?></span> -->
									   </div>
									   <a href='tour.php?CorsaId=<?= $t['CorsaId'] ?>&DataPartenza=<?= $t['AppCalendarioData'] ?>'><i class="fa fa-eye buttonList" aria-hidden="true"></i></a>
								   </div>
							   </a>
						   <?php }
					   } else { ?>
						   <div class="ticket">
							   <div class="info">
								   <i>Nessun tour da fare per il giorno selezionato</i>
							   </div>
						   </div>
					   <?php } ?>
					   </div>
					   <div class="filters" style="margin-top:30px;">
						   <span>Tour già effettuati</span>
						   <button>
							   <i class="fa fa-chevron-up showTourEffettuati upArrow" aria-hidden="true"></i>
							   <i class="fa fa-chevron-down showTourEffettuati downArrow" aria-hidden="true" style="display:none;"></i>
						   </button>
					   </div>
					   <div class="allTour allTourEffettuati">
					   <?php
					   if (count($tour_gia_effettuati) > 0) {
						   foreach ($tour_gia_effettuati as $t) {
							   $dateTime = new DateTime($t['OrarioPartenza']);
							   $formattedTime = $dateTime->format('H:i'); ?>
							   <a href='tour.php?CorsaId=<?= $t['CorsaId'] ?>&DataPartenza=<?= $t['AppCalendarioData'] ?>' class="link_no_underline">
								   <div class="ticket" style="opacity:0.7;">
									   <div class="info">
											<span class="title" style="margin-bottom:0px;display: block;">
												<?php if (!empty($t['Colore'])) { ?>
													<span style="background:<?= htmlspecialchars($t['Colore']) ?>;color:#fff;padding:2px 10px 2px 10px;border-radius:14px;display:inline-block;font-weight:600;letter-spacing:0.5px;box-shadow:0 1px 4px rgba(0,0,0,0.07);margin-bottom:2px;">
														<?= $t['LineaNome'] ?>, <?= $t['CorsaNome'] ?>
													</span>
												<?php } else { ?>
															<?= $t['LineaNome'] ?>, <?= $t['CorsaNome'] ?>
												<?php } ?>
												<br />
												(Passeggeri: <?=$t['PostiRealmentePrenotati']?> / Validati: <?=$t['PostiValidati']?> / Max: <?=$t['TotalePostiTour']?>)
												<!-- <span class="date"> - Barca <?= $t['Flotta']?>, <?= $t['DataPartenzaFormattata'] ?> ore <?= $formattedTime ?></span> -->
											</span>
										</div>
									   <a href='tour.php?CorsaId=<?= $t['CorsaId'] ?>&DataPartenza=<?= $t['AppCalendarioData'] ?>'><i class="fa fa-eye buttonList" aria-hidden="true"></i></a>
								   </div>
							   </a>
						   <?php }
					   } else { ?>
						   <div class="ticket">
							   <div class="info">
								   <i>Nessun tour già effettuato per il giorno selezionato</i>
							   </div>
						   </div>
					   <?php } ?>
					   </div>
				</div>
				<div class="ticket-list">
					<div class="filters">
						<span>Scontrini del giorno</span>
						<button>
							<i class="fa fa-chevron-up showTicket upArrow" aria-hidden="true"></i>
							<i class="fa fa-chevron-down showTicket downArrow" aria-hidden="true" style="display:none;"></i>
						</button>
					</div>
					<div class="allTicket">
						<?php if (count($arr_scontrini) > 0) {
							foreach ($arr_scontrini as $t) {
								$dateTime = new DateTime($t['DataIns']);
								$formattedDate = $dateTime->format('d/m/Y');
								$formattedTime = $dateTime->format('H:i'); ?>
								<a href="show-ticket.php?PrenotazioneId=<?=$t['PrenotazioneId']?>&CorsaId=<?=$t['CorsaId']?>&DataPartenza=<?=$t['CorsaDataPartenza']?>" class="link_no_underline">
									<div class="ticket">
										<div class="info">
											<span class="title">Prenotazione <?= $t['CodicePrenotazione'] ?>, <?= $t['ClienteNome'] ?></span>
											<span class="date"> - <?= $formattedDate . ' ore ' . $formattedTime ?></span>
										</div>
										<button href="show-ticket.php?PrenotazioneId=<?=$t['PrenotazioneId']?>&CorsaId=<?=$t['CorsaId']?>&DataPartenza=<?=$t['CorsaDataPartenza']?>"><i class="fa fa-eye buttonList" aria-hidden="true"></i></button>
									</div>
								</a>
							<?php }
						} else { ?>
							<div class="ticket">
								<div class="info">
									<i>Nessuno scontrino emesso</i>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php include_once("app.php"); ?>
		<script type="text/javascript">
			   $(document).ready(function() {
				   // Toggle filtro barca
				   $('#toggleFiltroBarca').click(function(e) {
					   e.preventDefault();
					   $('#filtroBarcaPanel').slideToggle(180);
				   });
				   // Reset filtro barche
				   $('#resetFiltro').click(function(e) {
					   e.preventDefault();
					   $('#FlottaId').val([]);
					   $('#filtroBarcaForm').submit();
				   });
				   $('#logout').click(function() {
					   var formData = { action: "logoutBrowser" };
					   $.ajax({
						   url: '<?php echo Config::$UrlMobile; ?>',
						   type: "POST",
						   data: formData,
						   dataType: 'json',
						   success: function(responce) {
							   window.location = "./login.php";
						   }
					   });
				   });
				   $('#DataPartenza').change(function() {
					   $("#search").submit();
				   });
				   // Toggle Tour da fare
				   $('.showTourDaFare').click(function() {
					   var display = $('.allTourDaFare').css('display');
					   $('.allTourDaFare').slideToggle();
					   if (display != 'none') {
						   $('.showTourDaFare.upArrow').hide();
						   $('.showTourDaFare.downArrow').show();
					   } else {
						   $('.showTourDaFare.upArrow').show();
						   $('.showTourDaFare.downArrow').hide();
					   }
				   });
				   // Toggle Tour già effettuati
				   $('.showTourEffettuati').click(function() {
					   var display = $('.allTourEffettuati').css('display');
					   $('.allTourEffettuati').slideToggle();
					   if (display != 'none') {
						   $('.showTourEffettuati.upArrow').hide();
						   $('.showTourEffettuati.downArrow').show();
					   } else {
						   $('.showTourEffettuati.upArrow').show();
						   $('.showTourEffettuati.downArrow').hide();
					   }
				   });
				   // Toggle Scontrini
				   $('.showTicket').click(function() {
					   var display = $('.allTicket').css('display');
					   $('.allTicket').slideToggle();
					   if (display != 'none') {
						   $('.showTicket.upArrow').hide();
						   $('.showTicket.downArrow').show();
					   } else {
						   $('.showTicket.upArrow').show();
						   $('.showTicket.downArrow').hide();
					   }
				   });

				// --- QR SCAN LOGIC ---
				var scanner = null;
				var cameraStarted = false;
				$('#scansiona').on('click', function() {
					$('#qr-feedback').text('');
					$('#qr-overlay').css('display','flex');
					if (!scanner) {
						scanner = new Instascan.Scanner({ video: document.getElementById('preview'), mirror: false });
						scanner.addListener('scan', function (content) {
							$('#qr-feedback').text('Codice rilevato, controllo...');
							// Invia il codice via AJAX a validate-ticket.php e mostra il risultato
							// Usa la URL contenuta nel QR code come endpoint di validazione (come in app.php)
							var urlQRcode = content;
							var formData = { action: "validate", app: 1 };
							// Forza https se necessario
							if (urlQRcode.indexOf('http://') === 0) {
								urlQRcode = urlQRcode.replace('http://', 'https://');
							}
							$.ajax({
								url: urlQRcode,
								type: 'GET',
								data: formData,
								dataType: 'json',
								success: function(resp) {
									if(resp.risultato && resp.risultato['A'] != 0) {
										// Se la risposta contiene i dati necessari, reindirizza a show-ticket.php
										if(resp.infoBiglietto && resp.infoBiglietto['PrenotazioneId'] && resp.infoBiglietto['CorsaId'] && resp.infoBiglietto['DataPartenza']) {
											var url = 'show-ticket.php?PrenotazioneId=' + encodeURIComponent(resp.infoBiglietto['PrenotazioneId'])
												+ '&CorsaId=' + encodeURIComponent(resp.infoBiglietto['CorsaId'])
												+ '&DataPartenza=' + encodeURIComponent(resp.infoBiglietto['DataPartenza']);
											if(resp.infoBiglietto['CorsaIdRitorno']) url += '&CorsaIdRitorno=' + encodeURIComponent(resp.infoBiglietto['CorsaIdRitorno']);
											if(resp.infoBiglietto['DataPartenzaRitorno']) url += '&DataPartenzaRitorno=' + encodeURIComponent(resp.infoBiglietto['DataPartenzaRitorno']);
											if(resp.risultato['A']) url += '&A=' + encodeURIComponent(resp.risultato['A']);
											if(resp.risultato['R']) url += '&R=' + encodeURIComponent(resp.risultato['R']);
											window.location.href = url;
										} else {
											$('#qr-feedback').html('<span style="color:green;">Biglietto valido</span>');
										}
									} else {
										$('#qr-feedback').html('<span style="color:red;">Biglietto non valido. Il codice non è stato riconosciuto</span>');
									}
								},
								error: function() {
									$('#qr-feedback').html('<span style="color:red;">Errore di comunicazione col server.</span>');
								}
							});
						});
					}
					// Avvia la camera solo se non già avviata
					Instascan.Camera.getCameras().then(function (cameras) {
						if (cameras.length > 0) {
							scanner.start(cameras[1]);
							cameraStarted = true;
						} else {
							$('#qr-feedback').html('<span style="color:red;">Nessuna camera trovata.</span>');
						}
					}).catch(function (e) {
						var msg = ''+e;
						if (msg.indexOf('Cannot access video stream') !== -1 || msg.indexOf('TypeError') !== -1) {
							$('#qr-feedback').html('<span style="color:red;">Camera del dispositivo non rilevata</span>');
						} else {
							$('#qr-feedback').html('<span style="color:red;">Errore accesso camera: '+msg+'</span>');
						}
					});
				});
				function closeQrModal() {
					$('#qr-overlay').hide();
					$('#qr-feedback').text('');
					if(scanner && cameraStarted) {
						scanner.stop();
						cameraStarted = false;
					}
				}
				$('#close-qr').on('click', closeQrModal);
				$('#back-qr').on('click', closeQrModal);
				// Chiudi overlay con ESC
				$(document).on('keydown', function(e) {
					if(e.key === "Escape") {
						$('#close-qr').click();
					}
				});
			});
		</script>
	</body>
	</html>
<?php
	$db->close();
}

// Avvio della gestione ottimizzata se l'autista è loggato
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
			gestioneOttimizzata();
			break;
	}
} else {
	// Se non loggato, redirect a login
	header('Location: ./login.php');
}
?>

<?php
