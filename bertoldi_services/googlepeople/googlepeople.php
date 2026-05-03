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
	
	case 'insertPeople': {
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
			$responce =  insertPeople($_POST['nome'], $_POST['cognome'], $_POST['email'], $_POST['cellulare']);
			
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



function insertPeople($nome, $cognome, $email, $cellulare) {
	$db = new Database();
	$db->connect();
	$servicePeople = new ServiceGooglePeople($db);
	$result = $servicePeople->insertPeople($nome, $cognome, $email, $cellulare);
	
	//risultato
	$data['result'] = 'ok';

	return $data;
}

?>