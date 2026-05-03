<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include($basepath."/main_include.php");

include_once ($classespath_ . "/class.ServiceGooglePeople.php");
$config=new Config();

$run=$config->load(); 

ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL);

$handler = explode('/', $_GET['handler']);

$token = $_GET['token'];

if (!function_exists('http_response_code')) {
	function http_response_code($newcode = NULL) {
		static $code = 200;
		if ($newcode !== NULL) {
			header('X-PHP-Response-Code: ' . $newcode, true, $newcode);
			if (!headers_sent()) {
				$code = $newcode;
			}
		}
		return $code;
	}
}

//verifica token
if($token != Config::$accessTokenAPI){
	http_response_code(404);

	echo json_encode(
			array("message" => "Token not valid.")
	);
	exit();
}

switch ($handler[0]) {
	case 'status': {
		// required headers
		header("Access-Control-Allow-Origin: *");
		header('Content-Type: application/json');
		//         header("Content-Type: application/json; charset=UTF-8");

		if ($_SERVER['REQUEST_METHOD'] === 'GET') {
			// The request is using the POST method

			// set response code - 200 OK
			http_response_code(200);

			echo json_encode(array(
					'version' => '1.0.0',
					'status' => 'OK'
			));

		} else {
			// set response code - 404 Not found
			http_response_code(404);

			echo json_encode(
					array("message" => "Method not valid.")
			);
		}

		break;
	}
	
	case 'createCalendar': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce =  createCalendar($_POST['calendar']);
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}
	
	case 'addEventToCalendar': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce = addEventToCalendar($_POST['calendarId'], $_POST['name'], $_POST['description'], $_POST['start'], $_POST['end']);
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}
	
	case 'deleteEvent': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce = deleteEvent($_POST['calendarId'], $_POST['eventId']);
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}
	
	
	case 'deleteAllCalendars': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce =  deleteAllCalendars();
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}
	
	case 'deleteAllEvents': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce =  deleteAllEvents($_POST['calendarId']);
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}


	case 'deleteEventsByRangeDate': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce =  deleteEventsByRangeDate($_POST['calendarId'], $_POST['start'], $_POST['end']);
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}
	
	case 'getEventsInDateRange': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce =  getEventsInDateRange($_POST['calendarId'], $_POST['start'], $_POST['end']);
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}
	
	case 'getCalendarsList': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce =  getCalendarsList();
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}
	
	case 'createCalendarsForFlotta': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce =  createCalendarsForFlotta();
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}
	
	case 'fetchCorsaEvents': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce =  fetchCorsaEvents();
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}
	
	case 'fetchCorsaEventsDay': {
		// required headers
		header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			// The request is using the POST method
	
			// set response code - 200 OK
			http_response_code(200);
			$responce =  fetchCorsaEvents(true);
			
			echo json_encode($responce);
	
		} else {
			// set response code - 404 Not found
			http_response_code(404);
	
			echo json_encode(
					array("message" => "Method not valid.")
			);
		}
	
		break;
	}		
	
	default: {
		// required headers
		header("Access-Control-Allow-Origin: *");
		header('Content-Type: application/json');
		//         header("Content-Type: application/json; charset=UTF-8");
	
		// set response code - 404 Not found
		http_response_code(404);
	
		echo json_encode(
				array("message" => "API not valid.")
		);
	}
}



function createCalendar($calendarName) {
	$db = new Database();
	$db->connect();
	$servicePeople = new ServiceGooglePeople($db);
	$result = $servicePeople->createCalendar($calendarName);
	
	//risultato
	$data['result'] = 'ok';
	$data['caelndar'] = $result;

	return $data;
}



function getCalendarsList() {
	$db = new Database();
	$db->connect();
	$servicePeople = new ServiceGooglePeople($db);
	$result = $servicePeople->getCalendarsList();
	
	//risultato
	$data['result'] = 'ok';
	$data['caelndars'] = $result;

	return $data;
}

function deleteAllCalendars() {
	$db = new Database();
	$db->connect();
	$servicePeople = new ServiceGooglePeople($db);
	$result = $servicePeople->deleteAllCalendars();
	
	//risultato
	$data['result'] = 'ok';

	return $data;
}

function addEventToCalendar($calendarId, $eventName, $description, $startDateTime, $endDateTime) {
	$db = new Database();
	$db->connect();
	$servicePeople = new ServiceGooglePeople($db);
	$result = $servicePeople->addEventToCalendar($calendarId, $eventName, $description, $startDateTime, $endDateTime);
	
	//risultato
	$data['result'] = 'ok';
	$data['event'] = $result;

	return $data;
}

function deleteEvent($calendarId, $eventId) {
	$db = new Database();
	$db->connect();
	$servicePeople = new ServiceGooglePeople($db);
	$result = $servicePeople->deleteEvent($calendarId, $eventId);
	
	//risultato
	$data['result'] = $result;

	return $data;
}

