<?php
//include per funzioni base
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.DT.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Autisti.php");
include_once($classespath_."class.Mobile.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.Orario.php");
include_once($classespath_."class.Listino.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.TipologiaBus.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Operatore.php");
include_once($classespath_."class.PrenotazioneDettaglio.php");
include_once($classespath_."class.PrenotazioneMovimento.php");
include_once($classespath_."Graph/class.LineaGraph.php");
include_once($classespath_."Graph/class.GrafoTratte.php");
include_once($classespath_."Graph/class.DisponibilitaGraph.php");
include_once ($classespath_ . "/class.Disponibilita.php");
include_once($classespath_."class.BookingService.php");


include_once($basepath."/api_function.php");

global $pagamentoTipoId;

$handler = explode('/', $_GET['handler']);
$pagamentoTipoId = 6;

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
$operatoreGoEuro = checkToken($token);
if($operatoreGoEuro == -1){
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
                'versione' => '1.1.0',
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
    
//     case 'routes': {
//         // required headers
//         header("Access-Control-Allow-Origin: *");
//         header('Content-Type: application/json');
// //         header("Content-Type: application/json; charset=UTF-8");
        
//         if ($_SERVER['REQUEST_METHOD'] === 'GET') {
//             // The request is using the POST method
            
//             // set response code - 200 OK
//             http_response_code(200);
            
//             $result = routes();
            
//             echo json_encode($result);
//         } else {
//             // set response code - 404 Not found
//             http_response_code(404);
            
//             echo json_encode(
//                 array("message" => "Method not valid.")
//             );
//         }
        
//         break;
//     }

    case 'experienceDates': {
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json_str = file_get_contents('php://input');
            $json_obj = json_decode($json_str);

            if (!isset($json_obj->lineaId) ||
                !isset($json_obj->dataInizio) ||
                !isset($json_obj->dataFine)
            ) {
                http_response_code(404);
                echo json_encode(array("message" => "Parameters not valid."));
                break;
            }

            $orario = isset($json_obj->orario) ? $json_obj->orario : null;
            $result = getExperienceDates(
                intval($json_obj->lineaId),
                $json_obj->dataInizio,
                $json_obj->dataFine,
                $orario
            );
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }

        break;
    }
    
//     case 'search': {
//         // required headers
//         header("Access-Control-Allow-Origin: *");
// //         header("Content-Type: application/json; charset=UTF-8");
//         header("Access-Control-Allow-Methods: POST");
//         header("Access-Control-Max-Age: 3600");
//         header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
//         header('Content-Type: application/json');

//         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//             // The request is using the POST method
            
//             // get posted data
//             $json_str = file_get_contents('php://input');
//             $json_obj = json_decode($json_str);

//             $passengers = $json_obj->passengers;

//             $data = search(
//                 $json_obj->departureStationCode, 
//                 $json_obj->arrivalStationCode, 
//                 $json_obj->departureDate, 
//                 (isset($json_obj->returnDate))? $json_obj->returnDate : null, 
//                 (isset($passengers->adult))? $passengers->adult : 0,  
//                 (isset($passengers->infant))? $passengers->infant : 0,
//             	(isset($passengers->child))? $passengers->child : 0
//             );
            
//             echo json_encode($data);
//         } else {
//             // set response code - 404 Not found
//             http_response_code(404);
            
//             echo json_encode(
//                 array("message" => "Method not valid.")
//             );
//         }
        
