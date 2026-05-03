<?php
// Include per funzioni base
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");

// Configurazione e path
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();

// Include classi necessarie
include_once($classespath_ . "class.DT.php");
include_once($classespath_ . "class.Linea.php");
include_once($classespath_ . "class.Percorso.php");
include_once($classespath_ . "class.Tratta.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Fermata.php");
include_once($classespath_ . "class.Orario.php");
include_once($classespath_ . "class.Listino.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.TipologiaBus.php");
include_once($classespath_ . "class.Comune.php");
include_once($classespath_ . "class.Prenotazione.php");
include_once($classespath_ . "class.Operatore.php");
include_once($classespath_ . "Graph/class.LineaGraph.php");
include_once($classespath_ . "Graph/class.GrafoTratte.php");

// Variabili globali
global $pagamentoTipoId, $operatoreId, $db;
$pagamentoTipoId = 6;
$operatoreId = null;

// Recupero handler dalla query string
$handler = explode('/', $_GET['handler']);

// Funzione compatibilità per http_response_code su vecchie versioni PHP
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

// --- Funzione di controllo token API ---
function checkApiToken($token) {
    global $db, $operatoreId;
    if (!$db) {
        $db = new Database();
        $db->connect();
    }
    $sql = "SELECT Token, OperatoreId FROM ApiToken WHERE Token = '" . addslashes($token) . "'";
    $row = $db->query_first($sql);
	if(isset( $row['OperatoreId'])) {
		$operatoreId = $row['OperatoreId'];
	}
    return !empty($row['Token']);
}

// --- Controllo sicurezza token ---
$token = isset($_GET['token']) ? $_GET['token'] : (isset($_POST['token']) ? $_POST['token'] : '');
if (empty($token) || !checkApiToken($token)) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code(403);
    echo json_encode(array("message" => "Forbidden: invalid or missing token."));
    exit;
}

// --- Gestione degli endpoint ---
switch ($handler[0]) {
    case 'status': {
        // Headers CORS e content-type
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // Risposta OK per lo status
            http_response_code(200);
            echo json_encode(array(
                'versione' => '1.0.0',
                'status' => 'OK'
            ));
        } else {
            // Metodo non valido
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }
        break;
    }

    case 'get-pickup': {
        // Headers CORS e content-type
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recupera i dati delle fermate pickup
            $data = getPickup();
            echo json_encode(array("data" => $data));
        } else {
            // Metodo non valido
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }
        break;
    }

    case 'get-dropoff': {
        // Headers CORS e content-type
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recupera i dati delle fermate dropoff
            $data = getDropoff();
            echo json_encode(array("data" => $data));
        } else {
            // Metodo non valido
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }
        break;
    }

    case 'get-linea': {
        // Headers CORS e content-type
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = getLinea();
            echo json_encode(array("data" => $data));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }
        break;
    }

    case 'get-linea-by-name': {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = getLineaByName();
            echo json_encode(array("data" => $data));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }
        break;
    }

    case 'get-porti': {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = getPorti();
            echo json_encode(array("data" => $data));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }
        break;
    }

    case 'get-date-corsa': {
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Max-Age: 3600");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = getDateCorsa();
            echo json_encode(array("data" => $data));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Method not valid."));
        }
        break;
    }

    default: {
        // Endpoint non valido
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        http_response_code(404);
        echo json_encode(array("message" => "API not valid."));
    }
}