function deleteAllEvents($calendarId) {
	$db = new Database();
	$db->connect();
	$servicePeople = new ServiceGooglePeople($db);
	$result = $servicePeople->deleteAllEvents($calendarId);
	
	//risultato
	$data['result'] = $result;

	return $data;
}

function getEventsInDateRange($calendarId, $startDateTime, $endDateTime) {
	$db = new Database();
	$db->connect();
	$servicePeople = new ServiceGooglePeople($db);
	$result = $servicePeople->getEventsInDateRange($calendarId, $startDateTime, $endDateTime);
	
	//risultato
	$data['result'] = $result;

	return $data;
}


function createCalendarsForFlotta() {
    // Connessione al database
    $db = new Database();
    $db->connect();
    
    // Servizio per creare i calendari
    $servicePeople = new ServiceGooglePeople($db);

    // Query per ottenere la flotta attiva
    $query = "SELECT * FROM RT_Flotta WHERE Stato = 1 AND Cancella = 0";
    $flottaRecords = $db->fetch_array($query);

    // Itera sui record della flotta
    foreach ($flottaRecords as $record) {
        $flottaId = $record['FlottaId'];

        // Controlla se esiste già un record per questa flottaId nella tabella GoogleCalendarFlotta
        $checkQuery = "SELECT GoogleCalendarFlottaId FROM GoogleCalendarFlotta WHERE FlottaId = '$flottaId'";
        
		$existingRecord = $db->query_first($checkQuery);

        // Se esiste già un record, salta la creazione del calendario
        if (!empty($existingRecord)) {
            // Già esiste un calendario per questa flotta, salto la creazione
            continue;
        }

        // Nome del calendario come concatenazione di Modello e Targa
        $calendarName = $record['Modello'] . ' ' . $record['Targa'];

        // Crea il calendario su Google Calendar
        $result = $servicePeople->createCalendar($calendarName);

        // Ottieni l'ID e gli URL del calendario creato
        $calendarId = $result['idCalendar'];
        $calendarUrl = $result['calendarUrl'];
        $addCalendarUrl = $result['addCalendarUrl'];

        // Prepara i dati per l'inserimento nella tabella GoogleCalendarFlotta
        $insertQuery = "INSERT INTO GoogleCalendarFlotta (FlottaId, CalendarId, UrlCalendar, UrlAdd)
                        VALUES ('$flottaId', '$calendarId', '$calendarUrl', '$addCalendarUrl')";

        // Esegui l'inserimento nel database
        $db->query($insertQuery);
    }

    // Risultato finale
    $data['result'] = 'ok';
    $data['message'] = 'Calendari creati con successo per la flotta attiva, se non già esistenti.';
    
    return $data;
}