//         break;    
//     }
    
    case 'reservation': {
        // required headers
        header("Access-Control-Allow-Origin: *");
//         header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // The request is using the POST method
            
            // get posted data
            $json_str = file_get_contents('php://input');
            $json_obj = json_decode($json_str);
            
            $passengers = array();
            foreach ($json_obj->passengers as $p) {
                $passengers[] = (array) $p;
            }
                
            $response = reservation(
                $json_obj->departureDate, 
                $json_obj->departureRide, 
                (isset($json_obj->returnDate))? $json_obj->returnDate : null, 
                (isset($json_obj->returnRide))? $json_obj->returnRide : null, 
                $json_obj->departureStationCode, 
                $json_obj->arrivalStationCode,
                $passengers, 
                $json_obj->phoneNumber,
            	$json_obj->email,
                (isset($json_obj->customerName) ? $json_obj->customerName : null)
            );
//             if (strpos($response, '105') !== false) {
//             	http_response_code(404);
//             }
        	echo json_encode($response);
        } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $id = $handler[1];
            $response = reservationDelete($id);
            echo json_encode($response);
        } else {
            // set response code - 404 Not found
            http_response_code(404);
            
            echo json_encode(
                array("message" => "Method not valid.")
            );
        }
        
        break;
    }
    
    case 'booking': {
        // required headers
        header("Access-Control-Allow-Origin: *");
//         header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // The request is using the POST method
            // get posted data
            $json_str = file_get_contents('php://input');
            $json_obj = json_decode($json_str);
            $data = booking($json_obj->reservationId);
            
            echo json_encode($data);
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = $handler[1];
            
            $data = getBooking($id);
            
            echo json_encode($data);
        } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $id = $handler[1];
            if(isset($_GET['force']) && $_GET['force'] == 'true'){
            	$force = true;
            } else {
            	$force = false;
            }
            $response = bookingDelete($id, $force);
            echo json_encode($response);
        } else {
            // set response code - 404 Not found
            http_response_code(404);
            
            echo json_encode(
                array("message" => "Method not valid.")
            );
        }
        
        break;
    }
    
    case 'getBookings': {
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $db = new Database();
            $db->connect();
            $gestoreCheck = $db->query_first("SELECT o.GestoreId FROM ApiToken api LEFT JOIN Operatore o ON o.OperatoreId = api.OperatoreId WHERE api.Token = '" . addslashes($token) . "'");
            if (!isset($gestoreCheck['GestoreId']) || intval($gestoreCheck['GestoreId']) !== 1) {
                http_response_code(403);
                echo json_encode(array("message" => "Permessi non validi."));
                break;
            }
            global $user, $operatoreGoEuro;
            $user = new Operatore();
            $user->conn = $db;
            $user->inizializzaMobile($operatoreGoEuro);
            $service = new BookingService();
            $service->conn = $db;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : null;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;
            $data = $service->getBookings($offset, $limit);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }
        
        break;
    }

    case 'experiences': {
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $tipoTour = isset($_GET['tipoTour']) ? $_GET['tipoTour'] : null;
            $data = getExperiences($tipoTour);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }

        break;
    }

    case 'experience': {
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if(!isset($handler[1]) || intval($handler[1]) <= 0) {
                http_response_code(404);
                echo json_encode(array("message" => "Experience id not valid."));
                break;
            }
            $data = getExperience($handler[1]);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }

        break;
    }

    case 'experiencePrices': {
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $lineaId = isset($_GET['lineaId']) ? intval($_GET['lineaId']) : 0;
            $dataPartenza = isset($_GET['dataPartenza']) ? $_GET['dataPartenza'] : '';

            if($lineaId <= 0 || $dataPartenza == '') {
                http_response_code(404);
                echo json_encode(array("message" => "Parameters not valid."));
                break;
            }

            $data = getExperiencePrices($lineaId, $dataPartenza);
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }

        break;
    }

    case 'experiencePorts': {
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json_str = file_get_contents('php://input');
            $json_obj = json_decode($json_str);

            if (!isset($json_obj->lineaId) || intval($json_obj->lineaId) <= 0) {
                http_response_code(404);
                echo json_encode(array("message" => "Parameters not valid."));
                break;
            }

            $data = getPorti(intval($json_obj->lineaId));
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }

        break;
    }

    case 'availableRides': {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $json_str = file_get_contents('php://input');
            $json_obj = json_decode($json_str);

            if (!isset($json_obj->comunePartenzaId) ||
                !isset($json_obj->comuneDestinazioneId) ||
                !isset($json_obj->dataPartenza) ||
                !isset($json_obj->lineaId) ||
                !isset($json_obj->biglietti)
            ) {
                http_response_code(404);
                echo json_encode(array("message" => "Parameters not valid."));
                break;
            }

            $data = availableRides(
                intval($json_obj->comunePartenzaId),
                intval($json_obj->comuneDestinazioneId),
                $json_obj->dataPartenza,
                intval($json_obj->lineaId),
                $json_obj->biglietti
            );
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
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