// --- Funzione per recuperare le fermate pickup ---
function getPickup() {
    global $db;

    $db = new Database();
    $conn = $db->connect();

    // Parametri POST
    $ComunePickup = $_POST['ComuneFermataPickup'];
    if (isset($_POST['ComuneFermataDropoffId'])) {
        $FermataDropOff = $_POST['ComuneFermataDropoffId'];
    } else {
        $FermataDropOff = "";
    }

    // Ricerca fermate pickup tramite WebSearch
    $search = new WebSearch(null);
    $search->conn = $db;
    $arrComuniPickup = $search->getSearchGraphComunePickup($ComunePickup, $FermataDropOff);

    $x = 0;
    $return_arr = array();
    while ($x < sizeof($arrComuniPickup)) {
        $cp = $arrComuniPickup[$x]['ComuneId'];
        $vis = true;

        // Controllo tratte non vendibili
        if ((!empty($FermataDropOff)) && ($FermataDropOff > 0)) {
            $sql1 = "SELECT TratteNonVendibiliId FROM RT_TratteNonVendibili WHERE ComunePickUpId=$cp AND ComuneDropOffId=$FermataDropOff";
            $result1 = $db->query_first($sql1);
            if (!empty($result1['TratteNonVendibiliId']))
                $vis = false;
        }

        // Costruzione array di risposta
        $row_array['ComuneFermataPickup'] = $arrComuniPickup[$x]['Comune'];
        $row_array['ComuneIdPickup'] = $arrComuniPickup[$x]['ComuneId'];
        if ($vis == true)
            array_push($return_arr, $row_array);

        $x++;
    }

    return $return_arr;
}

// --- Funzione per recuperare le fermate dropoff ---
function getDropoff() {
    global $db;

    $db = new Database();
    $conn = $db->connect();

    // Parametri POST
    $ComuneDropOff = $_POST['ComuneFermataDropOff'];
    $ComunePickupId = isset($_POST['ComunePickupId']) ? $_POST['ComunePickupId'] : null;
    $FermataPickup = isset($_POST['FermataPickup']) ? $_POST['FermataPickup'] : null;

    // Ricerca fermate dropoff tramite WebSearch
    $search = new WebSearch(null);
    $search->conn = $db;
    $arrComuniDropOff = $search->getSearchGraphFermateDropOff($ComunePickupId, $FermataPickup, $ComuneDropOff);

    $x = 0;
    $return_arr = array();
    while ($x < sizeof($arrComuniDropOff)) {
        $vis = true;
        $cd = $arrComuniDropOff[$x]['ComuneId'];

        // Controllo tratte non vendibili
        if (!empty($ComunePickupId)) {
            $sql1 = "SELECT TratteNonVendibiliId FROM RT_TratteNonVendibili WHERE ComunePickUpId=$ComunePickupId AND ComuneDropOffId=$cd";
            $result1 = $db->query_first($sql1);
            if (!empty($result1['TratteNonVendibiliId']))
                $vis = false;
        }

        // Costruzione array di risposta
        $row_array['ComuneFermataDropOff'] = $arrComuniDropOff[$x]['Comune'];
        $row_array['ComuneIdDropOff'] = $arrComuniDropOff[$x]['ComuneId'];

        if ($vis == true)
            array_push($return_arr, $row_array);

        $x++;
    }

    return $return_arr;
}

// --- FUNZIONE PER get-linea ---
function getLinea() {
    global $db;

    $db = new Database();
    $conn = $db->connect();

    // Recupera parametri opzionali
    $percorsoId = isset($_POST['percorso_id']) ? intval($_POST['percorso_id']) : null; // <-- usa percorso_id
    $tipoTour = isset($_POST['tipo_tour']) ? intval($_POST['tipo_tour']) : null;

    // Query base
    $sql = "SELECT * FROM RT_Linea 
    WHERE Stato = 1 AND Cancella = 0 AND LineaId <> 2 AND IsWebSelling = 1";

    // Aggiungi filtri opzionali
    if ($percorsoId !== null) {
        $sql .= " AND PercorsoId = " . $percorsoId; // <-- campo corretto PercorsoId
    }
    if ($tipoTour !== null && ($tipoTour === 0 || $tipoTour === 1)) {
        $sql .= " AND TipoTour = " . $tipoTour;
    }

    $result = $db->fetch_array($sql);
    

    return $result;
}

