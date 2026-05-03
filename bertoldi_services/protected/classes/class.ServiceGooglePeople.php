<?php
$basepath=$_SERVER['DOCUMENT_ROOT'].'/protected/classes/google-api-php-client';
require $basepath . '/vendor/autoload.php';

/**
 * Description of class
 *
 * @author L.Casaburi (MDT Software)
 */
class ServiceGooglePeople {

    public $conn;

    function __construct($conn) {
        $this->conn = $conn;
    }
	
	private function getClient() {
		$basepath = $_SERVER['DOCUMENT_ROOT'].'/protected/classes/google-api-php-client';

		// Inizializza il client di Google
		$client = new Google_Client();
		$client->setApplicationName('Bertoldi Boats');
		
		// Aggiungi i permessi per Google Calendar e Google People
		$client->setScopes([
			Google_Service_PeopleService::CONTACTS,
			Google_Service_PeopleService::CONTACTS_READONLY,
			Google_Service_Calendar::CALENDAR,  // Permessi per Google Calendar
			Google_Service_Calendar::CALENDAR_EVENTS
		]);

		// Usa il file del service account JSON
		$client->setAuthConfig($basepath . '/service-account.json');
		
		// Specifica che è un accesso offline
		$client->setAccessType('offline');

		// Delega l'utente per conto del quale vuoi agire
		$client->setSubject('bertoldi-boats@bertoldi-boats-gestionale.iam.gserviceaccount.com');

		// Restituisci il client configurato
		return $client;
	}
	
	public function deleteAllCalendars() {
		$client = $this->getClient();
		$service = new Google_Service_Calendar($client);

		// Recupera tutti i calendari
		$calendarsList = $service->calendarList->listCalendarList();

		// Cicla attraverso tutti i calendari e li elimina
		foreach ($calendarsList->getItems() as $calendar) {
			try {
				$service->calendars->delete($calendar->getId());
			} catch (Google_Service_Exception $e) {
				echo 'Errore durante l\'eliminazione del calendario: ' . $e->getMessage() . "\n";
			}
		}
	}
	
	public function getCalendarsList() {
		$client = $this->getClient();
		$service = new Google_Service_Calendar($client);

		// Richiedi la lista dei calendari
		$calendarList = $service->calendarList->listCalendarList();

		// Prepara un array per contenere le informazioni sui calendari
		$calendars = [];

		// Scorri attraverso i calendari e raccogli l'ID e l'URL
		foreach ($calendarList->getItems() as $calendarListEntry) {
			$calendarId = $calendarListEntry->getId();
			$calendarSummary = $calendarListEntry->getSummary();
			$addCalendarUrl = 'https://calendar.google.com/calendar/u/0/r?cid=' . urlencode($calendarId);
			$calendarUrl = 'https://calendar.google.com/calendar/embed?src=' . urlencode($calendarId);
		
			$calendars[] = [
				'summary' => $calendarSummary,
				'id' => $calendarId,
				'addCalendarUrl' => $addCalendarUrl,
				'calendarUrl' => $calendarUrl
			];
		}

		// Restituisci la lista dei calendari (summary, id, url)
		return $calendars;
	}

    // Metodo per creare un nuovo calendario
	public function createCalendar($calendarName) {
		$client = $this->getClient();
		$service = new Google_Service_Calendar($client);

		$calendar = new Google_Service_Calendar_Calendar();
		$calendar->setSummary($calendarName);
		$calendar->setTimeZone('Europe/Rome');

		// Crea il calendario
		$createdCalendar = $service->calendars->insert($calendar);

		// Ottieni l'ID del calendario creato
		$calendarId = $createdCalendar->getId();

		// Rendi il calendario accessibile a tutti
		$rule = new Google_Service_Calendar_AclRule();
		$rule->setRole('reader'); // Ruolo "reader" per accesso in sola lettura
		$rule->setScope(new Google_Service_Calendar_AclRuleScope(['type' => 'default'])); // Modificato da 'public' a 'default'

		// Inserisci la regola di accesso
		try {
			$service->acl->insert($calendarId, $rule);
		} catch (Google_Service_Exception $e) {
			throw new Exception('Error adding ACL rule: ' . $e->getMessage());
		}

		// Restituisci l'URL per visualizzare il calendario su Google Calendar
		$return['idCalendar'] = $calendarId;
		$return['addCalendarUrl'] = 'https://calendar.google.com/calendar/u/0/r?cid=' . urlencode($calendarId);
		$return['calendarUrl'] = 'https://calendar.google.com/calendar/embed?src=' . urlencode($calendarId);
		
		return $return;
	}


	// Metodo per aggiungere un evento a un calendario
	public function addEventToCalendar($calendarId, $eventName, $description, $startDateTime, $endDateTime) {
		$client = $this->getClient();
		$service = new Google_Service_Calendar($client);

		$event = new Google_Service_Calendar_Event([
			'summary' => $eventName,
			'description' => $description,
			'start' => [
				'dateTime' => $startDateTime,
				'timeZone' => 'Europe/Rome',
			],
			'end' => [
				'dateTime' => $endDateTime,
				'timeZone' => 'Europe/Rome',
			],
		]);

		// Inserisci l'evento nel calendario specificato
		$event = $service->events->insert($calendarId, $event);

		// Restituisci l'ID dell'evento creato
		return $event->getId();
	}
	
