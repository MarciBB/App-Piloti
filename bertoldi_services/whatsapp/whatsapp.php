<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include($basepath."/main_include.php");

require_once $basepath.'/protected/classes/twilio-php-main/twilio-php-main/src/Twilio/autoload.php';

$config=new Config();
$run=$config->load(); 

ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL);

use Twilio\Rest\Client;

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
	
	case 'sendMessage': {
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
			$responce =  sendMessage($_POST['message'], $_POST['number'], $_POST['operatoreId'], $_POST['prenotazioneId'], $_POST['tipo']);
			
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
	
	case 'sendTicket': {
		
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
			$responce =  sendTicket($_POST['number'], $_POST['operatoreId'], $_POST['prenotazioneId']);
				
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
	
	case 'sendPayment': {
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
			$responce =  sendPayment($_POST['number'], $_POST['operatoreId'], $_POST['prenotazioneId'], $_POST['scadenza']);
	
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



function sendMessage($message, $number, $operatoreId, $prenotazioneId, $tipoMessaggio, $tickets = null) {
	
	$sid    = Config::$twilloSid;
	$token  = Config::$twilloAuthToken;
	$numeroWhatsapp = Config::$twilloNumero;
	$twilio = new Client($sid, $token);
	if(isset($tickets) && $tipoMessaggio == 'ticket') {
		$message .= " Scarica qui ".$tickets[0];
	}
	
	$messageReturn = $twilio->messages
	                  ->create("whatsapp:$number", // to
	                           [
	                               	"from" => "whatsapp:$numeroWhatsapp",
	                               	"body" => $message
	                           ]
	                  );
// 	if(isset($tickets) && $tipoMessaggio == 'ticket') {
// 		foreach($tickets as $t) {
// 			$messageTicket = $twilio->messages
// 					->create("whatsapp:$number", // to
// 							[
// 									"from" => "whatsapp:$numeroWhatsapp",
// 									"body" => $t
// 							]
// 					);
// 		}
// 	}
	//salvataggio su db
	$db = new Database();
	$db->connect();
	$dataInsert['PrenotazioneId'] = $prenotazioneId;
	$dataInsert['Messaggio'] = $message;
	$dataInsert['Numero'] = $number;
	$dataInsert['Canale'] = $tipoMessaggio;
	$dataInsert['DataIns'] = date('Y-m-d H:i');
	$dataInsert['OpeIns'] = $operatoreId;
	$db->insert("RT_PrenotazioneMessaggio", $dataInsert);
	
	//risultato
	if(isset($messageReturn->sid)) {
		$data['result'] = 'ok';
	} else {
		$data['result'] = 'error';
	}
	return $data;
}

function sendTicket($number, $operatoreId, $prenotazioneId, $messaggio = null) {
	$db = new Database();
	$db->connect();
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";
	$rowPrenotazione = $db->query_first($sql);
	
	$sql = "SELECT CodiceQrcode FROM RT_PrenotazioneNumero WHERE PrenotazioneId = $prenotazioneId";
	$rowTitoli = $db->fetch_array($sql);

	$basepath=Config::$gestionale;
	$voucherpath = $basepath."/protected/modules/rt_qrcode/ticket.php?code=";
	$titoli = array();
	foreach($rowTitoli as $t) {
		$titoli[0] = $voucherpath.$t['CodiceQrcode'];
	}
	if(isset($messaggio)) {
		$message = $messaggio;
	} else {
		$message = 'Gentile '.$rowPrenotazione['ClienteNome'].', puo\' scaricare i suoi biglietti della sua prenotazione  '.$rowPrenotazione['CodicePrenotazione'].' dal seguente link. Grazie mille, lo staff Onebus.';
	}
	
	$responce = sendMessage($message, $number, $operatoreId, $prenotazioneId, 'ticket', $titoli);
	return $responce;
}

function sendPayment($number, $operatoreId, $prenotazioneId, $scadenza) {
	$db = new Database();
	$db->connect();
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";
	$row = $db->query_first($sql);
	
	$message = 'Gentile '.$row['ClienteNome'].', le ricordiamo di effettuare il pagamento di '.$row['TotaleResiduo'].'euro per la prenotazione '.$row['CodicePrenotazione'].' entro il '.$scadenza.'. Grazie mille, lo staff Onebus.';	
	
	$result = sendMessage($message, $number, $operatoreId, $prenotazioneId, 'payment');
	return $result;
}
























?>