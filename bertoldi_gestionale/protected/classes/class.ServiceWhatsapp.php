<?php

/**
 * Gestione ed invio dei messaggi whatsapp
 *
 * @author L.Casaburi (MDT Software)
 */
class ServiceWhatsapp {
    public $conn;

	function __construct($conn=null) {
	    $this->conn = $conn;
	}
	
	public function invioWhatsapp($prenotazioneId, $user, $messaggio = null) {
		$sql = "SELECT ClienteCellularePrefisso, ClienteCellulare FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";
		$row =  $this->conn->query_first($sql);
		if(isset($row['ClienteCellularePrefisso']) && isset($row['ClienteCellulare'])) {
			$cell = '+'.$row['ClienteCellularePrefisso'].$row['ClienteCellulare'];
			$url = Config::$servicesUrl.'whatsapp/sendTicket?token='.Config::$servicesToken;
	
			$data = array('number' => $cell,
					'operatoreId' => $user->OperatoreId,
					'prenotazioneId' => $prenotazioneId,
			);
			if(isset($messaggio)) {
				$data['messaggio'] = $messaggio;
			}
	
			// use key 'http' even if you send the request to https://...
			$options = array(
					'http' => array(
							'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
							'method'  => 'POST',
							'content' => http_build_query($data)
					)
			);
			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);
			return $result;
		}
	}
	
	public function invioMessaggioWhatsapp($messaggio, $prenotazioneId, $user, $tipo = 'generic') {
		$sql = "SELECT ClienteCellularePrefisso, ClienteCellulare FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";
		$row =  $this->conn->query_first($sql);
		if(isset($row['ClienteCellularePrefisso']) && isset($row['ClienteCellulare'])) {
			$cell = '+'.$row['ClienteCellularePrefisso'].$row['ClienteCellulare'];
			$url = Config::$servicesUrl.'whatsapp/sendMessage?token='.Config::$servicesToken;
	
			$data = array('number' => $cell,
					'operatoreId' => $user->OperatoreId,
					'prenotazioneId' => $prenotazioneId,
					'message' => $messaggio,
					'tipo' => $tipo
			);
	
			// use key 'http' even if you send the request to https://...
			$options = array(
					'http' => array(
							'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
							'method'  => 'POST',
							'content' => http_build_query($data)
					)
			);
			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);
			return $result;
		}
	}
	
	public function invioPagamentoWhatsapp($scadenza, $prenotazioneId, $user) {
		$sql = "SELECT ClienteCellularePrefisso, ClienteCellulare FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";
		$row =  $this->conn->query_first($sql);
		if(isset($row['ClienteCellularePrefisso']) && isset($row['ClienteCellulare'])) {
			$cell = '+'.$row['ClienteCellularePrefisso'].$row['ClienteCellulare'];
			$url = Config::$servicesUrl.'whatsapp/sendPayment?token='.Config::$servicesToken;
	
			$data = array('number' => $cell,
					'operatoreId' => $user->OperatoreId,
					'prenotazioneId' => $prenotazioneId,
					'scadenza' => $messaggio
			);
	
			// use key 'http' even if you send the request to https://...
			$options = array(
					'http' => array(
							'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
							'method'  => 'POST',
							'content' => http_build_query($data)
					)
			);
			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);
			return $result;
		}
	}
}
?>