// --- FUNZIONE PER get-linea-by-name ---
function getLineaByName() {
    global $db;

    $db = new Database();
    $conn = $db->connect();

    // Recupera il parametro POST
    $lineaNome = isset($_POST['LineaNome']) ? trim($_POST['LineaNome']) : '';

    if (empty($lineaNome)) {
        return [];
    }

    // Query per recuperare la linea tramite LineaNome
    $sql = "SELECT * FROM RT_Linea WHERE LineaNome = '" . addslashes($lineaNome) . "' LIMIT 1";
    $result = $db->query_first($sql);

    return $result ? $result : [];
}

// --- FUNZIONE PER get-porti ---
function getPorti() {
    global $db;

    $db = new Database();
    $conn = $db->connect();

    $linea_id = isset($_POST['linea_id']) ? intval($_POST['linea_id']) : 0;
    if ($linea_id == 0) {
        return [];
    }

    // Query Porto Partenza
    $sqlPortoPartenza = "SELECT f.FermataId, f.FermataNome, c.Comune, c.ComuneId, o.CorsaId, MIN(o.Orario) AS Orario, o.GiorniAggiuntivi, f.TrattaId
        FROM RT_Fermata f
        LEFT JOIN RT_Orario o ON f.FermataId = o.FermataId
        LEFT JOIN Comune c ON c.ComuneId = f.ComuneId
        LEFT JOIN Provincia p ON c.provincia = p.ProvinciaId
        LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
        WHERE t.LineaId = $linea_id
            AND o.Orario IS NOT NULL AND o.Orario <> ''
            AND o.Stato = 1 AND o.Cancella = 0
            AND f.Stato = 1 AND f.Cancella = 0
            AND f.IsPickup = 1
            AND c.ComuneId IS NOT NULL
            AND f.WebSelling = 1
        GROUP BY c.ComuneId
        ORDER BY c.Comune ASC";
    $arrPortoPartenza = $db->fetch_array($sqlPortoPartenza);

    // Query Porto Destinazione
    $sqlPortoDestinazione = "SELECT f.FermataId, f.FermataNome, c.Comune, c.ComuneId, o.CorsaId, MIN(o.Orario) AS Orario, o.GiorniAggiuntivi, f.TrattaId
        FROM RT_Fermata f
        LEFT JOIN RT_Orario o ON f.FermataId = o.FermataId
        LEFT JOIN Comune c ON c.ComuneId = f.ComuneId
        LEFT JOIN Provincia p ON c.provincia = p.ProvinciaId
        LEFT JOIN RT_Tratta t ON t.TrattaId = f.TrattaId
        WHERE t.LineaId = $linea_id
            AND o.Orario IS NOT NULL AND o.Orario <> ''
            AND o.Stato = 1 AND o.Cancella = 0
            AND f.Stato = 1 AND f.Cancella = 0
            AND f.IsDropOff = 1
            AND c.ComuneId IS NOT NULL
            AND f.WebSelling = 1
        GROUP BY c.ComuneId
        ORDER BY c.Comune ASC";
    $arrPortoDestinazione = $db->fetch_array($sqlPortoDestinazione);

    // Incrocia i comuni partenza/destinazione
    $selectPortoAR = array();
    foreach ($arrPortoPartenza as $portoPar) {
        foreach ($arrPortoDestinazione as $portoDes) {
            $selectPortoAR[$portoPar['ComuneId'].'_'.$portoDes['ComuneId']]['partenza'] = $portoPar['Comune'];
            $selectPortoAR[$portoPar['ComuneId'].'_'.$portoDes['ComuneId']]['destinazione'] = $portoDes['Comune'];
        }
    }

    return $selectPortoAR;
}

