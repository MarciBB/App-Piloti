<?php
/**
 * Description of class
 *
 * @author L.Casaburi (Braincomputing)
 */
class ServiceGoogleCalendar  {

	public $conn;

	function __construct($conn) {
	    $this->conn = $conn;
	}
	
	public function getCalendarFlotta(){
		$sql = "SELECT * FROM GoogleCalendarFlotta c
		LEFT JOIN RT_Flotta f ON f.FlottaId = c.FlottaId
		WHERE f.Stato = 1 and f.Cancella = 0";
		$rows =  $this->conn->fetch_array($sql);
		
		return $rows;
	}
	
	public function createCalendarsForFlotta() {
		ini_set('display_errors', 0);
		ini_set('error_reporting', E_ALL);

		// URL con il token
		$url = Config::$servicesUrl . 'googlecalendar/createCalendarsForFlotta?token=' . Config::$servicesToken;

		// Inizializza cURL
		$ch = curl_init();

		// Imposta le opzioni di cURL
		curl_setopt($ch, CURLOPT_URL, $url);                  // URL da chiamare
		curl_setopt($ch, CURLOPT_POST, true);                 // Metodo POST
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);       // Restituisci il risultato come stringa
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);                // Timeout opzionale
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(           // Header HTTP
			'Content-Type: application/x-www-form-urlencoded'
		));
		
		 // Ignora la verifica SSL (non raccomandato per produzione)
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		// Esegui la richiesta cURL
		$result = curl_exec($ch);

		// Verifica se ci sono errori
		if (curl_errno($ch)) {
			// Ottieni il messaggio di errore
			$error_msg = curl_error($ch);
			curl_close($ch); // Chiudi la sessione cURL
			return 'Errore nella richiesta: ' . $error_msg;
		}

		// Chiudi la sessione cURL
		curl_close($ch);

		// Restituisci il risultato
		return $result;
	}
	
	public function fetchCorsaEvents() {
		ini_set('display_errors', 0);
		ini_set('error_reporting', E_ALL);

		// URL con il token
		$url = Config::$servicesUrl . 'googlecalendar/fetchCorsaEvents?token=' . Config::$servicesToken;

		// Inizializza cURL
		$ch = curl_init();

		// Imposta le opzioni di cURL
		curl_setopt($ch, CURLOPT_URL, $url);                  // URL da chiamare
		curl_setopt($ch, CURLOPT_POST, true);                 // Metodo POST
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);       // Restituisci il risultato come stringa
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);                // Timeout opzionale
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(           // Header HTTP
			'Content-Type: application/x-www-form-urlencoded'
		));

		// Disattiva la verifica SSL (non raccomandato in produzione)
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		// Esegui la richiesta cURL
		$result = curl_exec($ch);

		// Verifica se ci sono errori
		if (curl_errno($ch)) {
			// Ottieni il messaggio di errore
			$error_msg = curl_error($ch);
			curl_close($ch); // Chiudi la sessione cURL
			return 'Errore nella richiesta: ' . $error_msg;
		}

		// Chiudi la sessione cURL
		curl_close($ch);

		// Restituisci il risultato
		return $result;
	}
	
	public function fetchCorsaEventsDay() {
		ini_set('display_errors', 0);
		ini_set('error_reporting', E_ALL);

		// URL con il token
		$url = Config::$servicesUrl . 'googlecalendar/fetchCorsaEventsDay?token=' . Config::$servicesToken;

		// Inizializza cURL
		$ch = curl_init();

		// Imposta le opzioni di cURL
		curl_setopt($ch, CURLOPT_URL, $url);                  // URL da chiamare
		curl_setopt($ch, CURLOPT_POST, true);                 // Metodo POST
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);       // Restituisci il risultato come stringa
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);                // Timeout opzionale
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(           // Header HTTP
			'Content-Type: application/x-www-form-urlencoded'
		));

		// Disattiva la verifica SSL (non raccomandato in produzione)
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		// Esegui la richiesta cURL
		$result = curl_exec($ch);

		// Verifica se ci sono errori
		if (curl_errno($ch)) {
			// Ottieni il messaggio di errore
			$error_msg = curl_error($ch);
			curl_close($ch); // Chiudi la sessione cURL
			return 'Errore nella richiesta: ' . $error_msg;
		}

		// Chiudi la sessione cURL
		curl_close($ch);

		// Restituisci il risultato
		return $result;
	}
}
?>
