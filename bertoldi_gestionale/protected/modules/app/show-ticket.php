<?php
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

//controllo login
if (!isset($_SESSION['autista'])) {
	header('Location: ./login.php');
}
$autista = $_SESSION['autista'];
header('Set-Cookie: cross-site-cookie=name; SameSite=None; Secure');

// Funzione principale per la gestione ottimizzata della pagina di conferma ticket
function gestioneOttimizzata()
{
	// Pulizia delle variabili di sessione relative alla prenotazione
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
	unset($_SESSION['GestioneIdRef']);
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
		<link rel="stylesheet" type="text/css" href="/css/style.css?v5" />
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

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<!-- jQuery first, then Popper.js, then Bootstrap JS -->
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js" type="text/javascript"></script>
		<script src="https://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
		<script src="https://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>

		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

		<script>
			$(function() {
				$("#catalog").accordion({
					autoHeight: false,
					navigation: true
				});

				$("#catalog tr").draggable({
					appendTo: "body"
					//helper: "clone"
				});

				$("#catalog1").accordion({
					autoHeight: false,
					navigation: true
				});

				$(".accordion_bus").accordion({
					autoHeight: false,
					navigation: true,
					collapsible: true,
					autoHeight: false,
					navigation: true,
					header: '.accordion_header'
				});

				$("#catalog1 tr").draggable({
					appendTo: "body"
				});

			});
		</script>

		<style>
			#accordion_content_custom .ui-accordion .ui-accordion-content {
				padding: 1em 1em !important;
			}
		</style>

		<!--[if lte IE 8]>
				<link rel="stylesheet" href="/css/styleIE.css" type="text/css" />
				<![endif]-->

		<link rel="stylesheet" type="text/css" href="/css/mediazioni.css" />
		<link rel="stylesheet" href="/css/home.css" type="text/css" />
		<link rel="stylesheet" href="/css/home_2.css" type="text/css" />
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet">

	</head>

	<body class="main-bg bg-ancore" id="validazione-ingresso">

		<!-- Fine Live Search -->
		<?php

		$codiceControllo = 'gC_6XHrZvC9$avW!';

		/* if (!isset($_POST['CorsaId']))
    {
    $code=$_REQUEST['code'];
	    if ($code!=$codiceControllo){
	    	header('Location: ./login.php');
	        die("servizio non disponibile");
	    }
    }*/

		global $user, $HtmlCommon, $dizionario, $autista;

		$db = new Database();
		$db->connect();

		$user = new Operatore();
		$user->OperatoreId = 1;
		$user->OdcId = 1;
		$user->IsAdmin = 1;
		$user->GestoreId = 1;


		$DataPartenza = date('Y-m-d');
		$datacorrente = Date('d/m/Y');
		$CorsaId = 0;
		if (isset($_GET['CorsaId']))
			$CorsaId = $_GET['CorsaId'];

		if (isset($_GET['DataPartenza'])) {
			$dt = new DT();
			$post_dal = $_GET['DataPartenza'];
			$DataPartenza = $_GET['DataPartenza'];
			$dateTime = new DateTime($DataPartenza);
			$datacorrente = $dateTime->format('d/m/Y');
		}

		$DataPartenzaRitorno = null;
		if (isset($_GET['DataPartenzaRitorno'])) {
			$DataPartenzaRitorno = $_GET['DataPartenzaRitorno'];
		}

		$CorsaIdRitorno = null;
		if (isset($_GET['CorsaIdRitorno'])) {
			$CorsaIdRitorno = $_GET['CorsaIdRitorno'];
		}

		$messaggioAndata = null;
		$messaggioRitorno = null;
		if (isset($_GET['A'])) {
			switch ($_GET['A']) {
				case 2:
					$messaggioAndata = "Biglietto di Andata Valido";
					break;
				case 1:
					$messaggioAndata = "Biglietto di Andata Valido - Orario di partenza superato";
					break;
			}
		}
		if (isset($_GET['R'])) {
			switch ($_GET['R']) {
				case 2:
					$messaggioRitorno = "Biglietto di Ritorno Valido";
					break;
				case 1:
					$messaggioRitorno = "Biglietto di Ritorno Valido - Orario di partenza superato";
					break;
			}
		}


		$PrenotazioneId = 0;
		if (isset($_GET['PrenotazioneId'])) {
			$dt = new DT();
			$PrenotazioneId = $_GET['PrenotazioneId'];
		}

		//recupero la corsa selezionata
		// Query per recuperare i dati della corsa selezionata
		$sqlCorsa = "SELECT 
			c.`CorsaId` AS `CorsaId`,
			appcal.`AppCalendarioData` AS `AppCalendarioData`,
			DATE_FORMAT(appcal.`AppCalendarioData`, _utf8'%d/%m/%Y') AS `DataPartenzaFormattata`,
			c.`CorsaNome` AS `CorsaNome`,
			c.`LineaId` AS `LineaId`,
			`RT_Linea`.`LineaNome` AS `LineaNome`,
			c.`OrarioPartenza` AS `OrarioPartenza`,
			c.FlottaDefaultId AS FlottaDefaultId
		FROM
			`RT_Corsa` c
			JOIN `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
			JOIN `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
			JOIN `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
			JOIN `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
		WHERE appcal.`AppCalendarioData` = '$DataPartenza' AND c.`CorsaId` = $CorsaId";
		$corsa = $db->query_first($sqlCorsa);

		//recupero il biglitto principale della prenotazione
	// Query per recuperare il biglietto principale della prenotazione
	$sqlBiglietto = "SELECT * FROM RT_PrenotazioneBiglietto WHERE PrenotazioneId = $PrenotazioneId AND Stato = 1 AND Cancella = 0";
	$biglietto = $db->query_first($sqlBiglietto);

		//recupero la corsa di ritorno se presente
		// Se presente, recupera i dati della corsa di ritorno
		if (isset($DataPartenzaRitorno) && isset($CorsaIdRitorno)) {
			$sqlCorsa = "SELECT 
				c.`CorsaId` AS `CorsaId`,
				appcal.`AppCalendarioData` AS `AppCalendarioData`,
				DATE_FORMAT(appcal.`AppCalendarioData`, _utf8'%d/%m/%Y') AS `DataPartenzaFormattata`,
				c.`CorsaNome` AS `CorsaNome`,
				c.`LineaId` AS `LineaId`,
				`RT_Linea`.`LineaNome` AS `LineaNome`,
				c.`OrarioPartenza` AS `OrarioPartenza`
			FROM
				`RT_Corsa` c
				JOIN `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
				JOIN `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
				JOIN `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
				JOIN `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
			WHERE appcal.`AppCalendarioData` = '$DataPartenzaRitorno' AND c.`CorsaId` = $CorsaIdRitorno";
			$corsaRitorno = $db->query_first($sqlCorsa);
		}


		//recupero il titolo 
	// Query per recuperare il titolo della prenotazione
	$queryTitolo = "SELECT * FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = '$PrenotazioneId' AND Stato = 1 AND Cancella = 0 AND TipoTitolo = 'E'";
	$titolo = $db->query_first($queryTitolo);
	$prenotazioneTitoloId = $titolo['PrenotazioneTitoloId'];

		//recupero la prenotazione eseguita da questo operatori la corsa selezionata
		// Query principale per recuperare i dati della prenotazione e del biglietto
		$sqlPrenotazioni = "SELECT
			`apps`.`PrenotazioneStato` AS `StatoPrenotazione`,
			`g`.`RagioneSociale` AS `RagioneSociale`,
			(CASE
				WHEN (`rdc`.`Codice` IS NOT NULL)
					THEN CONCAT(CONVERT(`rdc`.`Codice` USING utf8), _utf8'/', CAST(`rdc`.`Anno` AS CHAR(5) CHARSET utf8))
				WHEN (`rdc`.`Codice` IS NULL AND `apps`.`PrenotazioneStato` = _latin1'Biglietto emesso')
					THEN (CONVERT((SELECT CONCAT(Codice, _utf8'/', Anno) FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = `p`.`PrenotazioneId` AND TipoTitolo = 'E') USING utf8))
				ELSE CONCAT(CONVERT(`rdc`.`CodicePrenotazione` USING utf8), _utf8'/', CAST(`rdc`.`PrenotazioneNumero` AS CHAR(10) CHARSET utf8))
			END) AS `NumeroBiglietto`,
			(CASE
				WHEN (`tp`.`OccupaPosto` = 1) THEN `p`.`ClienteNome`
				ELSE CONCAT('Servizio: ', _latin1' ', `pd`.`Nome`)
			END) AS `Cliente`,
			CONCAT(_latin1'+', `p`.`ClienteCellularePrefisso`, _latin1' ', `p`.`ClienteCellulare`) AS `ClienteCellulare`,
			(CASE 
				WHEN `p`.`ClienteCellulareFamiliare` IS NULL OR `p`.`ClienteCellulareFamiliare` = '' 
					THEN '' 
				ELSE CONCAT(_latin1'+', `p`.`ClienteCellularePrefisso`, _latin1' ', `p`.`ClienteCellulareFamiliare`) 
			END) AS `ClienteWhatsapp`,
			p.ClienteEmail AS ClienteEmail,
			`pd`.`OrarioPartenza` AS `OrarioPartenza`,
			`pd`.`OrarioArrivo` AS `OrarioArrivo`,
			`pd`.`DataArrivo` AS `DataArrivo`,
			`pd`.`ComunePartenza` AS `ComunePartenza`,
			`pd`.`ComuneArrivo` AS `ComuneArrivo`,
			(CASE
				WHEN (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') THEN _utf8'CS'
				ELSE _utf8'A/R'
			END) AS `TipoViaggio`,
			(CASE
				WHEN (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') THEN ROUND(`pd`.`Importo`, 2)
				ELSE ROUND((`pb`.`PrezzoPax` + ((`pb`.`AumentoPax` - `pb`.`RiduzionePax`) / `pb`.`NumeroPax`)), 2)
			END) AS `Importo`,
			`pd`.`CorsaId` AS `CorsaId`,
			`pd`.`DataPartenza` AS `DataPartenza`,
			`pd`.`PrenotazioneDettaglioId` AS `PrenotazioneDettaglioId`,
			`pd`.`DataInizioItinerario` AS `DataInizioItinerario`,
			`p`.`PrenotazioneId` AS PrenotazioneId,
			`p`.`DataIns` AS DataIns,
			`p`.`Libera` AS Libera,
			`pd`.`LineaId` AS `LineaId`,
			l.TipoTour AS TipoTour,
			p.TotaleDaPagare,
			p.TotaleResiduo,
			p.Libera,
			p.LiberaTitolo,
			pc.ValidatoMolo
		FROM
			(((((((`RT_PrenotazioneDettaglio` `pd`
			LEFT JOIN `RT_Prenotazione` `p` ON ((`pd`.`PrenotazioneId` = `p`.`PrenotazioneId`)))
			LEFT JOIN `RT_AppPrenotazioneStato` `apps` ON ((`p`.`PrenotazioneStato` = `apps`.`PrenotazioneStatoId`)))
			LEFT JOIN `Operatore` `o` ON ((`p`.`OpeIns` = `o`.`OperatoreId`)))
			LEFT JOIN `Gestore` `g` ON ((`o`.`GestoreId` = `g`.`GestoreId`)))
			LEFT JOIN `RT_PrenotazioneDettaglioCompleto` `rdc` ON (((`p`.`PrenotazioneId` = `rdc`.`PrenotazioneId`)
				AND (`rdc`.`PrenotazioneId` = `pd`.`PrenotazioneId`)
				AND (`rdc`.`PrenotazioneNumero` = `pd`.`PrenotazioneNumero`)
				AND (`rdc`.`DataPartenza` = `pd`.`DataPartenza`))))
			LEFT JOIN `RT_PrenotazioneBiglietto` `pb` ON (((`pb`.`PrenotazioneId` = `p`.`PrenotazioneId`)
				AND (`pb`.`TipologiaBiglietto` = `pd`.`TipologiaBiglietto`))))
			LEFT JOIN `RT_TipologiaBiglietto` `tp` ON (((`tp`.`TipologiaBigliettoId` = `pb`.`TipologiaBigliettoId`))))
			LEFT JOIN RT_Linea l ON l.LineaId = pd.LineaId
			LEFT JOIN RT_PrenotazionePercorso pc ON (pc.PrenotazioneId = p.PrenotazioneId AND pc.Direzione = 'A')
		WHERE
			(((`p`.`PrenotazioneStato` = 3)
				OR (`p`.`PrenotazioneStato` = 1)
				OR (`p`.`PrenotazioneStato` = 2))
				AND (`pd`.`Escludi` <> 1)
				AND (`pd`.`Rimborso` <> 1))
				AND `tp`.`OccupaPosto` = 1
				AND (`rdc`.Codice NOT LIKE 'E-%' OR `rdc`.Codice IS NULL)
				AND `pd`.`CorsaId` = $CorsaId
				AND `pd`.`DataPartenza` = '$DataPartenza'
				AND `p`.`PrenotazioneId` = $PrenotazioneId";
		$prenotazione = $db->query_first($sqlPrenotazioni);

		$dateTime = new DateTime($prenotazione['OrarioPartenza']);
		$formattedOrarioPartenza = $dateTime->format('H:i');

		$dateTime = new DateTime($prenotazione['OrarioArrivo']);
		$formattedOrarioArrivo = $dateTime->format('H:i');

		//recupero lista passeggeri
	// Query per recuperare la lista dei passeggeri
	$sql = "SELECT * FROM RT_PrenotazioneBiglietto b
	    LEFT JOIN RT_TipologiaBiglietto t ON t.TipologiaBigliettoId = b.TipologiaBigliettoId
	    WHERE PrenotazioneId = " . $prenotazione['PrenotazioneId'] . " AND t.OccupaPosto = 1";
	$passeggeriBiglietto = $db->fetch_array($sql);

		//recupero lista servizi
	// Query per recuperare la lista dei servizi aggiuntivi
	$sql = "SELECT * FROM RT_PrenotazioneBiglietto b
	    LEFT JOIN RT_TipologiaBiglietto t ON t.TipologiaBigliettoId = b.TipologiaBigliettoId
	    WHERE PrenotazioneId = " . $prenotazione['PrenotazioneId'] . " AND t.OccupaPosto = 0";
	$serviziBiglietto = $db->fetch_array($sql);

		// Se presente, query per recuperare i dati della prenotazione di ritorno
		if (isset($DataPartenzaRitorno) && isset($CorsaIdRitorno)) {
			$sqlPrenotazioni = "SELECT
				`apps`.`PrenotazioneStato` AS `StatoPrenotazione`,
				`g`.`RagioneSociale` AS `RagioneSociale`,
				(CASE
					WHEN (`rdc`.`Codice` IS NOT NULL)
						THEN CONCAT(CONVERT(`rdc`.`Codice` USING utf8), _utf8'/', CAST(`rdc`.`Anno` AS CHAR(5) CHARSET utf8))
					WHEN (`rdc`.`Codice` IS NULL AND `apps`.`PrenotazioneStato` = _latin1'Biglietto emesso')
						THEN (CONVERT((SELECT CONCAT(Codice, _utf8'/', Anno) FROM RT_PrenotazioneTitolo WHERE PrenotazioneId = `p`.`PrenotazioneId` AND TipoTitolo = 'E') USING utf8))
					ELSE CONCAT(CONVERT(`rdc`.`CodicePrenotazione` USING utf8), _utf8'/', CAST(`rdc`.`PrenotazioneNumero` AS CHAR(10) CHARSET utf8))
				END) AS `NumeroBiglietto`,
				(CASE
					WHEN (`tp`.`OccupaPosto` = 1) THEN `p`.`ClienteNome`
					ELSE CONCAT('Servizio: ', _latin1' ', `pd`.`Nome`)
				END) AS `Cliente`,
				CONCAT(_latin1'+', `p`.`ClienteCellularePrefisso`, _latin1' ', `p`.`ClienteCellulare`) AS `ClienteCellulare`,
				(CASE 
					WHEN `p`.`ClienteCellulareFamiliare` IS NULL OR `p`.`ClienteCellulareFamiliare` = '' 
						THEN '' 
					ELSE CONCAT(_latin1'+', `p`.`ClienteCellularePrefisso`, _latin1' ', `p`.`ClienteCellulareFamiliare`) 
				END) AS `ClienteWhatsapp`,
				p.ClienteEmail AS ClienteEmail,
				`pd`.`OrarioPartenza` AS `OrarioPartenza`,
				`pd`.`OrarioArrivo` AS `OrarioArrivo`,
				`pd`.`DataArrivo` AS `DataArrivo`,
				`pd`.`ComunePartenza` AS `ComunePartenza`,
				`pd`.`ComuneArrivo` AS `ComuneArrivo`,
				(CASE
					WHEN (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') THEN _utf8'CS'
					ELSE _utf8'A/R'
				END) AS `TipoViaggio`,
				(CASE
					WHEN (`pd`.`TipoViaggio` = _latin1'Corsa Semplice') THEN ROUND(`pd`.`Importo`, 2)
					ELSE ROUND((`pb`.`PrezzoPax` + ((`pb`.`AumentoPax` - `pb`.`RiduzionePax`) / `pb`.`NumeroPax`)), 2)
				END) AS `Importo`,
				`pd`.`CorsaId` AS `CorsaId`,
				`pd`.`DataPartenza` AS `DataPartenza`,
				`pd`.`PrenotazioneDettaglioId` AS `PrenotazioneDettaglioId`,
				`pd`.`DataInizioItinerario` AS `DataInizioItinerario`,
				`p`.`PrenotazioneId` AS PrenotazioneId,
				`p`.`DataIns` AS DataIns,
				`p`.`Libera` AS Libera,
				`pd`.`LineaId` AS `LineaId`,
				l.TipoTour AS TipoTour,
				pc.ValidatoMolo
			FROM
				(((((((`RT_PrenotazioneDettaglio` `pd`
				LEFT JOIN `RT_Prenotazione` `p` ON ((`pd`.`PrenotazioneId` = `p`.`PrenotazioneId`)))
				LEFT JOIN `RT_AppPrenotazioneStato` `apps` ON ((`p`.`PrenotazioneStato` = `apps`.`PrenotazioneStatoId`)))
				LEFT JOIN `Operatore` `o` ON ((`p`.`OpeIns` = `o`.`OperatoreId`)))
				LEFT JOIN `Gestore` `g` ON ((`o`.`GestoreId` = `g`.`GestoreId`)))
				LEFT JOIN `RT_PrenotazioneDettaglioCompleto` `rdc` ON (((`p`.`PrenotazioneId` = `rdc`.`PrenotazioneId`)
					AND (`rdc`.`PrenotazioneId` = `pd`.`PrenotazioneId`)
					AND (`rdc`.`PrenotazioneNumero` = `pd`.`PrenotazioneNumero`)
					AND (`rdc`.`DataPartenza` = `pd`.`DataPartenza`))))
				LEFT JOIN `RT_PrenotazioneBiglietto` `pb` ON (((`pb`.`PrenotazioneId` = `p`.`PrenotazioneId`)
					AND (`pb`.`TipologiaBiglietto` = `pd`.`TipologiaBiglietto`))))
				LEFT JOIN `RT_TipologiaBiglietto` `tp` ON (((`tp`.`TipologiaBigliettoId` = `pb`.`TipologiaBigliettoId`))))
				LEFT JOIN RT_Linea l ON l.LineaId = pd.LineaId
				LEFT JOIN RT_PrenotazionePercorso pc ON (pc.PrenotazioneId = p.PrenotazioneId AND pc.Direzione = 'R')
			WHERE
				(((`p`.`PrenotazioneStato` = 3)
					OR (`p`.`PrenotazioneStato` = 1)
					OR (`p`.`PrenotazioneStato` = 2))
					AND (`pd`.`Escludi` <> 1)
					AND (`pd`.`Rimborso` <> 1))
					AND `tp`.`OccupaPosto` = 1
					AND (`rdc`.Codice NOT LIKE 'E-%' OR `rdc`.Codice IS NULL)
					AND `pd`.`CorsaId` = $CorsaIdRitorno
					AND `pd`.`DataPartenza` = '$DataPartenzaRitorno'
					AND `p`.`PrenotazioneId` = $PrenotazioneId";
			$prenotazioneRitorno = $db->query_first($sqlPrenotazioni);

			$dateTime = new DateTime($prenotazioneRitorno['OrarioPartenza']);
			$formattedOrarioPartenzaRitorno = $dateTime->format('H:i');

			$dateTime = new DateTime($prenotazioneRitorno['OrarioArrivo']);
			$formattedOrarioArrivoRitorno = $dateTime->format('H:i');
		}
		
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
			<div class="bg-sea confirm-title">
				<h2>Prenotazione Confermata</h2>
				<div class="operator">
					Ecco i riferimenti della<br>
					prenotazione validata.
				</div>
			</div>
			<div class="content">
				<div class="info-container dark-bg-container">

					<?php
					if (isset($DataPartenzaRitorno) && isset($CorsaIdRitorno)) { ?>
						<div class="info">
							<div class="info-title"><b>- Andata -</b></div>
							<?php if (isset($messaggioAndata)) { ?>
								<div class="info-message">
									<?= $messaggioAndata ?>
								</div>
							<?php } ?>
						</div>
					<?php } ?>
					<div class="info">
						<div class="info-title">Nome Tour:</div>
						<div class="info-value">
							<b><?= $corsa['LineaNome'] ?>
								<br>
								<?= $corsa['CorsaNome'] ?></b>
						</div>
					</div>
					<div class="info">
						<div class="info-title">Data/Orario:</div>
						<div class="info-value"><?= $corsa['DataPartenzaFormattata'] ?> <?= $formattedOrarioPartenza ?> - <?= $formattedOrarioArrivo ?></div>
					</div>
					<?php
					if (isset($DataPartenzaRitorno) && isset($CorsaIdRitorno)) { ?>
						<div class="info">
							<div class="info-title"><b>- Ritorno -</b></div>
							<?php if (isset($messaggioRitorno)) { ?>
								<div class="info-message">
									<?= $messaggioRitorno ?>
								</div>
							<?php } ?>
						</div>
						<div class="info">
							<div class="info-title">Nome Tour:</div>
							<div class="info-value">
								<b><?= $corsaRitorno['LineaNome'] ?>
									<br>
									<?= $corsaRitorno['CorsaNome'] ?></b>
							</div>
						</div>
						<div class="info">
							<div class="info-title">Data/Orario:</div>
							<div class="info-value"><?= $corsaRitorno['DataPartenzaFormattata'] ?> <?= $formattedOrarioPartenzaRitorno ?> - <?= $formattedOrarioArrivoRitorno ?></div>
						</div>
					<? }
					?>
					<div class="info">
						<div class="info-title">Prenotazione / Biglietto:</div>
						<div class="info-value"><?= $prenotazione['NumeroBiglietto'] ?></div>
					</div>

					<div class="info">
						<div class="info-title">Stato:</div>
						<div class="info-value"><?= $prenotazione['StatoPrenotazione'] ?></div>
						<?php
						// Stato validazione ANDATA
						if ($prenotazione['ValidatoMolo'] == 1) { ?>
							<div class="info-value info-contact text-dark-green"><i class="fa fa-check" aria-hidden="true"></i> Validato al molo (Andata)</div>
						<?php } else { ?>
							<div class="info-value info-contact text-warning"><i class="fa fa-times" aria-hidden="true"></i> Non validato al molo (Andata)</div>
						<?php }
						// Stato validazione RITORNO (se presente)
						if (isset($prenotazioneRitorno) && isset($prenotazioneRitorno['ValidatoMolo'])) {
							if ($prenotazioneRitorno['ValidatoMolo'] == 1) { ?>
								<div class="info-value info-contact text-dark-green"><i class="fa fa-check" aria-hidden="true"></i> Validato al molo (Ritorno)</div>
							<?php } else { ?>
								<div class="info-value info-contact text-warning"><i class="fa fa-times" aria-hidden="true"></i> Non validato al molo (Ritorno)</div>
							<?php }
						}
						?>
					</div>

					<div class="info">
						<div class="info-title">Cliente:</div>
						<div class="info-value"><?= $prenotazione['Cliente'] ?></div>
						<?php if (isset($prenotazione['ClienteCellulare']) && $prenotazione['ClienteCellulare'] != '') { ?>
							<div class="info-value info-contact"><a class="white" href="tel:<?= str_replace(' ', '', $prenotazione['ClienteCellulare']) ?>"><i class="fa fa-phone" aria-hidden="true"></i> <?= $prenotazione['ClienteCellulare'] ?></a></div>
						<?php } ?>
						<?php if (isset($prenotazione['ClienteWhatsapp']) && $prenotazione['ClienteWhatsapp'] != '' && $prenotazione['ClienteWhatsapp'] != '+39 ') { ?>
							<div class="info-value info-contact"><a class="white" target="_blank" href="https://wa.me/<?= str_replace(' ', '', $prenotazione['ClienteWhatsapp']) ?>"><img class="iconImage" src="css/imgs/whatsapp_logo.png"> <?= $prenotazione['ClienteWhatsapp'] ?></a></div>
						<?php } ?>
						<?php if (isset($prenotazione['ClienteEmail']) && $prenotazione['ClienteEmail'] != '') { ?>
							<div class="info-value info-contact"><a class="white" target="_blank" href="mailto:<?= str_replace(' ', '', $prenotazione['ClienteEmail']) ?>"><i class="fa fa-envelope-o" aria-hidden="true"></i> <?= $prenotazione['ClienteEmail'] ?></a></div>
						<?php } ?>
					</div>

					<?php
					if (count($passeggeriBiglietto) > 0) { ?>
						<div class="info">
							<div class="info-title">Passeggeri:</div>
							<?php foreach ($passeggeriBiglietto as $p) {
								echo "<div class='info-value'>- x" . $p['NumeroPax'] . " " . $p['TipologiaBiglietto'] . "</div>";
							} ?>
						</div>
					<?php } ?>

					<?php
					if (count($serviziBiglietto) > 0) { ?>
						<div class="info">
							<div class="info-title">Servizi:</div>
							<?php foreach ($serviziBiglietto as $p) {
								echo "<div class='info-value'>- x" . $p['NumeroPax'] . " " . $p['TipologiaBiglietto'] . "</div>";
							} ?>
						</div>
					<?php } ?>

					<!-- PAGAMENTO -->
					<div class="info">
						<div class="info-title">Pagamento:</div>
						<div class="info-value">
							<?php
							// Visualizza stato pagamento
							if (isset($prenotazione['TotaleResiduo']) && floatval($prenotazione['TotaleResiduo']) == 0) {
								echo "PAGATO";
							} else {
								echo "DA PAGARE<";
							}
							?>
							<br>
							<?php
							// Visualizza importo totale da pagare formattato in euro
							if (isset($prenotazione['TotaleDaPagare'])) {
								echo "Totale: " . number_format($prenotazione['TotaleDaPagare'], 2, ',', '.') . " &euro;";
							}
							?>
						</div>
					</div>
					<div class="info">
						<div class="info-title">Data prenotazione:</div>
						<?php $dateTime = new DateTime($prenotazione['DataIns']);
						$formattedDate = $dateTime->format('d/m/Y');
						$formattedTime = $dateTime->format('H:i'); ?>
						<div class="info-value"><?= $formattedDate . ' - ' . $formattedTime ?></div>
					</div>
				</div>

				<?php if( $DataPartenza >= date('Y-m-d')) { ?>
					<form method="post" action="new-ticket.php" id="search">

						<input type="hidden" name="PrenotazioneId" id="prenotazioneId" value="<?= $PrenotazioneId ?>">
						<input type="hidden" name="CorsaId" id="corsaId" value="<?= $CorsaId ?>">
						<input type="hidden" name="DataPartenza" id="dataPartenza" value="<?= $DataPartenza ?>">
						<input type="hidden" name="tipo_tour" id="tipo_tour" value="<?= $prenotazione['TipoTour'] ?>">
						<input type="hidden" name="seleziona_lesperienza" id="seleziona_lesperienza" value="<?= $prenotazione['LineaId'] ?>">
						
						<input type="hidden" name="CorsaIdR" id="corsaIdR" value="<?php isset($prenotazioneRitorno['CorsaId']) ?  $prenotazioneRitorno['CorsaId'] : '' ?>">
						<input type="hidden" name="DataPartenzaR" id="dataPartenzaR" value="<?= isset($prenotazioneRitorno['DataPartenza']) ? $prenotazioneRitorno['DataPartenza'] : '' ?>">

						<input type="hidden" name="prenotazione_libera" id="prenotazione_libera" value="<?= $prenotazione['Libera'] ?>">	
						<input type="hidden" name="TitoloLibera" id="TitoloLibera" value="<?= $prenotazione['LiberaTitolo'] ?>">
						<input type="hidden" name="OrarioPartenzaLibera" id="OrarioPartenzaLibera" value="<?= $formattedOrarioPartenza ?>">
						<input type="hidden" name="OrarioArrivoLibera" id="OrarioArrivoLibera" value="<?= $formattedOrarioArrivo ?>">
						<input type="hidden" name="BarcaLibera" id="BarcaLibera" value="<?= $corsa['FlottaDefaultId'] ?>">
						<input type="hidden" name="LiberaImporto" id="LiberaImporto" value="<?= $biglietto['PrezzoTotalePax'] ?>">
											
						
						<button type="submit" class="btn btn-big btn-primary button-action">MODIFICA TICKET</button>

					</form>
				<?php } ?>

				<?php
				// Bottone valida e marca ticket ANDATA (rosso)
				$oggi = date('Y-m-d');
				if ($prenotazione['ValidatoMolo'] == 0 && $prenotazione['DataPartenza'] <= $oggi) {
				?>
				<form method="post" action="<?php echo Config::$UrlMobile; ?>" id="valida-andata-form">
					<input type="hidden" name="prenotazioneId" value="<?= $PrenotazioneId ?>">
					<input type="hidden" name="corsaId" value="<?= $CorsaId ?>">
					<input type="hidden" name="dataPartenza" value="<?= $DataPartenza ?>">
					<input type="hidden" name="prenotazioneTitoloId" value="<?= $prenotazioneTitoloId ?>">
					<input type="hidden" name="action" value="validaAndata">
					<button type="submit" class="btn btn-big btn-danger button-action">VALIDA BIGLIETTO ANDATA</button>
				</form>
				<?php }

				// Bottone valida e marca ticket RITORNO (rosso)
				if (isset($prenotazioneRitorno) && isset($prenotazioneRitorno['ValidatoMolo']) && isset($prenotazioneRitorno['DataPartenza'])) {
					if ($prenotazioneRitorno['ValidatoMolo'] == 0 && $prenotazioneRitorno['DataPartenza'] <= $oggi) {
				?>
				<form method="post" action="<?php echo Config::$UrlMobile; ?>" id="valida-ritorno-form">
					<input type="hidden" name="prenotazioneId" value="<?= $PrenotazioneId ?>">
					<input type="hidden" name="corsaId" value="<?= $prenotazioneRitorno['CorsaId'] ?>">
					<input type="hidden" name="dataPartenza" value="<?= $prenotazioneRitorno['DataPartenza'] ?>">
					<input type="hidden" name="prenotazioneTitoloId" value="<?= $prenotazioneTitoloId ?>">
					<input type="hidden" name="action" value="validaRitorno">
					<button type="submit" class="btn btn-big btn-danger button-action">VALIDA BIGLIETTO RITORNO</button>
				</form>
				<?php }
				}
				?>

				<div class="modal" id="modal-valida-andata" style="display:none;">
					<div class="modal-content">
						<div class="ticket-card">
							<div class="modal-title">Validazione Ticket</div>
							<p id="valida-andata-msg"></p>
							<div class="modal-actions" style="text-align:center;">
								<button class="btn btn-big btn-primary" id="close-valida-andata-modal" style="width: 50%;">
									<span>OK</span>
								</button>
							</div>
						</div>
					</div>
				</div>
				<div class="modal" id="modal-valida-ritorno" style="display:none;">
					<div class="modal-content">
						<div class="ticket-card">
							<div class="modal-title">Validazione Ticket</div>
							<p id="valida-ritorno-msg"></p>
							<div class="modal-actions" style="text-align:center;">
								<button class="btn btn-big btn-primary" id="close-valida-ritorno-modal" style="width: 50%;">
									<span>OK</span>
								</button>
							</div>
						</div>
					</div>
				</div>

				<form method="post" action="<?php echo Config::$UrlMobile; ?>" id="search" target="_blank">
					<input type="hidden" name="prenotazioneId" id="prenotazioneId" value="<?= $PrenotazioneId ?>">
					<input type="hidden" name="corsaId" id="corsaId" value="<?= $CorsaId ?>">
					<input type="hidden" name="dataPartenza" id="dataPartenza" value="<?= $DataPartenza ?>">
					<input type="hidden" name="prenotazioneTitoloId" id="prenotazioneTitoloId" value="<?= $prenotazioneTitoloId ?>">
					<input type="hidden" name="action" id="action" value="stampaCartaceoPdf">
					<button type="submit" class="btn btn-big btn-primary button-action">STAMPA TICKET</button>
				</form>

				<form method="post" action="<?php echo Config::$UrlMobile; ?>" id="search" target="_blank">

					<input type="hidden" name="prenotazioneId" id="prenotazioneId" value="<?= $PrenotazioneId ?>">
					<input type="hidden" name="corsaId" id="corsaId" value="<?= $CorsaId ?>">
					<input type="hidden" name="dataPartenza" id="dataPartenza" value="<?= $DataPartenza ?>">
					<input type="hidden" name="prenotazioneTitoloId" id="prenotazioneTitoloId" value="<?= $prenotazioneTitoloId ?>">
					<input type="hidden" name="action" id="action" value="stampaPdf">
					<button type="submit" class="btn btn-big btn-primary button-action">STAMPA TICKET DIGITALE</button>

				</form>

				<form method="post" action="index.php" id="search">

					<input type="hidden" name="DataPartenza" id="DataPartenza" value="<?= $DataPartenza ?>">

					<button type="button" class="btn btn-big btn-primary button-action" id="inviaTicketEmail">
						INVIA TICKET A CLIENTE</button>
					<button type="button" class="btn btn-big btn-secondary button-action" onclick="window.location.href='tour.php?DataPartenza=<?= $DataPartenza ?>&CorsaId=<?= $CorsaId ?>';">TORNA AL TOUR</button>
					<button type="submit" class="btn btn-big btn-secondary button-action">TORNA ALLA HOME</button>

				</form>




			</div>
		</div>

		<div class="modal" id="modal-invio">
			<div class="modal-content">
				<div class="ticket-card">
					<div class="modal-title">Ticket Inviato!</div>
					<p>Il ticket è stato inviato all'email del ciente in formato digitale.</p>
					<div class="modal-actions" style="text-align:center;">
						<button class="btn btn-big btn-black" data-dismiss="modal">
							<span>Procedi</span>
						</button>
					</div>
				</div>
			</div>
		</div>


		<?php include_once("app.php"); ?>
		<script type="text/javascript">
			$(document).ready(function() {

				$("#inviaTicketEmail").click(function() {
					inviaTicketEmail(<?= $PrenotazioneId ?>);
				});

				$('#logout').click(function() {
					var formData = {
						action: "logoutBrowser",
					};
					$.ajax({
						url: '<?php echo Config::$UrlMobile; ?>',
						type: "POST",
						data: formData,
						dataType: 'json',
						success: function(responce) {
							window.location = "./login.php";
						},
						error: function(xhr, ajaxOptions, thrownError) {
						},
					});
				});

				// AJAX submit for valida-andata-form
				$('#valida-andata-form').submit(function(e) {
					e.preventDefault();
					var form = $(this);
					$.ajax({
						url: form.attr('action'),
						type: 'POST',
						data: form.serialize(),
						dataType: 'json',
						success: function(response) {
							if (response && response.result === true) {
								$('#valida-andata-msg').text('Biglietto andata validato con successo!');
							} else {
								$('#valida-andata-msg').text('Errore nella validazione del biglietto andata.');
							}
							$('#modal-valida-andata').show();
						},
						error: function() {
							$('#valida-andata-msg').text('Errore di comunicazione con il server.');
							$('#modal-valida-andata').show();
						}
					});
				});
				// Close modal and reload page
				$('#close-valida-andata-modal').click(function() {
					$('#modal-valida-andata').hide();
					location.reload();
				});

				// AJAX submit for valida-ritorno-form
				$('#valida-ritorno-form').submit(function(e) {
					e.preventDefault();
					var form = $(this);
					$.ajax({
						url: form.attr('action'),
						type: 'POST',
						data: form.serialize(),
						dataType: 'json',
						success: function(response) {
							if (response && response.result === true) {
								$('#valida-ritorno-msg').text('Biglietto ritorno validato con successo!');
							} else {
								$('#valida-ritorno-msg').text('Errore nella validazione del biglietto ritorno.');
							}
							$('#modal-valida-ritorno').show();
						},
						error: function() {
							$('#valida-ritorno-msg').text('Errore di comunicazione con il server.');
							$('#modal-valida-ritorno').show();
						}
					});
				});
				// Close modal and reload page
				$('#close-valida-ritorno-modal').click(function() {
					$('#modal-valida-ritorno').hide();
					location.reload();
				});

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
			});
		</script>




	</body>

	</html>
<?php
	$db->close();
}




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
}
// se l'utente non Ã¨ loggato
else {
	header('Location: ./login.php');
}
?>