// --- FUNZIONE PER get-date-corsa ---
function getDateCorsa() {
    global $db;

    $db = new Database();
    $conn = $db->connect();

    // Parametri POST
    $dataInizio = isset($_POST['dataInizio']) ? $_POST['dataInizio'] : '2025-12-01';
    $dataFine = isset($_POST['dataFine']) ? $_POST['dataFine'] : '2025-12-31';
    $orario = isset($_POST['orario']) ? $_POST['orario'] : null;
    $lineaId = isset($_POST['LineaId']) ? intval($_POST['LineaId']) : 6;

    // Costruzione query
    $sql = "
        SELECT
            RT_Corsa.CorsaId AS CorsaId,
            RT_AppCalendario.AppCalendarioData AS AppCalendarioData,
            DATE_FORMAT(RT_AppCalendario.AppCalendarioData, '%d/%m/%Y') AS DataPartenzaFormattata,
            RT_Corsa.CorsaNome AS CorsaNome,
            RT_Corsa.LineaId AS LineaId,
            RT_Linea.LineaNome AS LineaNome,
            RT_Corsa.OrarioPartenza AS OrarioPartenza,
            RT_Corsa.OdcIdRef AS OdcIdRef,
            RT_Corsa.GestoreIdRef AS GestoreIdRef,
            COUNT(*) AS NumeroCorse
        FROM
            RT_Corsa
            JOIN RT_CorsaSettimana ON RT_Corsa.CorsaId = RT_CorsaSettimana.CorsaId
            JOIN RT_AppSettimana ON RT_CorsaSettimana.SettimanaId = RT_AppSettimana.AppSettimanaId
            JOIN RT_AppCalendario ON RT_AppSettimana.AppSettimanaGiorno = RT_AppCalendario.GiornoSettimana
            JOIN RT_Linea ON RT_Corsa.LineaId = RT_Linea.LineaId
            JOIN RT_TipologiaBus ON RT_Corsa.TipologiaBusDefaultId = RT_TipologiaBus.TipologiaBusId
            LEFT JOIN RT_CorsaBloccoWeb ON (
                RT_Corsa.CorsaId = RT_CorsaBloccoWeb.CorsaId
                AND RT_AppCalendario.AppCalendarioData = RT_CorsaBloccoWeb.DataPartenza
            )
            LEFT JOIN RT_CorsaBlocco ON (
                RT_Corsa.CorsaId = RT_CorsaBlocco.CorsaId
                AND RT_AppCalendario.AppCalendarioData = RT_CorsaBlocco.DataPartenza
            )
        WHERE
            RT_Corsa.Cancella = 0
            AND RT_Linea.IsWebSelling = 1
            AND RT_Corsa.Stato = 1
            AND RT_AppCalendario.AppCalendarioData >= RT_Corsa.AttivaDal
            AND RT_AppCalendario.AppCalendarioData <= RT_Corsa.AttivaAl
            AND (
                (RT_AppCalendario.Feriale = RT_Corsa.IncludiFeriale AND RT_Corsa.IncludiFeriale = 1)
                OR (RT_AppCalendario.Prefestivo = RT_Corsa.IncludiPrefestivo AND RT_Corsa.IncludiPrefestivo = 1)
                OR (RT_AppCalendario.Festivo = RT_Corsa.IncludiFestivo AND RT_Corsa.IncludiFestivo = 1)
            )
            AND RT_AppCalendario.AppCalendarioData >= '" . addslashes($dataInizio) . "'
            AND RT_AppCalendario.AppCalendarioData <= '" . addslashes($dataFine) . "'
            AND RT_Corsa.LineaId = " . intval($lineaId);

    // Se orario è passato, aggiungi filtro
    if ($orario) {
        $sql .= " AND RT_Corsa.OrarioPartenza >= '" . addslashes($orario) . "'";
    }

    $sql .= "
        GROUP BY
            RT_AppCalendario.AppCalendarioData
        ORDER BY
            RT_AppCalendario.AppCalendarioData,
            RT_Linea.PercorsoId,
            RT_Linea.LineaNome,
            RT_Corsa.CorsaPeso ASC,
            RT_Corsa.CorsaNome
    ";

    $result = $db->fetch_array($sql);

    return $result;
}