function fetchCorsaEvents($day = false) {
	// Connessione al database
    $db = new Database();
    $db->connect();
    
    // Servizio per creare i calendari
    $servicePeople = new ServiceGooglePeople($db);
	
	//recupero flotte e calendari
    $sql = "SELECT * FROM GoogleCalendarFlotta";
	$flottaRecords = $db->fetch_array($sql);
	$calendars = array();
	foreach($flottaRecords as $f) {
		$calendars[$f['FlottaId']]['CalendarId'] = $f['CalendarId'];
		$calendars[$f['FlottaId']]['GoogleCalendarFlottaId'] = $f['GoogleCalendarFlottaId'];
	}
	
	if($day) {
		$whereDay = " appcal.`AppCalendarioData` = DATE(NOW()) ";
	} else {
		$whereDay = " appcal.`AppCalendarioData` >= DATE(NOW() - INTERVAL 90 DAY)
						AND appcal.`AppCalendarioData` < DATE(NOW() + INTERVAL 12 MONTH) 
						AND appcal.`AppCalendarioData` <> DATE(NOW()) ";
	}
	 
	$query = "
					SELECT 
						c.`CorsaId` AS `CorsaId`,
						appcal.`AppCalendarioData` AS `AppCalendarioData`,
						c.`LineaId` AS `LineaId`,
						RT_Linea.`LineaNome` AS `LineaNome`,
						c.`OrarioPartenza` AS `OrarioPartenza`,
						c.`OrarioArrivo` AS `OrarioArrivo`,
						c.`CorsaNome` AS `CorsaNome`,
						RT_Flotta.FlottaId,
						RT_Linea.`TipoTour` AS `TipoTour`,
						(
							SELECT IFNULL((
								SELECT COUNT(0)
								FROM `RT_PrenotazionePercorso`
								JOIN `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
								JOIN `RT_PrenotazioneDettaglio` ON (
									`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
									AND `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
									AND `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`
								)
								JOIN `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
								LEFT JOIN `RT_PrenotazioneNumero` p ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
								LEFT JOIN `RT_TipologiaBiglietto` tb ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
								WHERE 
									(`RT_Prenotazione`.`Cancella` = 0)
									AND (`RT_PrenotazionePercorso`.`Cancella` = 0)
									AND (`RT_PrenotazionePercorso`.`Stato` = 1)
									AND (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
									AND (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
									AND (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
									AND (`tb`.`OccupaPosto` = 1)
									AND `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId 
									AND `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
								GROUP BY 
									`RT_PrenotazionePercorso`.`CorsaDataPartenza`, 
									`RT_PrenotazionePercorso`.`CorsaId`, 
									`RT_PrenotazionePercorso`.`OdcIdRef`
							), 0)
						) AS `PostiRealmentePrenotati`,
						
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
							 and (`RT_Prenotazione`.`PrenotazioneStato` = 3 or `RT_Prenotazione`.`PrenotazioneStato` = 1)
							 and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
							 and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
							 and (`tb`.`OccupaPosto` = 1))
							 and `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
						   group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0)
						) AS `TotalePrenotati`,
						
						(
							select IFNULL((select 
							(count(`RT_Prenotazione`.`PrenotazioneId`) - sum(`RT_PrenotazioneDettaglio`.`Escludi`)) AS `TotaleServiziPrenotati`
						   from
							`RT_PrenotazionePercorso`
							join `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
							join `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
							 and `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
							 and `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
							join `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
							left join `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBiglietto` = `RT_PrenotazioneDettaglio`.`TipologiaBiglietto`)
						   where
							((`RT_Prenotazione`.`Cancella` = 0)
							 and (`RT_PrenotazionePercorso`.`Cancella` = 0)
							 and (`RT_PrenotazionePercorso`.`Stato` = 1)
							 and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
							 and (((`RT_Prenotazione`.`PrenotazioneStato` = 3)
							 and (`RT_PrenotazioneDettaglio`.`Escludi` = 0))
							 or ((`RT_Prenotazione`.`PrenotazioneStato` = 1)
							 and (`RT_Prenotazione`.`Pagato` = 1))
							 or ((`RT_Prenotazione`.`PrenotazioneStato` = 1)
							 and (`RT_Prenotazione`.`Pagato` = 0))
							 or ((`RT_Prenotazione`.`ABordo` = 1)
							 and (`RT_Prenotazione`.`PrenotazioneStato` = 1)))
							 and (`tb`.`OccupaPosto` = 0) and tb.TipologiaBigliettoId = 23)
							 and `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
						   group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0)
						) AS `Sosta`,
						
						GoogleCalendarFlottaTour.CalendarId,
						GoogleCalendarFlottaTour.EventId,
						GoogleCalendarFlottaTour.GoogleCalendarFlottaTourId,
						GoogleCalendarFlottaTour.DataIns as DataCalendario,
						(SELECT MAX(p1.DataIns) FROM RT_Prenotazione p1
							LEFT JOIN RT_PrenotazionePercorso pp1 ON p1.PrenotazioneId = pp1.PrenotazioneId
                            WHERE pp1.CorsaId = c.CorsaId AND pp1.CorsaDataPartenza = appcal.`AppCalendarioData`
						) as DataIns,
						(SELECT MAX(p1.DataAgg) FROM RT_Prenotazione p1
							LEFT JOIN RT_PrenotazionePercorso pp1 ON p1.PrenotazioneId = pp1.PrenotazioneId 
                            WHERE pp1.CorsaId = c.CorsaId AND pp1.CorsaDataPartenza = appcal.`AppCalendarioData`
						) as DataAgg
					FROM
						`RT_Corsa` c
						JOIN `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
						JOIN `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
						JOIN `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
						JOIN `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
						JOIN `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
						JOIN `RT_Flotta` ON (RT_Flotta.`TipologiaBusId` = `RT_TipologiaBus`.`TipologiaBusId`)
						LEFT JOIN `GoogleCalendarFlottaTour` ON (GoogleCalendarFlottaTour.`CorsaId` = c.`CorsaId` 
							AND GoogleCalendarFlottaTour.AppCalendarioData = appcal.`AppCalendarioData` 
							AND GoogleCalendarFlottaTour.FlottaId = RT_Flotta.FlottaId)
					WHERE
						$whereDay 
					AND (select IFNULL((select 
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
							 and (`RT_Prenotazione`.`PrenotazioneStato` = 3 or `RT_Prenotazione`.`PrenotazioneStato` = 1)
							 and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
							 and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
							 and (`tb`.`OccupaPosto` = 1))
							 and `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
						   group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0)
						) > 0
					LIMIT %d OFFSET %d
				";
	
	// Parametri paginazione
	$batchSize = 1000; // Numero di record per batch
	$offset = 0;
	$totalProcessed = 0;
	
	// Loop per processare i dati in batch
	do {
		// Esegui la query con limit e offset
		$queryPaginated = sprintf($query, $batchSize, $offset);
		//echo $queryPaginated; die();
		$records = $db->fetch_array($queryPaginated);
		$recordCount = count($records);
		$totalProcessed += $recordCount;
		
		//echo "Batch processing: offset=$offset, count=$recordCount, total=$totalProcessed\n";
		
		foreach($records as $row) {
		$deleteEvent = false;
		if(isset($row['CalendarId']) && isset($row['EventId']) && 
			(null === $row['DataCalendario'] || 
				$row['DataCalendario'] < $row['DataIns'] || 
				(isset($row['DataAgg']) && $row['DataCalendario'] < $row['DataAgg']))) {
			deleteEvent($row['CalendarId'], $row['EventId']);
			$db->query("DELETE FROM GoogleCalendarFlottaTour WHERE GoogleCalendarFlottaTourId = ".$row['GoogleCalendarFlottaTourId']);
			$deleteEvent = true;
		}
		
		if (is_null($row['CalendarId']) || is_null($row['EventId']) || $deleteEvent) {
			
			// Calcolo ore di navigazione
			$datetimePartenza = DateTime::createFromFormat('H:i:s', $row['OrarioPartenza']);
			$datetimeArrivo = DateTime::createFromFormat('H:i:s', $row['OrarioArrivo']);
			$intervallo = $datetimePartenza->diff($datetimeArrivo);
			$ore = $intervallo->h;
			$minuti = $intervallo->i;
			
			//recupero tratta del tour di gruppo
			$sqlTratta = "SELECT f.* FROM RT_Fermata f 
							LEFT JOIN RT_Tratta t on t.TrattaId = f.TrattaId
							WHERE t.LineaId = ".$row['LineaId']." AND t.Stato = 1 AND t.Cancella = 0
								AND f.Stato = 1 AND f.Cancella = 0
							ORDER BY f.FermataPeso";
			$tratta = $db->fetch_array($sqlTratta);
			
			//recupero elenco passeggeri
			$sqlClienti = "SELECT 
								pp.PrenotazionePercorsoId,
								pp.PrenotazioneStato, 
								pp.ComuneSalita,
								pp.ComuneDiscesa,								
								p.ClienteNome,
								p.ClienteCellularePrefisso,
								p.ClienteCellulare,
								p.CodicePrenotazione,
								p.TipoTourPasseggeri,
								CASE 
									WHEN IFNULL((SELECT SUM(ImportoPagato) FROM RT_PrenotazioneMovimento 
												WHERE RT_PrenotazioneMovimento.Stato = 1
												AND RT_PrenotazioneMovimento.Cancella = 0 
												AND RT_PrenotazioneMovimento.TipoMovimento IN ('I', 'R')
												AND RT_PrenotazioneMovimento.PrenotazioneId = p.PrenotazioneId), 0) = 0
									THEN p.TotaleDaPagare
									ELSE (SELECT SUM(ImportoPagato) FROM RT_PrenotazioneMovimento 
										WHERE RT_PrenotazioneMovimento.Stato = 1
										AND RT_PrenotazioneMovimento.Cancella = 0 
										AND RT_PrenotazioneMovimento.TipoMovimento IN ('I', 'R')
										AND RT_PrenotazioneMovimento.PrenotazioneId = p.PrenotazioneId)
								END AS TotaleDaPagare,
								g.RagioneSociale as GestoreNome,
								g.GestoreId as GestoreId,
								p.LiberaTitolo,
								(p.TotalePaxPrenotati - IFNULL(
									(SELECT COUNT(0) FROM RT_PrenotazioneDettaglio 
									WHERE PrenotazioneId = p.PrenotazioneId AND (Escludi = 1 AND Rimborso = 0)
									), 0)
								) AS TotalePaxPrenotati,
								p.PrenotazioneId,
								CASE 
									WHEN pp.PrenotazioneStato = 1 THEN 'DA PAGARE'
									ELSE 'PAGATO'
								END AS Stato
								FROM RT_PrenotazionePercorso pp
								LEFT JOIN RT_Prenotazione p ON p.PrenotazioneId = pp.PrenotazioneId
								LEFT JOIN Gestore g ON g.GestoreId = p.GestoreIdRef
								WHERE 
								pp.CorsaId = ".$row['CorsaId']." 
								AND pp.CorsaDataPartenza = '".$row['AppCalendarioData']."' 
								AND pp.Direzione = 'A'
								AND (p.PrenotazioneStato = 1 OR p.PrenotazioneStato = 3)";
			$clienti = $db->fetch_array($sqlClienti);

			if(count($clienti) == 0) {
				//recupero elenco passeggeri
				$sqlClienti = "SELECT 
					pp.PrenotazionePercorsoId,
					pp.PrenotazioneStato, 
					pp.ComuneSalita,
					pp.ComuneDiscesa,								
					p.ClienteNome,
					p.ClienteCellularePrefisso,
					p.ClienteCellulare,
					p.CodicePrenotazione,
					p.TipoTourPasseggeri,
					CASE 
						WHEN IFNULL((SELECT SUM(ImportoPagato) FROM RT_PrenotazioneMovimento 
									WHERE RT_PrenotazioneMovimento.Stato = 1
									AND RT_PrenotazioneMovimento.Cancella = 0 
									AND RT_PrenotazioneMovimento.TipoMovimento IN ('I', 'R')
									AND RT_PrenotazioneMovimento.PrenotazioneId = p.PrenotazioneId), 0) = 0
						THEN p.TotaleDaPagare
						ELSE (SELECT SUM(ImportoPagato) FROM RT_PrenotazioneMovimento 
							WHERE RT_PrenotazioneMovimento.Stato = 1
							AND RT_PrenotazioneMovimento.Cancella = 0 
							AND RT_PrenotazioneMovimento.TipoMovimento IN ('I', 'R')
							AND RT_PrenotazioneMovimento.PrenotazioneId = p.PrenotazioneId)
					END AS TotaleDaPagare,
					g.RagioneSociale as GestoreNome,
					g.GestoreId as GestoreId,
					p.LiberaTitolo,
					p.TotalePaxPrenotati,
					p.PrenotazioneId,
					CASE 
						WHEN pp.PrenotazioneStato = 1 THEN 'DA PAGARE'
						ELSE 'PAGATO'
					END AS Stato
					FROM RT_PrenotazionePercorso pp
					LEFT JOIN RT_Prenotazione p ON p.PrenotazioneId = pp.PrenotazioneId
					LEFT JOIN Gestore g ON g.GestoreId = p.GestoreIdRef
					WHERE 
					pp.CorsaId = ".$row['CorsaId']." 
					AND pp.CorsaDataPartenza = '".$row['AppCalendarioData']."' 
					AND pp.Direzione = 'R'
					AND (p.PrenotazioneStato = 1 OR p.PrenotazioneStato = 3)";
					
				$clienti = $db->fetch_array($sqlClienti);
			}
			$descrizioneClienti = '';
			foreach($clienti as $c) {
				//recupero note
				$sqlNote = "SELECT * FROM RT_PrenotazionePercorsoNote WHERE PrenotazionePercorsoId = ".$c['PrenotazionePercorsoId'];
				$noteRow = $db->fetch_array($sqlNote);
				$note = '';
				if(count($noteRow) > 0 ) {
					$note = '';
					foreach($noteRow as $n){
						$note .= "\n  - ".$n['Nota'];
					}

				} else {
					$note = '-';
				}

				if($row['TipoTour'] == 1) {
					//recupero i servizi del tour privato
					$sqlServizi = "SELECT b.*
									FROM RT_PrenotazioneBiglietto b
									LEFT JOIN RT_TipologiaBiglietto t ON t.TipologiaBigliettoId = b.TipologiaBigliettoId
									WHERE
										b.PrenotazioneId = ".$c['PrenotazioneId']."
										AND t.OccupaPosto = 0";
					$serviziRows = $db->fetch_array($sqlServizi);
					$servizi = '';
					if(count($serviziRows) > 0 ) {
						foreach($serviziRows as $n){
							$servizi .= "\n  - ".$n['TipologiaBiglietto'] . " x".$n['NumeroPax'];
						}
					} else {
						$servizi = '-';
					}
				}

				if($row['TipoTour'] == 1) {
					//tour privato
					$descrizioneClienti .= "\n".$c['CodicePrenotazione']." (".$c['GestoreNome'].")\n  Cliente: ".$c['ClienteNome']."\n  Tel: +".$c['ClienteCellularePrefisso'].$c['ClienteCellulare']."\n  Servizi: $servizi\n  Note: $note\n  ".$c['Stato']." (".number_format($c['TotaleDaPagare'], 2, ',', '.')."€)\n";
				} else { 
					//tour di gruppo
					$descrizioneClienti .= "\n".$c['CodicePrenotazione']." (".$c['GestoreNome'].")\n  Cliente: ".$c['ClienteNome']."\n  Num. passeggeri: ".$c['TotalePaxPrenotati']."\n Tel: +".$c['ClienteCellularePrefisso'].$c['ClienteCellulare']."\n  Note: $note\n  ".$c['Stato']." (".number_format($c['TotaleDaPagare'], 2, ',', '.')."€)\n";
				}
			}
			
			// Aggiungi evento al calendario
			$eventName = '';
			$description = '';
			if($row['TipoTour'] == 1) {
				//tour privato
				$eventName = $clienti[0]['LiberaTitolo'];
				if(!isset($eventName) || $eventName == '') {
					if($row['LineaId'] == 14) {
						$eventName = $row['CorsaNome'];
					} else {
						$eventName = $row['LineaNome'];
					}
				}

				$eventName .= " - ";
				if($clienti[0]['GestoreId'] != 1) {
					$eventName .= " (" .$clienti[0]['GestoreNome'].") ";
				}
				$eventName .= $clienti[0]['ClienteNome'];

				$tempOraArrivo = new DateTime($row['OrarioArrivo']);
				$tempOraArrivo->modify("+".$row['Sosta']." hours");
				$tempOraArrivoS = $tempOraArrivo->format("H:i");


				
				
				$description = "Orario di partenza: ".substr($row['OrarioPartenza'], 0, 5)."\n";
				$description .= "Orario di arrivo: ".substr($tempOraArrivoS, 0, 5)."\n";
				$description .= "Ore di navigazione/sosta: ".$ore."h ".$minuti."min\n";
				//$description .= "Ore di sosta: ".$row['Sosta']."h\n";
				$description .= "Porto di partenza: ".$clienti[0]['ComuneSalita']."\n";
				$description .= "Porto di arrivo: ".$clienti[0]['ComuneDiscesa']."\n";
				$description .= "Numero di persone: ".$clienti[0]['TipoTourPasseggeri']."\n";
				$description .= "Clienti:\n".$descrizioneClienti;
				
				$oraArrivoTemp = $tempOraArrivoS;
			} else {
				
				//tour di gruppo
				if (stripos($row['CorsaNome'], 'Cambio') !== false) {
					$eventName = $row['LineaNome'] . ' - Nuovo giorno';
				} else {
					$eventName = $row['LineaNome'];
				}
				
				$description = "Orario di partenza: ".substr($row['OrarioPartenza'], 0, 5)."\n";
				$description .= "Orario di arrivo: ".substr($row['OrarioArrivo'], 0, 5)."\n";
				$description .= "Ore di navigazione/sosta: ".$ore."h ".$minuti."min\n";
				//$description .= "Ore di sosta: ".$row['Sosta']."h\n";
				$description .= "Porto di partenza: ".$clienti[0]['ComuneSalita']."\n";
				$description .= "Porto di arrivo: ".$clienti[0]['ComuneDiscesa']."\n";
				$description .= "Numero di persone: ".$row['PostiRealmentePrenotati']."\n";
				$description .= "Clienti:\n".$descrizioneClienti;
				
				$oraArrivoTemp = $row['OrarioArrivo'];
			}
			
			
			
			
			$startDateTime = new DateTime($row['AppCalendarioData'] . " " . $row['OrarioPartenza'], new DateTimeZone('Europe/Rome'));
            $start = $startDateTime->format('Y-m-d\TH:i:sP');
			$endDateTime = new DateTime($row['AppCalendarioData'] . " " . $oraArrivoTemp, new DateTimeZone('Europe/Rome'));			
            $end = $endDateTime->format('Y-m-d\TH:i:sP');
			
			$eventId = $servicePeople->addEventToCalendar($calendars[$row['FlottaId']]['CalendarId'], $eventName, $description, $start, $end);

			// Salva l'ID dell'evento nella tabella
            if ($eventId) {
                $insertQuery = "INSERT INTO GoogleCalendarFlottaTour (CorsaId, AppCalendarioData, FlottaId, GoogleCalendarFlottaId, CalendarId, EventId, DataIns)
                    VALUES (".$row['CorsaId'].", '".$row['AppCalendarioData']."', ".$row['FlottaId'].", '".$calendars[$row['FlottaId']]['GoogleCalendarFlottaId']."', '".$calendars[$row['FlottaId']]['CalendarId']."', '".$eventId."', '".date('Y-m-d H:i:s')."')";

				$db->query($insertQuery);
            }
		}
	}
	
	// Incrementa offset per il prossimo batch
	$offset += $batchSize;
	
	} while ($recordCount >= $batchSize); // Continua finché ci sono record
	
	//cancello eventi con prenotazioni annullate e 0 passeggeri
	fetchCorsaDeleteEvents($day);
	
	return "ok";
}

function fetchCorsaDeleteEvents($day = false) {
	// Connessione al database
    $db = new Database();
    $db->connect();
 
    // Servizio per creare i calendari
    $servicePeople = new ServiceGooglePeople($db);
	
	if($day) {
		$whereDay = " appcal.`AppCalendarioData` = DATE(NOW()) ";
	} else {
		$whereDay = " appcal.`AppCalendarioData` >= DATE(NOW() - INTERVAL 90 DAY)
						AND appcal.`AppCalendarioData` < DATE(NOW() + INTERVAL 12 MONTH)"; 
	}
	
	//***FASE 1: cancellazione eventi con 0 prenotazioni *****/
	$query = "		SELECT 
						c.`CorsaId` AS `CorsaId`,
						appcal.`AppCalendarioData` AS `AppCalendarioData`,
						c.`LineaId` AS `LineaId`,
						RT_Linea.`LineaNome` AS `LineaNome`,
						c.`OrarioPartenza` AS `OrarioPartenza`,
						c.`OrarioArrivo` AS `OrarioArrivo`,
						c.`CorsaNome` AS `CorsaNome`,
						RT_Flotta.FlottaId,
						RT_Linea.`TipoTour` AS `TipoTour`,
						(
							SELECT IFNULL((
								SELECT COUNT(0)
								FROM `RT_PrenotazionePercorso`
								JOIN `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
								JOIN `RT_PrenotazioneDettaglio` ON (
									`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
									AND `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
									AND `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`
								)
								JOIN `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
								LEFT JOIN `RT_PrenotazioneNumero` p ON (`RT_PrenotazioneDettaglio`.`PrenotazioneNumero` = `p`.`PrenotazioneNumeroId`)
								LEFT JOIN `RT_TipologiaBiglietto` tb ON (`tb`.`TipologiaBigliettoId` = `p`.`TipologiaBigliettoId`)
								WHERE 
									(`RT_Prenotazione`.`Cancella` = 0)
									AND (`RT_PrenotazionePercorso`.`Cancella` = 0)
									AND (`RT_PrenotazionePercorso`.`Stato` = 1)
									AND (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
									AND (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
									AND (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
									AND (`tb`.`OccupaPosto` = 1)
									AND `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId 
									AND `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
								GROUP BY 
									`RT_PrenotazionePercorso`.`CorsaDataPartenza`, 
									`RT_PrenotazionePercorso`.`CorsaId`, 
									`RT_PrenotazionePercorso`.`OdcIdRef`
							), 0)
						) AS `PostiRealmentePrenotati`,
						
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
							 and (`RT_Prenotazione`.`PrenotazioneStato` = 3 or `RT_Prenotazione`.`PrenotazioneStato` = 1)
							 and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
							 and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
							 and (`tb`.`OccupaPosto` = 1))
							 and `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
						   group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0)
						) AS `TotalePrenotati`,
						
						(
							select IFNULL((select 
							(count(`RT_Prenotazione`.`PrenotazioneId`) - sum(`RT_PrenotazioneDettaglio`.`Escludi`)) AS `TotaleServiziPrenotati`
						   from
							`RT_PrenotazionePercorso`
							join `RT_Prenotazione` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
							join `RT_PrenotazioneDettaglio` ON (`RT_PrenotazionePercorso`.`PrenotazioneId` = `RT_PrenotazioneDettaglio`.`PrenotazioneId`
							 and `RT_PrenotazioneDettaglio`.`ComunePartenza` = `RT_PrenotazionePercorso`.`ComuneSalita`
							 and `RT_PrenotazioneDettaglio`.`PrenotazioneId` = `RT_Prenotazione`.`PrenotazioneId`)
							join `RT_AppPrenotazioneStato` ON (`RT_PrenotazionePercorso`.`PrenotazioneStato` = `RT_AppPrenotazioneStato`.`PrenotazioneStatoId`)
							left join `RT_TipologiaBiglietto` `tb` ON (`tb`.`TipologiaBiglietto` = `RT_PrenotazioneDettaglio`.`TipologiaBiglietto`)
						   where
							((`RT_Prenotazione`.`Cancella` = 0)
							 and (`RT_PrenotazionePercorso`.`Cancella` = 0)
							 and (`RT_PrenotazionePercorso`.`Stato` = 1)
							 and (`RT_AppPrenotazioneStato`.`OccupaPosti` = 1)
							 and (((`RT_Prenotazione`.`PrenotazioneStato` = 3)
							 and (`RT_PrenotazioneDettaglio`.`Escludi` = 0))
							 or ((`RT_Prenotazione`.`PrenotazioneStato` = 1)
							 and (`RT_Prenotazione`.`Pagato` = 1))
							 or ((`RT_Prenotazione`.`PrenotazioneStato` = 1)
							 and (`RT_Prenotazione`.`Pagato` = 0))
							 or ((`RT_Prenotazione`.`ABordo` = 1)
							 and (`RT_Prenotazione`.`PrenotazioneStato` = 1)))
							 and (`tb`.`OccupaPosto` = 0) and tb.TipologiaBigliettoId = 23)
							 and `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
						   group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0)
						) AS `Sosta`,
						
						GoogleCalendarFlottaTour.CalendarId,
						GoogleCalendarFlottaTour.EventId,
						GoogleCalendarFlottaTour.GoogleCalendarFlottaTourId,
						GoogleCalendarFlottaTour.DataIns as DataCalendario,
						(SELECT MAX(p1.DataIns) FROM RT_Prenotazione p1
							LEFT JOIN RT_PrenotazionePercorso pp1 ON p1.PrenotazioneId = pp1.PrenotazioneId
                            WHERE pp1.CorsaId = c.CorsaId AND pp1.CorsaDataPartenza = appcal.`AppCalendarioData`
						) as DataIns,
						(SELECT MAX(p1.DataAgg) FROM RT_Prenotazione p1
							LEFT JOIN RT_PrenotazionePercorso pp1 ON p1.PrenotazioneId = pp1.PrenotazioneId 
                            WHERE pp1.CorsaId = c.CorsaId AND pp1.CorsaDataPartenza = appcal.`AppCalendarioData`
						) as DataAgg
					FROM
						`RT_Corsa` c
						JOIN `RT_CorsaSettimana` ON (c.`CorsaId` = `RT_CorsaSettimana`.`CorsaId`)
						JOIN `RT_AppSettimana` ON (`RT_CorsaSettimana`.`SettimanaId` = `RT_AppSettimana`.`AppSettimanaId`)
						JOIN `RT_AppCalendario` appcal ON (`RT_AppSettimana`.`AppSettimanaGiorno` = appcal.`GiornoSettimana`)
						JOIN `RT_Linea` ON (c.`LineaId` = `RT_Linea`.`LineaId`)
						JOIN `RT_TipologiaBus` ON (c.`TipologiaBusDefaultId` = `RT_TipologiaBus`.`TipologiaBusId`)
						JOIN `RT_Flotta` ON (RT_Flotta.`TipologiaBusId` = `RT_TipologiaBus`.`TipologiaBusId`)
						LEFT JOIN `GoogleCalendarFlottaTour` ON (GoogleCalendarFlottaTour.`CorsaId` = c.`CorsaId` 
							AND GoogleCalendarFlottaTour.AppCalendarioData = appcal.`AppCalendarioData` 
							AND GoogleCalendarFlottaTour.FlottaId = RT_Flotta.FlottaId)
					WHERE
						$whereDay 
						AND GoogleCalendarFlottaTour.GoogleCalendarFlottaId IS NOT NULL 
						AND (select IFNULL((select 
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
							 and (`RT_Prenotazione`.`PrenotazioneStato` = 3 or `RT_Prenotazione`.`PrenotazioneStato` = 1)
							 and (`RT_PrenotazioneDettaglio`.`Escludi` <> 1)
							 and (`RT_PrenotazioneDettaglio`.`Rimborso` <> 1)
							 and (`tb`.`OccupaPosto` = 1))
							 and `RT_PrenotazionePercorso`.`CorsaId` = c.CorsaId and `RT_PrenotazionePercorso`.`CorsaDataPartenza` = appcal.AppCalendarioData
						   group by `RT_PrenotazionePercorso`.`CorsaDataPartenza` , `RT_PrenotazionePercorso`.`CorsaId` , `RT_PrenotazionePercorso`.`OdcIdRef`),0)
						) = 0
				";

	$records = $db->fetch_array($query);
	foreach($records as $row) {
		if(isset($row['CalendarId']) && isset($row['EventId'])) {
			deleteEvent($row['CalendarId'], $row['EventId']);
			$db->query("DELETE FROM GoogleCalendarFlottaTour WHERE GoogleCalendarFlottaTourId = '".$row['GoogleCalendarFlottaTourId']."'");
		}
	}

	//*** FINE FASE 1 ***/
	//*** FASE 2: cancellazione eventi per corse di prenotazione libera e tour normali scambiate e non esistono più *****/

	$query = "select * from GoogleCalendarFlottaTour appcal 
				left join RT_Corsa corsa on corsa.CorsaId = appcal.CorsaId
				where 
				$whereDay AND  
				corsa.FlottaDefaultId <> appcal.FlottaId";

				echo $query;
	$records = $db->fetch_array($query);
	foreach($records as $row) {
		if(isset($row['CalendarId']) && isset($row['EventId'])) {
			deleteEvent($row['CalendarId'], $row['EventId']);
			$db->query("DELETE FROM GoogleCalendarFlottaTour WHERE GoogleCalendarFlottaTourId = '".$row['GoogleCalendarFlottaTourId']."'");
		}
	}

	//*** FINE FASE 2 ***/

	return true;
}

// Elimina tutti gli eventi di un calendario in un intervallo di date
function deleteEventsByRangeDate($calendarId, $startDateTime, $endDateTime) {
	$db = new Database();
	$db->connect();
	$servicePeople = new ServiceGooglePeople($db);

	// Recupera tutti gli eventi nel range
	$events = $servicePeople->getEventsInDateRange($calendarId, $startDateTime, $endDateTime);
	
	$deleted = [];
	$errors = [];

	if (is_array($events) && count($events) > 0) {
		foreach ($events as $event) {
			if (isset($event['id'])) {
				$result = $servicePeople->deleteEvent($calendarId, $event['id']);
				if ($result) {
					$deleted[] = $event['id'];
				} else {
					$errors[] = $event['id'];
				}
			}
		}
	}

	$data = [
		'result' => 'ok',
		'deleted' => $deleted,
		'errors' => $errors
	];
	return $data;
}