	public function deleteEvent($calendarId, $eventId) {
		$client = $this->getClient();  // Usa il client Google per autenticarsi
		$service = new Google_Service_Calendar($client);

		try {
			// Cancella l'evento specificato
			$service->events->delete($calendarId, $eventId);
			return "ok";
		} catch (Google_Service_Exception $e) {
			// Restituisce un messaggio di errore se la cancellazione fallisce
			return 'Errore: ' . $e->getMessage();
		}
	}
	
	
	public function deleteAllEvents($calendarId) {
		$client = $this->getClient();  // Usa il client Google per autenticarsi
		$service = new Google_Service_Calendar($client);

		try {
			// Ottieni tutti gli eventi del calendario
			$events = $service->events->listEvents($calendarId);
			
			// Verifica se ci sono eventi nel calendario
			if (count($events->getItems()) == 0) {
				return "Nessun evento trovato nel calendario.";
			}
			
			// Scorri tutti gli eventi e cancellali
			foreach ($events->getItems() as $event) {
				$eventId = $event->getId();
				$service->events->delete($calendarId, $eventId);
			}

			return "ok";
		} catch (Google_Service_Exception $e) {
			// Restituisce un messaggio di errore se la cancellazione fallisce
			return 'Errore: ' . $e->getMessage();
		}
	}
	
	public function getEventsInDateRange($calendarId, $startDateTime, $endDateTime) {
		$client = $this->getClient();  // Usa il client Google per autenticarsi
		$service = new Google_Service_Calendar($client);

		try {
			// Impostare i parametri per la query: data di inizio e fine intervallo
			$optParams = [
				'timeMin' => $startDateTime,
				'timeMax' => $endDateTime,
				'singleEvents' => true,  // Per ottenere gli eventi singoli e ricorrenti
				'orderBy' => 'startTime'  // Ordina gli eventi in base all'orario di inizio
			];

			// Ottenere gli eventi nel range di tempo specificato
			$events = $service->events->listEvents($calendarId, $optParams);

			// Creare un array per memorizzare gli eventi
			$eventArray = [];

			// Scorrere gli eventi e aggiungerli all'array
			foreach ($events->getItems() as $event) {
				$eventData = [
					'id' => $event->getId(),
					'summary' => $event->getSummary(),
					'description' => $event->getDescription(),
					'start' => $event->getStart()->getDateTime() ?: $event->getStart()->getDate(),
					'end' => $event->getEnd()->getDateTime() ?: $event->getEnd()->getDate(),
					'location' => $event->getLocation()
				];

				$eventArray[] = $eventData;
			}

			return $eventArray;
		} catch (Google_Service_Exception $e) {
			// Gestione degli errori
			return 'Errore: ' . $e->getMessage();
		}
	}



    // Esistente: Controlla se una persona esiste nel database tramite email
    public function existPeopleByEmail($email){
        $sql = "SELECT * FROM GooglePeople WHERE Email = '$email'";
        $row = $this->conn->query_first($sql);

        if(isset($row['Email']) && $row['Email'] != ''){
            return true;
        } else {
            return false;
        }
    }

    // Esistente: Controlla se una persona esiste nel database tramite cellulare
    public function existPeopleByCellulare($cellulare){
        $sql = "SELECT * FROM GooglePeople WHERE Cellulare = '$cellulare'";
        $row = $this->conn->query_first($sql);

        if(isset($row['Cellulare']) && $row['Cellulare'] != ''){
            return true;
        } else {
            return false;
        }
    }

    // Esistente: Inserisce una persona nel database e nei contatti Google People
    public function insertPeople($nome, $cognome, $email, $cellulare){
        if(!$this->existPeopleByEmail($email) || !$this->existPeopleByCellulare($cellulare)) {
            $client = $this->getClient();
            $service = new Google_Service_PeopleService($client);

            $person = new Google_Service_PeopleService_Person();

            $name = new Google_Service_PeopleService_Name();
            $name->setGivenName($nome);
            $name->setFamilyName($cognome);
            $person->setNames([$name]);

            $email1 = new Google_Service_PeopleService_EmailAddress();
            $email1->setValue($email);
            $person->setEmailAddresses([$email1]);

            $phone1 = new Google_Service_PeopleService_PhoneNumber();
            $phone1->setValue('+'.$cellulare);
            $phone1->setType('home');
            $person->setPhoneNumbers([$phone1]);

            $service->people->createContact($person);

            $sql = "INSERT INTO GooglePeople (Email, Cellulare) VALUES
                    ('".$email."','+".$cellulare."')";
            $this->conn->query($sql);
        }
    }
}
?>
