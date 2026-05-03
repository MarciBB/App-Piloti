<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];

//caricamento config
$config_include = $basepath.'/custom/reserved/class.Config.php';
include_once($config_include);
$config = new Config();
$run = $config->load(); 
//fine caricamento config

// Avvia la sessione
$dbSession=$basepath.'/protected/classes/class.SessionHandlerDB.php';
include_once($dbSession);
try {
    $dsn = 'mysql:host='.Config::$dbserver.';port='.Config::$dbport.';dbname='.Config::$dbname.';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
    ];
    $pdo = new PDO($dsn, Config::$dbuser, Config::$dbpass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Imposta il gestore di sessioni personalizzato
$handler = new SessionHandlerDB($pdo);
session_set_save_handler($handler, true);

// Gestisci l'ID della sessione dalla URL
$sessionId = '';
if (isset($_GET['session_id'])) {
    session_id($_GET['session_id']);
	$sessionId = $_GET['session_id'];
}
// Avvia la sessione
session_start();

$errors_include=$basepath.'/protected/classes/class.Errors.php';

$database_include=$basepath.'/protected/classes/class.Database.php';
$operatore_include=$basepath.'/protected/classes/class.Operatore.php';
$email_include=$basepath.'/protected/classes/class.Email.php';
$session_include=$basepath.'/protected/include/session.inc.php';
$html_page_include=$basepath.'/protected/classes/class.Html_Page.php';
$storico_operazioni=$basepath.'/protected/classes/class.StoricoOperazioni.php';
$date_time=$basepath.'/protected/classes/class.DT.php';
$search=$basepath.'/protected/classes/class.WebSearch.php';
$fermata=$basepath.'/protected/classes/class.Fermata.php';
$grafo=$basepath.'/protected/classes/Graph/Graph.php';
$grafoLinea=$basepath.'/protected/classes/Graph/class.LineaGraph.php';
$grafoDisponibilita=$basepath.'/protected/classes/Graph/class.DisponibilitaGraph.php';
$grafoTratte=$basepath.'/protected/classes/Graph/class.GrafoTratte.php';
$prefissoTelefono=$basepath.'/protected/classes/class.PrefissoTelefono.php';
$utenteWeb=$basepath.'/protected/classes/class.UtenteWeb.php';
$prenotazione=$basepath.'/protected/classes/class.Prenotazione.php';
$gestore=$basepath.'/protected/classes/class.Gestore.php';
$googlePeople=$basepath.'/protected/classes/class.ServiceGooglePeople.php';
$nazione=$basepath.'/protected/classes/class.Nazione.php';
$salesManago=$basepath.'/protected/classes/class.ServiceSalesManago.php';

$stripeSession=$basepath.'/protected/classes/stripe-php/lib/Checkout/Session.php';

include_once($errors_include);
include_once($session_include);

include_once($database_include); 
include_once($operatore_include);
include_once($html_page_include);
include_once($storico_operazioni);
include_once($date_time);
include_once($email_include);
include_once($search);
include_once($fermata);
include_once($grafo);
include_once($grafoLinea);
include_once($grafoDisponibilita);
include_once($grafoTratte);
include_once($prefissoTelefono);
include_once($utenteWeb);
include_once($prenotazione);
include_once($gestore);
include_once($googlePeople);
include_once($nazione);
include_once($salesManago);

// include stripe
require $basepath.'/protected/classes/stripe-php/init.php';
use Stripe\Stripe;
include_once($stripeSession);



global $user;
global $HtmlCommon;
$HtmlCommon=new Html_page();
global $errore;
$errore=new Errors();
global $db, $OperatoreId, $OdcId, $GestoreId, $SedeId;
global $LinkActive;


$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();

$OdcId=1;
$GestoreId=1;
$OperatoreId=42;
$SedeId=36;

// recupero la lingua del browser
$browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
// Se è presente il parametro lang nella URL, usa quello; altrimenti usa la lingua del browser
$supportedLangs = ['it', 'en', 'de', 'fr', 'es'];
// Se è presente il parametro lang nella URL, usa quello SEMPRE
if (isset($_GET['lang']) && in_array($_GET['lang'], $supportedLangs)) {
    $_SESSION['lang'] = $_GET['lang'];
} elseif (!isset($_SESSION['lang'])) {
    // Altrimenti, se non è già in sessione, usa la lingua del browser
    $_SESSION['lang'] = in_array($browserLang, $supportedLangs) ? $browserLang : 'en';
}

include($basepath.'/dizionario/'.$_SESSION['lang'].'/dizionario.php');

function setSessionCookieWithSameSite() {
    // Solo per versioni PHP precedenti a 7.3, se necessario
    if (PHP_VERSION_ID < 70300 && !headers_sent()) {
        $params = session_get_cookie_params();
        $cookie = session_name() . '=' . session_id();
        $cookie .= '; expires=' . gmdate('D, d M Y H:i:s T', $params['lifetime'] ? time() + $params['lifetime'] : 0);
        $cookie .= '; path=' . $params['path'];
        $cookie .= '; domain=' . $params['domain'];
        $cookie .= '; secure';
        $cookie .= '; HttpOnly';
        $cookie .= '; SameSite=None';
        header('Set-Cookie: ' . $cookie, false);
    }
}

function clearSession(){
	$_SESSION['CURRENT_SEARCH'] = null;
    unset($_SESSION['CURRENT_SEARCH']);
	
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
	unset($_SESSION['seleziona_lesperienza']);
	unset($_SESSION['tipo_viaggio']);
	unset($_SESSION['porto_partenza_nome']);
	unset($_SESSION['porto_destinazione_nome']);
	unset($_SESSION['total_price']);
	unset($_SESSION['PickupId']);
	unset($_SESSION['Pickup']);
	unset($_SESSION['DropoffId']);
	unset($_SESSION['Dropoff']);
	unset($_SESSION['start_time']);
	unset($_SESSION['end_time']);
	unset($_SESSION['time_diffrence']);
	unset($_SESSION['extra_services_price']);
	unset($_SESSION['totalprice']);
	unset($_SESSION['pagamentoId']);
	unset($_SESSION['CorsaIdR']);
    unset($_SESSION['total_priceR']);
    unset($_SESSION['PickupIdR']);
	unset($_SESSION['PickupR']);
    unset($_SESSION['DropoffIdR']);
	unset($_SESSION['DropoffR']);
    unset($_SESSION['start_timeR']);
    unset($_SESSION['end_timeR']);
    unset($_SESSION['time_diffrenceR']);
    unset($_SESSION['locationR']);
	unset($_SESSION['prefisso']);
    unset($_SESSION['coupons']);
    unset($_SESSION['coupon_importo']);
    unset($_SESSION['coupon']);
}

// Funzione per rimuovere il testo tra parentesi tonde
function rimuoviParentesiComune($string) {
    // Espressione regolare per rimuovere qualsiasi testo tra parentesi (con spazi opzionali)
    return preg_replace('/\s*\(.*?\)\s*/i', '', $string);
}

function estraiPax($stringa) {
    // Usa un'espressione regolare per trovare due numeri separati da uno spazio o da "a"
    if (preg_match('/\d+\s*(?:a|to)\s*(\d+)/i', $stringa, $matches)) {
        return $matches[1]; // Il secondo numero è il primo gruppo di cattura
    }
    return null; // Nessun numero trovato
}

function getGestoreByCode() {
    global $db;
    
    if (!isset($_GET['code'])) {
        $_SESSION['gestore'] = -1;
        $_SESSION['code_gestore'] = '';
        return false;
    }

    $code = trim($_GET['code']);

    $sql = "SELECT * FROM Gestore
        WHERE CodiceAzienda = '{$code}'
        AND Stato = 1
        AND Cancella = 0
        AND GestoreId <> 1
        LIMIT 1";

    $gestore = $db->query_first($sql);
    $_SESSION['gestore'] = $gestore;
    $_SESSION['code_gestore'] = '?code='.$_GET['code'];

    return true;
}


?>
