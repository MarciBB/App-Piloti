<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
include_once($classespath_ . "class.DT.php");
include_once($classespath_ . "class.Autisti.php");
include_once($classespath_ . "class.Mobile.php");
include_once($classespath_ . "class.Prenotazione.php");
//include_once($classespath_ . "class.Comunicazioni.php");
include_once($classespath_ . "class.Tratta.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Fermata.php");
include_once($classespath_ . "class.Orario.php");
include_once($classespath_ . "class.Listino.php");
include_once($classespath_ . "class.Disponibilita.php");

include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.TipologiaBus.php");
include_once($classespath_ . "class.Comune.php");
include_once($classespath_ . "class.Prenotazione.php");
include_once($classespath_ . "class.PrenotazioneDettaglio.php");
include_once($classespath_ . "class.Linea.php");
include_once($classespath_ . "class.Percorso.php");
include_once($classespath_ . "class.PrenotazioneMovimento.php");
include_once($classespath_ . "Graph/class.LineaGraph.php");
include_once($classespath_ . "Graph/class.GrafoTratte.php");
include_once($classespath_ . "class.ServiceFiscalGateway.php");
include_once($classespath_ . "class.StoricoOperazioni.php");

include_once($classespath_.'PHPMailer/class.phpmailer.php');
include_once($classespath_.'PHPMailer/class.smtp.php');

include_once($modulespath_ . "rt_previaggio/stampa_titoli_di_viaggio_func.php");
include_once($modulespath_ . "rt_previaggio/stampa_titoli_di_viaggio_cartaceo_func.php");

global $ModuloId;
global $user;
//header('Access-Control-Allow-Origin: http://csreisenapp.it');

//header('Content-type: application/xml');
//readfile('arunerDotNetResource.xml');

$db = new Database();
$db->connect();
$user = new Operatore();
$user->conn = $db;
$user->inizializzaMobile(1);

if (!empty($_POST)) {
	switch ($_POST['action']) {
		case "login":
			login();
			break;
		case "loginBrowser":
			loginBrowser();
			break;
		case "logoutBrowser":
			logoutBrowser();
			break;
		case "getCorse":
			getCorse();
			break;
		case "getFermateCarico":
			getFermateCarico();
			break;
		case "caricoCompleto":
			caricoCompleto();
			break;
		case "getPasseggeriFermata":
			getPasseggeriFermata();
			break;
		case "caricoReset":
			caricoReset();
			break;
		case "getFermateScarico":
			getFermateScarico();
			break;
		case "getFermateScambio":
			getFermateScambio();
			break;
		case "scaricoCompleto":
			scaricoCompleto();
			break;
		case "getPasseggeriFermataScarico":
			getPasseggeriFermataScarico();
			break;
		case "getPasseggeriFermataScambio":
			getPasseggeriFermataScambio();
			break;
		case "scaricoReset":
			scaricoReset();
			break;
		case "getBigliettiIncassare":
			getBigliettiIncassare();
			break;
		case "updateIncasso":
			updateIncasso();
			break;
		case "getBigliettiValidare":
			getBigliettiValidare();
			break;
		case "updateValidazione":
			updateValidazione();
			break;
		case "existQrcode":
			existQrcode();
			break;
		case "getPrenotazioniModifica":
			getPrenotazioniModifica();
			break;
		case "getCorseRitornoEmetti":
			getCorseRitornoEmetti();
			break;
		case "getTipologiaBiglietti":
			getTipologiaBiglietti();
			break;
		case "prenota":
			prenota();
			break;
		case "caricoPrenotazione":
			caricoPrenotazione();
			break;
		case "contabilita":
			contabilita();
			break;
		case "cerca":
			cerca();
			break;
		case "updateCarico";
			updateCarico();
			break;
		case "updateCaricoZero";
			updateCaricoZero();
			break;
		case "updateScarico";
			updateScarico();
			break;
		case "updateScaricoZero";
			updateScaricoZero();
			break;
		case "getInfo";
			getInfo();
			break;
		case "getInfoPrenotazione";
			getInfoPrenotazione();
			break;
		case "getNotePrenotazione";
			getNotePrenotazione();
			break;
		case "getComuni";
			getComuni();
			break;
		case "getCorseAndataEmetti";
			getCorseAndataEmetti();
			break;
		case "getFermate";
			getFermate();
			break;
		case "getPosti";
			getPosti();
			break;
		case "getInfoIncasso":
			getInfoIncasso();
			break;
		case "getInfoFoglioViaggio":
			getInfoFoglioViaggio();
			break;
		case "updateInfoFoglioViaggio":
			updateInfoFoglioViaggio();
			break;
		case "getParametriSpesa":
			getParametriSpesa();
			break;
		case "saveSpesa":
			saveSpesa();
			break;
		case "modificaParametriSpesa":
			modificaParametriSpesa();
			break;
		case "inviaTicketEmail":
			inviaTicketEmail();
			break;
		case "stampaPdf":
			stampaPdf();
			break;
		case "stampaCartaceoPdf":
			stampaCartaceoPdf();
			break;
		case "validaAndata":
			validaAndata();
			break;
		case "validaRitorno":
			validaRitorno();
			break;
		case "prenotaBigliettoABordo":
			prenotaBigliettoABordo();
			break;
		case "annullaABordo":
			annullaABordo();
			break;
		case "emettiABordo":
			emettiABordo();
			break;
	}
} else {
	echo json_encode(array('error' => 'action'));
}
function logoutBrowser()
{
	unset($_SESSION['autista']);
	session_destroy();
	echo json_encode(array('result' => 'true'));
}

function loginBrowser()
{
	$username = $_POST['username'];
	$password = $_POST['password'];
	$pin = $_POST['pin'];

	$db = new Database();
	$db->connect();
	$autistaObj = new Autista();
	$autistaObj->conn = $db;
	$account = $autistaObj->getAccount($username);

	if (!isset($account) || !isset($account['Password'])) {
		$sql = "INSERT INTO AppMobileLoginLog (Username, Data, Ip, Message)
 			VALUES ('" . $username . "', '" . date('Y-m-d H:i:s') . "', '" . $_SERVER['REMOTE_ADDR'] . "', 'Error username');";
		$result = $db->query($sql);
	}

	$pswmd5 = md5($password);
	if ($pswmd5 == $account['Password']) {
		$checkPsw = true;
	} else {
		$checkPsw = false;
	}
	if ($autistaObj->checkPin($pin, $username)) {
		$checkPin = true;
	} else {
		$checkPin = false;
	}
	if ($checkPin && $checkPsw) {
		session_start();
		$_SESSION['autista'] = $account;
		$sql = "INSERT INTO AppMobileLoginLog (Username, Data, Ip, Message)
 			VALUES ('" . $username . "', '" . date('Y-m-d H:i:s') . "', '" . $_SERVER['REMOTE_ADDR'] . "', 'Login OK');";
		$result = $db->query($sql);
		echo json_encode(array('result' => 'true', 'profilo' => $account));
	} else if (!$checkPsw) {
		$sql = "INSERT INTO AppMobileLoginLog (Username, Data, Ip, Message)
 			VALUES ('" . $username . "', '" . date('Y-m-d H:i:s') . "', '" . $_SERVER['REMOTE_ADDR'] . "', 'Error password');";
		$result = $db->query($sql);
		echo json_encode(array('result' => 'false', 'error' => 'psw'));
	} else {
		$sql = "INSERT INTO AppMobileLoginLog (Username, Data, Ip, Message)
 			VALUES ('" . $username . "', '" . date('Y-m-d H:i:s') . "', '" . $_SERVER['REMOTE_ADDR'] . "', 'Error PIN');";
		$result = $db->query($sql);
		echo json_encode(array('result' => 'false', 'error' => 'pin'));
	}
}

function login()
{
	$username = $_POST['username'];
	$password = $_POST['password'];
	$pin = $_POST['pin'];

	$db = new Database();
	$db->connect();
	$autistaObj = new Autista();
	$autistaObj->conn = $db;
	$account = $autistaObj->getAccount($username);
	$pswmd5 = md5($password);
	if ($pswmd5 == $account['Password']) {
		$checkPsw = true;
	} else {
		$checkPsw = false;
	}
	if ($autistaObj->checkPin($pin, $username)) {
		$checkPin = true;
	} else {
		$checkPin = false;
	}
	if ($checkPin && $checkPsw) {
		echo json_encode(array('result' => 'true', 'profilo' => $account));
	} else if (!$checkPsw) {
		echo json_encode(array('result' => 'false', 'error' => 'psw'));
	} else {
		echo json_encode(array('result' => 'false', 'error' => 'pin'));
	}
}

function getCorse()
{
	$data = $_POST['data'];

	$idAutista = $_POST['idAutista'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$bus = $mobileObj->getBus($idAutista, $data);
	$mobileObj->resetFoglioViaggio($idAutista, $data);

	$count = 0;
	foreach ($bus as $b) {
		$bus[$count]['TotPax'] = $mobileObj->getTotPax($b['CorsaId'], $b['DataPartenza'], $b['BusId'], $b['LineaId']);
		$bus[$count]['TotCaricati'] = $mobileObj->getTotCaricatiPax($b['CorsaId'], $b['DataPartenza'], $b['BusId']);
		$bus[$count]['Partenza'] = $mobileObj->getPartenzaBus($b['BusId']);
		$bus[$count]['Arrivo'] = $mobileObj->getArrivoBus($b['BusId']);
		$bus[$count]['OrarioPartenza'] = $mobileObj->getOrarioPartenzaBus($b['BusId']);

		$mobileObj->insertFoglioViaggio($bus[$count]);
		$count++;
	}

	echo json_encode(array('bus' => $bus));
}

function getFermateCarico()
{


	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];
	$busId = $_POST['busId'];
	$idAutista = $_POST['idAutista'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	// 	$mobileObj->updateSingoloBus($corsaId, $busId, $busNumero, $corsaDataPartenza, $idAutista);


	$fermate = $mobileObj->getFermate($busId);
	echo json_encode(array('fermate' => $fermate, 'passeggeri' => array()));
}

function getFermateScarico()
{
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];
	$busId = $_POST['busId'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$fermate = $mobileObj->getFermateScarico($corsaId, $corsaDataPartenza, $busId);
	// 	$restore = $mobileObj->restoreFermateScarico($trattaId, $corsaId, $corsaDataPartenza,$tipoServizio,$busId,$busNumero);

	echo json_encode(array('fermate' => $fermate, 'passeggeri' => array()));
}

function getFermateScambio()
{
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];
	$busId = $_POST['busId'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$fermate = $mobileObj->getFermateScambio($corsaId, $corsaDataPartenza, $busId);
	// 	$restore = $mobileObj->restoreFermateScarico($trattaId, $corsaId, $corsaDataPartenza,$tipoServizio,$busId,$busNumero);

	echo json_encode(array('fermate' => $fermate, 'passeggeri' => array()));
}

function caricoCompleto()
{
	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$trattaId = $_POST['trattaId'];
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];

	$arrayFermate = $_POST['fermate'];
	$arrayPasseggeri = $_POST['passeggeri'];
	$tipoServizio = $_POST['tipoServizio'];
	$busId = $_POST['busId'];
	$busNumero = $_POST['busNumero'];

	$mobileObj->updateFermateCarico($trattaId, $corsaId, $corsaDataPartenza, $arrayFermate, $arrayPasseggeri, $tipoServizio, $busId, $busNumero);

	echo json_encode(array('result' => 'true'));
}

function scaricoCompleto()
{
	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$trattaId = $_POST['trattaId'];
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];
	$arrayFermate = $_POST['fermate'];
	$arrayPasseggeri = $_POST['passeggeri'];

	$mobileObj->updateFermateScarico($trattaId, $corsaId, $corsaDataPartenza, $arrayFermate, $arrayPasseggeri);

	echo json_encode(array('result' => 'true'));
}

function caricoReset()
{
	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$trattaId = $_POST['trattaId'];
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];


	$mobileObj->resetFermateCarico($trattaId, $corsaId, $corsaDataPartenza);

	echo json_encode(array('result' => 'true'));
}

function scaricoReset()
{
	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$trattaId = $_POST['trattaId'];
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];


	$mobileObj->resetFermateScarico($trattaId, $corsaId, $corsaDataPartenza);

	echo json_encode(array('result' => 'true'));
}

function getPasseggeriFermata()
{
	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$busId = $_POST['busId'];
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];
	$idCarico = $_POST['idCarico'];
	$idAutista = $_POST['idAutista'];

	// 	$mobileObj->updateSingoloBus($corsaId, $busId, $busNumero, $corsaDataPartenza, $idAutista);

	$result = $mobileObj->getPasseggeriFermata($busId, $idCarico);

	echo json_encode(array('passeggeri' => $result));
}

function getPasseggeriFermataScarico()
{
	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];
	$idScarico = $_POST['idScarico'];
	$busId = $_POST['busId'];


	$result = $mobileObj->getPasseggeriFermataScarico($corsaId, $corsaDataPartenza, $busId, $idScarico);

	echo json_encode(array('passeggeri' => $result));
}
function getPasseggeriFermataScambio()
{
	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];
	$idScambio = $_POST['idScambio'];
	$busId = $_POST['busId'];


	$result = $mobileObj->getPasseggeriFermataScambio($corsaId, $corsaDataPartenza, $busId, $idScambio);

	echo json_encode(array('passeggeri' => $result));
}


function getBigliettiIncassare()
{
	$trattaId = $_POST['trattaId'];
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];

	$ordine = $_POST['ordine'];
	$tipoServizio = $_POST['tipoServizio'];
	$busId = $_POST['busId'];
	$busNumero = $_POST['busNumero'];
	$autistaId = $_POST['autistaId'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$biglietti = $mobileObj->getBigliettiIncassare($corsaId, $corsaDataPartenza, $ordine, $busId, $busNumero, $trattaId, $autistaId);
	$bigliettiRestore = $mobileObj->restoreBigliettazione($trattaId, $corsaId, $corsaDataPartenza, $ordine, $busId, $busNumero);


	echo json_encode(array('biglietti' => $bigliettiRestore));
}

function getBigliettiValidare()
{
	$trattaId = $_POST['trattaId'];
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];

	$ordine = $_POST['ordine'];
	$tipoServizio = $_POST['tipoServizio'];
	$busId = $_POST['busId'];
	$busNumero = $_POST['busNumero'];
	$autistaId = $_POST['autistaId'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$biglietti = $mobileObj->getBigliettiValidare($trattaId, $corsaId, $corsaDataPartenza, $ordine, $busId, $busNumero, $autistaId, $tipoServizio);
	$bigliettiRestore = $mobileObj->restoreValidazione($trattaId, $corsaId, $corsaDataPartenza, $ordine, $busId, $busNumero);
	echo json_encode(array('biglietti' => $bigliettiRestore));
}

function updateIncasso()
{

	$trattaId = $_POST['trattaId'];
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];

	$prenotazione = $_POST['prenotazione'];
	$email = $_POST['email'];
	$sms = $_POST['sms'];
	$busId = $_POST['busId'];
	$busNumero = $_POST['busNumero'];
	$autistaId = $_POST['autistaId'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$mobileObj->selectUser($autistaId);

	$sql = "SELECT CorsaId, Direzione FROM RT_PrenotazionePercorso where PrenotazioneId = " . $prenotazione['PrenotazioneId'];
	$tragitti = $db->fetch_array($sql);
	$corsaA = 0;
	$corsaR = 0;

	foreach ($tragitti as $entry) {
		if ($entry['Direzione'] == 'A') {
			$corsaA = $entry['CorsaId'];
		}
		if ($entry['Direzione'] == 'R') {
			$corsaR = $entry['CorsaId'];
		}
	}

	$pre = new Prenotazione($prenotazione['PrenotazioneId']);
	$pre->conn = $db;

	$pre->EmettiBigliettiAuto($prenotazione['Pax'], $corsaA, $corsaR, "", date('Y-m-d'));

	if ($tipoServizio = 'Bus') {
		$mobileObj->updateSingoloBus($corsaId, $busId, $busNumero, $corsaDataPartenza, $idAutista);
	}

	$biglietti = $mobileObj->updateIncasso($busId, $busNumero, $corsaId, $corsaDataPartenza, $prenotazione, $trattaId, $autistaId);

	$comunicazioniObj = new Comunicazioni();
	$comunicazioniObj->conn = $db;

	$sql = "Select CodicePrenotazione FROM RT_Prenotazione Where PrenotazioneId = " . $prenotazione['PrenotazioneId'];
	$cod = $db->fetch_array($sql);

	if ($prenotazione['Conferma'] == 'email') {
		$Oggetto = "Rocco Autolinee - biglietto emesso";
		$Messaggio = "Grazie mille di averci scelto.<br>Al seguente link puoi accedere ai QRCode da poter usare per scaricare il tuo biglietto e da poter utilizzare in viaggio per convalidare il tuo biglietto.";
		$Messaggio .= "<a href= 'http://" . $_SERVER['HTTP_HOST'] . "/protected/modules/rt_qrcode/qrcode.php?code=" . $prenotazione['PrenotazioneId'] . "&code2=" . $cod[0]['CodicePrenotazione'] . "&action=qrcodes'>I tuoi biglietti</a>";

		$comunicazioniObj->sendMail($email, $Oggetto, $Messaggio);
	} else if ($prenotazione['Conferma'] == 'sms') {
		$Messaggio = "Rocco Autolinee - I tuoi biglietti http://" . $_SERVER['HTTP_HOST'] . "/protected/modules/rt_qrcode/qrcode.php?code=" . $prenotazione['PrenotazioneId'] . "&code2=" . $cod[0]['CodicePrenotazione'] . "&action=qrcodes";
		//$comunicazioniObj->sendSMS("+39", $sms, $Messaggio);
	}
	$pagamentoId = 'CONTANTI';
	if ($prenotazione['Pagamento'] == 'pos') {
		$pagamentoId = 'POS';
	}
	$sql = "UPDATE RT_PrenotazioneTitolo SET TipoIncasso = '$pagamentoId' WHERE PrenotazioneId = " . $prenotazione['PrenotazioneId'];
	$db->query($sql);
	$mobileObj->selectUser($idAutista);
	echo json_encode(array('result' => $biglietti));
}

function updateValidazione()
{
	$trattaId = $_POST['trattaId'];
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];

	$prenotazione = $_POST['prenotazione'];
	$busId = $_POST['busId'];
	$busNumero = $_POST['busNumero'];
	$autistaId = $_POST['autistaId'];
	$tipoServizio = $_POST['tipoServizio'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;


	$biglietti = $mobileObj->updateValidazione($busId, $busNumero, $corsaId, $corsaDataPartenza, $prenotazione, $trattaId, $autistaId, $tipoServizio);
	echo json_encode(array('result' => $biglietti));
}

function updateCarico()
{
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];

	$prenotazione = $_POST['prenotazione'];
	$busId = $_POST['busId'];
	$autistaId = $_POST['autistaId'];
	$lastValue = $_POST['lastValue'];

	$conferma = $_POST['conferma'];
	$email = $_POST['email'];
	$cell = $_POST['cell'];
	$pagamento = $_POST['pagamento'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$biglietti = $mobileObj->updateCarico($busId, $corsaId, $corsaDataPartenza, $prenotazione, $lastValue);

	if ($prenotazione['Caricato'] == 1 && $lastValue != 1 && $prenotazione['DaPagare'] > 0) {
		$mobileObj->incassoCarico($busId, $corsaId, $corsaDataPartenza, $prenotazione, $lastValue, $conferma, $email, $cell, $pagamento);
	}

	if ($prenotazione['TitoloEmesso'] != 'E') {
		$mobileObj->EmettiTitoliDiViaggio($busId, $corsaId, $corsaDataPartenza, $prenotazione, $lastValue, $conferma, $email, $cell);
	}
	$mobileObj->selectUser($idAutista);

	echo json_encode(array('result' => $biglietti));
}

function updateCaricoZero()
{
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];

	$prenotazione = $_POST['prenotazione'];
	$busId = $_POST['busId'];
	$autistaId = $_POST['autistaId'];
	$lastValue = $_POST['lastValue'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$biglietti = $mobileObj->updateCaricoZero($busId, $corsaId, $corsaDataPartenza, $prenotazione, $lastValue);
	echo json_encode(array('result' => $biglietti));
}

function updateScarico()
{
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];
	$prenotazione = $_POST['prenotazione'];
	$busId = $_POST['busId'];
	$autistaId = $_POST['autistaId'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$biglietti = $mobileObj->updateScarico($busId, $corsaId, $corsaDataPartenza, $prenotazione);
	echo json_encode(array('result' => $biglietti));
}

function updateScaricoZero()
{
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];

	$prenotazione = $_POST['prenotazione'];
	$busId = $_POST['busId'];
	$autistaId = $_POST['autistaId'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$biglietti = $mobileObj->updateScaricoZero($busId, $corsaId, $corsaDataPartenza, $prenotazione);
	echo json_encode(array('result' => $biglietti));
}

function existQrcode()
{
	$codice = $_POST['codice'];
	$corsaId = $_POST['corsaId'];
	$dataCorsa = $_POST['dataPartenza'];

	$busId = $_POST['busId'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$result = $mobileObj->updateCaricobyQRcode($busId, $corsaId, $dataCorsa, $codice);

	echo json_encode(array('result' => $result));
}

function getPrenotazioniModifica()
{
	$trattaId = $_POST['trattaId'];
	$corsaId = $_POST['corsaId'];
	$corsaDataPartenza = $_POST['corsaDataPartenza'];

	$tipoServizio = $_POST['tipoServizio'];
	$busId = $_POST['busId'];
	$busNumero = $_POST['busNumero'];

	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$bigliettiModifica = $mobileObj->getPrenotazioniModifica($trattaId, $corsaId, $corsaDataPartenza, $tipoServizio, $busId, $busNumero);
	echo json_encode(array('modifica' => $bigliettiModifica));
}

function getCorseRitornoEmetti()
{
	// 	$corsaDataPartenza = $_POST['corsaDataPartenza'];

	// 	$prenotazioneId = $_POST['prenotazioneId'];
	// 	$db = new Database();
	// 	$db->connect();

	// 	$sql = "SELECT ComuneSalitaId, ComuneDiscesaId FROM RT_PrenotazionePercorso where PrenotazioneId = $prenotazioneId and Direzione = 'R'";
	// 	$rows=$db->fetch_array($sql);
	// 	if(count($rows) == 0){
	// 		$sql = "SELECT ComuneSalitaId as ComuneDiscesaId, ComuneDiscesaId as ComuneSalitaId FROM RT_PrenotazionePercorso where PrenotazioneId = $prenotazioneId and Direzione = 'A'";
	// 		$rows=$db->fetch_array($sql);
	// 	}

	// 	$mobileObj = new Mobile();
	// 	$mobileObj->conn = $db;
	// 	$corse = $mobileObj->getCorseRitornoModifica($rows[0]['ComuneSalitaId'], $rows[0]['ComuneDiscesaId'], $corsaDataPartenza);
	// 	echo json_encode(array('corse'=>$corse));
	$corsaDataPartenza = $_POST['corsaDataPartenza'];
	$comunePickup = $_POST['comunePickup'];
	$comuneDropoff = $_POST['comuneDropoff'];
	$CorsaId_post = $_POST['corsaPrenotazione'];
	$DataCorsa_post = $_POST['dataPrenotazione'];

	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$corse = $mobileObj->getCorseRitornoModifica($comunePickup, $comuneDropoff, $corsaDataPartenza, $CorsaId_post, $DataCorsa_post);
	echo json_encode(array('corse' => $corse['aaData']));
}

function getCorseAndataEmetti()
{
	$corsaDataPartenza = $_POST['corsaDataPartenza'];
	$comunePickup = $_POST['comunePickup'];
	$comuneDropoff = $_POST['comuneDropoff'];
	$CorsaId_post = $_POST['corsaPrenotazione'];
	$DataCorsa_post = $_POST['dataPrenotazione'];

	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$corse = $mobileObj->getCorseRitornoModifica($comunePickup, $comuneDropoff, $corsaDataPartenza, $CorsaId_post, $DataCorsa_post);
	echo json_encode(array('corse' => $corse['aaData']));
}

function getTipologiaBiglietti()
{


	$comunePickup = $_POST['comunePickup'];
	$fermataSalitaId = $_POST['fermataPickup'];
	$comuneDropoff = $_POST['comuneDropoff'];
	$fermataDiscesaId = $_POST['fermataDropoff'];
	$corsaAndataId = $_POST['corsaAndataId'];
	$corsaRitornoId = $_POST['corsaRitornoId'];
	$dataAndata = $_POST['dataAndata'];
	$daraRitorno = $_POST['daraRitorno'];
	$TV = $_POST['tipoViaggio'];
	$lineaIdA = $_POST['lineaIdA'];
	$lineaIdR = $_POST['lineaIdR'];
	$db = new Database();
	$db->connect();

	// 	$sql = "SELECT FermataSalitaId, FermataDiscesaId, CorsaId FROM RT_PrenotazionePercorso where PrenotazioneId = $prenotazioneId";
	// 	$rows=$db->fetch_array($sql);
	// 	$change = false;
	// 	if($rows[0]['FermataSalitaId'] != $fermataSalitaId || $rows[0]['FermataDiscesaId'] != $fermataDiscesaId || $rows[0]['CorsaId'] != $corsaId){
	// 		$change = true;
	// 	}
	// 	$sql = "SELECT PrenotazioneStato, TipoViaggioId FROM RT_Prenotazione where PrenotazioneId = $prenotazioneId";
	// 	$rows=$db->fetch_array($sql);
	// 	if($rows[0]['PrenotazioneStato'] == 3){
	// 		$change = false;
	// 	} else if($rows[0]['PrenotazioneStato'] == 1 && $rows[0]['TipoViaggioId'] != $TV){
	// 		$change = true;
	// 	}
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$corse = $mobileObj->getTipologiaBiglietti($comunePickup, $fermataSalitaId, $comuneDropoff, $fermataDiscesaId, $corsaAndataId, $corsaRitornoId, $dataAndata, $daraRitorno, $TV, $lineaIdR);
	echo json_encode(array('biglietti' => $corse));
}

function prenota()
{

	$comunePickup = $_POST['comunePickup'];
	$fermataPickup = $_POST['fermataPickup'];
	$comuneDropoff = $_POST['comuneDropoff'];
	$fermataDropoff = $_POST['fermataDropoff'];
	$dataAndata = $_POST['dataAndata'];
	$daraRitorno = $_POST['daraRitorno'];
	$corsaAndataId = $_POST['corsaAndataId'];
	$corsaRitornoId = $_POST['corsaRitornoId'];
	$biglietti_prenotati = $_POST['arr_bigliettiPrenotati'];
	$biglietti_aumento = $_POST['arr_bigliettiAumenti'];
	$biglietti_riduzione = $_POST['arr_bigliettiRiduzioni'];
	$bigliettiId = $_POST['bigliettiId'];
	$etichette = $_POST['etichette'];

	$idAutista = $_POST['idAutista'];

	$clienteNome = $_POST['clienteNome'];
	$clienteCellulare = $_POST['clienteCellulare'];
	$clienteCellulareFamiliare = $_POST['clienteCellulareFamiliare'];
	$clienteEmail = $_POST['clienteEmail'];
	$clienteSessoId = $_POST['clienteSessoId'];
	$clienteEta = $_POST['clienteEta'];
	$notaAndata = $_POST['notaAndata'];
	$notaRitorno = $_POST['notaRitorno'];
	$tipoViaggio = $_POST['tipoViaggio'];

	$passeggeri = $_POST['passeggeri'];

	$count = 0;
	foreach ($etichette as $entry) {
		$arr_biglietti_prenotati[$entry] = $biglietti_prenotati[$count];
		$arr_biglietti_aumento[$bigliettiId[$count]] = $biglietti_aumento[$count];
		$arr_biglietti_riduzione[$bigliettiId[$count]] = $biglietti_riduzione[$count];
		$count++;
	}

	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$mobileObj->selectUser($idAutista);

	$nuovaPrenotazioneId = $mobileObj->prenota($notaAndata, $notaRitorno, $arr_biglietti_prenotati, $arr_biglietti_riduzione, $arr_biglietti_aumento, $daraRitorno, $corsaRitornoId, $dataAndata, $corsaAndataId, $comunePickup, $fermataPickup, $comuneDropoff, $fermataDropoff, $tipoViaggio, $passeggeri, $clienteNome, $clienteCellulare, $clienteCellulareFamiliare, $clienteEmail, $clienteSessoId, $clienteEta);


	// 	$nuovaPrenotazioneId = $mobileObj->modifica($prenotazioneId, $arr_biglietti_prenotati, $arr_biglietti_riduzione, $arr_biglietti_aumento, $daraRitorno, $corsaRitornoId, $dataAndata, $corsaAndataId, $comunePickup, $fermataPickup, $comuneDropoff, $fermataDropoff, $tipoViaggio);
	// 	//modifica dati su rt_prenotazione
	// 	$sql = "Update RT_Prenotazione SET TotalePrenotazione = '".number_format($tempPr['TotaleImportoPrenotazione'],2)."', ClienteNome = '$clienteNome',
	// 	ClienteCellulare = '$clienteCellulare', ClienteSessoId = $clienteSessoId, ClienteCellulareFamiliare = '$clienteCellulareFamiliare', ClienteEmail = '$clienteEmail'
	// 	WHERE PrenotazioneId = $nuovaPrenotazioneId";
	// 	$result = $db->query($sql);
	// 	//aggiunta note
	// 	$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = $nuovaPrenotazioneId";
	// 	$resultNote = $db->fetch_array($sql);
	// 	foreach ($resultNote as $percorso){
	// 		if($percorso['Direzione'] == 'A' && $notaAndata != ""){
	// 			$sql = "INSERT INTO RT_PrenotazionePercorsoNote (PercorsoNotaId, PrenotazionePercorsoId, PrenotazioneId, Nota, TipoNota)
	// 			VALUES (null, ".$percorso['PrenotazionePercorsoId'].", $nuovaPrenotazioneId, '$notaAndata','G');";
	// 			$result = $db->query($sql);
	// 		} else if($percorso['Direzione'] == 'R' && $notaRitorno != ""){
	// 			$sql = "INSERT INTO RT_PrenotazionePercorsoNote (PercorsoNotaId, PrenotazionePercorsoId, PrenotazioneId, Nota, TipoNota)
	// 			VALUES (null, ".$percorso['PrenotazionePercorsoId'].", $nuovaPrenotazioneId, '$notaAndata','S');";
	// 			$result = $db->query($sql);
	// 		}
	// 	}

	// 	$mobileObj->updateSingoloBus($corsaId, $busId, $busNumero, $corsaDataPartenza, $idAutista);

	// 	$prenotazioneId = $_POST['prenotazioneId'];
	// 	$sql = "select * RT_PrenotazionePercorso where PrenotazioneId = $prenotazioneId";
	// 	$result = $db->query_first($sql);

	// 	$sql = "UPDATE RT_MobileCaricoPasseggeri SET Caricati = 1, AutistaId = $idAutista Where
	// 	PrenotazioneId = $nuovaPrenotazioneId and BusId = $busId and BusNumero = $busNumero and TipoServizio = 'Bus' and CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza'";
	// 	$result = $db->query($sql);

	// 	$sql = "SELECT * FROM RT_MobileCaricoPasseggeri Where
	// 	PrenotazioneId = $nuovaPrenotazioneId and BusId = $busId and BusNumero = $busNumero and TipoServizio = 'Bus' and CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza'";

	// 	$tempPr = $db->query_first($sql);
	// 	$sql = "UPDATE RT_MobileCarico SET Caricati = Caricati + ".$tempPr['Pax']." Where
	// 			FermataNome = \"".$tempPr['FermataPartenza']."\" and Comune = \"".$tempPr['ComunePartenza']."\" and BusId = $busId and BusNumero = $busNumero and TipoServizio = 'Bus' and CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza'";
	// 	$result = $db->query($sql);

	// 	if(!($result['ComuneSalitaId'] == $comunePickup && $result['FermataSalitaId'] == $fermataPickup && $result['ComuneDiscesaId'] == $comuneDropoff &&
	// 			$result['FermataDiscesaId'] == $fermataDropoff && $corsaAndataId == $result['CorsaId'] && $dataAndata == $result['CorsaDataPartenza'])){

	// 		$sql = "SELECT * FROM RT_MobileCaricoPasseggeri Where
	// 		PrenotazioneId = $prenotazioneId and BusId = $busId and BusNumero = $busNumero and TipoServizio = 'Bus' and CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza'";

	// 		$tempPr = $db->query_first($sql);
	// 		$sql = "UPDATE RT_MobileCarico SET Caricati = Caricati - ".$tempPr['Pax']." Where
	// 			FermataNome = \"".$tempPr['FermataPartenza']."\" and Comune = \"".$tempPr['ComunePartenza']."\" and BusId = $busId and BusNumero = $busNumero and TipoServizio = 'Bus' and CorsaId = $corsaId and CorsaDataPartenza = '$corsaDataPartenza'";
	// 		$result = $db->query($sql);

	// 	}



	$mobileObj->selectUser($idAutista);
	echo json_encode(array('result' => $nuovaPrenotazioneId));
}

function caricoPrenotazione()
{
	$prenotazioneId = $_POST['prenotazioneId'];
	$prenotazioneNuovaId = $_POST['prenotazioneNuovaId'];
	$corsaIdS = $_POST['corsaId'];
	$corsaDataPartenzaS = $_POST['corsaDataPartenza'];

	$trattaIdS = $_POST['trattaId'];
	$idAutista = $_POST['idAutista'];
	$corsaId = $_POST['corsaId'];
	$busNumero = $_POST['busNumero'];
	$tipoServizio = $_POST['tipoServizio'];
	$busId = $_POST['busId'];


	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$mobileObj->cancellaPrenotazione($prenotazioneId, $corsaIdS);


	$corse = $mobileObj->getBus($idAutista, $corsaDataPartenzaS);
	$corse = $mobileObj->caricoPrenotazione($prenotazioneNuovaId, $corsaIdS, $corsaDataPartenzaS, $busId, $busNumero, $tipoServizio, $prenotazioneId);


	echo json_encode(array('return' => 'true'));
}

function contabilita()
{
	$corsaIdS = $_POST['corsaId'];
	$corsaDataPartenzaS = $_POST['corsaDataPartenza'];

	$busId = $_POST['busId'];

	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;


	$totPrevisto = $mobileObj->contabilitaPrevisto($corsaIdS, $corsaDataPartenzaS, $busId);
	if ($totPrevisto == null) {
		$totPrevisto = 0;
	}
	$pos = $mobileObj->contabilitaPos($corsaIdS, $corsaDataPartenzaS, $busId);
	if ($pos == null) {
		$pos = 0;
	}
	$contanti = $mobileObj->contabilitaContanti($corsaIdS, $corsaDataPartenzaS, $busId);
	if ($contanti == null) {
		$contanti = 0;
	}
	$tot = $contanti + $pos;

	$residuoCarico = $mobileObj->contabilitaResiduoCarico($corsaIdS, $corsaDataPartenzaS, $busId);
	if ($residuoCarico == null) {
		$residuoCarico = 0;
	}
	$residuoTotale = $mobileObj->contabilitaResiduoTotale($corsaIdS, $corsaDataPartenzaS, $busId);
	if ($residuoTotale == null) {
		$residuoTotale = 0;
	}

	$c = $mobileObj->contabilitaContantiLista($corsaIdS, $corsaDataPartenzaS, $busId);
	$p = $mobileObj->contabilitaPOSLista($corsaIdS, $corsaDataPartenzaS, $busId);

	echo json_encode(array('totPrevisto' => $totPrevisto, 'pos' => $pos, 'contanti' => $contanti, 'tot' => $tot, 'residuoCarico' => $residuoCarico, 'residuoTot' => $residuoTotale, 'listaContanti' => $c, 'listaPOS' => $p));
}

function cerca()
{
	$nomeCliente = $_POST['nomeCliente'];
	$biglietto = $_POST['biglietto'];

	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$rows = $mobileObj->cerca($nomeCliente, $biglietto);

	echo json_encode(array('prenotazioni' => $rows));
}

function getInfo()
{
	$corsaIdS = $_POST['corsaId'];
	$corsaDataPartenzaS = $_POST['corsaDataPartenza'];

	$prenotazioneId = $_POST['prenotazioneId'];
	$busNumero = $_POST['busNumero'];
	$tipoServizio = $_POST['tipoServizio'];
	$busId = $_POST['busId'];

	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$rows = $mobileObj->getInfo($corsaIdS, $corsaDataPartenzaS, $busId, $busNumero, $tipoServizio, $prenotazioneId);

	echo json_encode(array('info' => $rows));
}

function getInfoPrenotazione()
{
	$prenotazioneId = $_POST['prenotazioneId'];

	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$rows = $mobileObj->getInfoPrenotazione($prenotazioneId);

	echo json_encode(array('info' => $rows));
}
function getNotePrenotazione()
{
	$prenotazioneId = $_POST['prenotazioneId'];

	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$rows = $mobileObj->getNotePrenotazione($prenotazioneId);

	echo json_encode(array('note' => $rows));
}

function getComuni()
{
	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$rowsPickup = $mobileObj->getAllPickup();
	$rowsDropoff = $mobileObj->getAllDropOff();
	echo json_encode(array('pickup' => $rowsPickup, 'dropoff' => $rowsDropoff));
}

function getFermate()
{
	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$result = $mobileObj->getFermateGrafo($_POST['corsaId'], $_POST['comunePickup'], $_POST['comuneDropoff'], $_POST['lineaId']);
	// 	$rowsPickup = $mobileObj->getFermateByComune($_POST['corsaId'], $_POST['comunePickup'], 'P');
	// 	$rowsDropoff = $mobileObj->getFermateByComune($_POST['corsaId'], $_POST['comuneDropoff'], 'D');
	echo json_encode(array('pickup' => $result['pickup'], 'dropoff' => $result['dropoff']));
}

function getPosti()
{
	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$result = $mobileObj->getPostiBus($_POST['corsaId'], $_POST['corsaDataPartenza'], $_POST['tipoServizio'], $_POST['busId'], $_POST['busNumero']);
	echo json_encode(array('result' => $result));
}


function getInfoIncasso()
{
	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$result = $mobileObj->getInfoIncasso($_POST['prenotazione'], $_POST['busId']);
	echo json_encode(array('result' => $result));
}

function getInfoFoglioViaggio()
{
	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$result = $mobileObj->getInfoFoglioViaggio($_POST['busId']);
	$result['Spese'] = $mobileObj->getSpeseFoglioViaggio($_POST['busId']);
	echo json_encode(array('result' => $result));
}

function updateInfoFoglioViaggio()
{
	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$result = $mobileObj->updateInfoFoglioViaggio($_POST['targa'], $_POST['kmPartenza'], $_POST['kmArrivo'], $_POST['busId'], $_POST['idAutista']);
	echo json_encode(array('result' => $result));
}

function getParametriSpesa()
{
	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$resultTipo = $mobileObj->getTipoSpesa();
	$resultPagamento = $mobileObj->getPagamentoSpesa();
	echo json_encode(array('tipo' => $resultTipo, 'pagamento' => $resultPagamento));
}

function modificaParametriSpesa()
{
	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$resultTipo = $mobileObj->getTipoSpesa();
	$resultPagamento = $mobileObj->getPagamentoSpesa();
	$resultSpesa = $mobileObj->getSpesa($_POST['spesaId']);

	echo json_encode(array('tipo' => $resultTipo, 'pagamento' => $resultPagamento, 'spesa' => $resultSpesa));
}


function saveSpesa()
{
	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$result = $mobileObj->saveSpesa($_POST['busId'], $_POST['idAutista'], $_POST['tipo'], $_POST['km'], $_POST['importo'], $_POST['litri'], $_POST['pagamento'], $_POST['spesaId']);

	echo json_encode(array('result' => $result));
}

function inviaTicketEmail()
{
	global $user;

	$prenotazioneId = $_POST['prenotazioneId'];
	$autistaId = $_POST['autistaId'];

	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$mobileObj->selectUser($autistaId);

	$result = $mobileObj->inviaTicketEmail($prenotazioneId);

	echo json_encode(array('result' => true, 'message' => $result));
}
function stampaPdf()
{
	global $user, $db;

	$db = new Database();
	$db->connect();
	$prenotazioneId = $_POST['prenotazioneId'];
	$corsaId = $_POST['corsaId'];
	$dataPartenza = $_POST['dataPartenza'];
	$prenotazioneTitoloId = $_POST['prenotazioneTitoloId'];

	if (!isset($_POST['prenotazioneTitoloId'])) {
		$sql = "SELECT * FROM `RT_PrenotazioneTitolo` WHERE PrenotazioneId = $prenotazioneId  ORDER BY `RT_PrenotazioneTitolo`.`PrenotazioneTitoloId` DESC";
		$info = $db->query_first($sql);
		$prenotazioneTitoloId = $info['PrenotazioneTitoloId'];
	}



	stampa_titoli_di_viaggio($prenotazioneId, $dataPartenza, $corsaId, 'Andata', $prenotazioneTitoloId, 'E', true, null, true);
	//exit();
}

function stampaCartaceoPdf()
{
	global $user, $db;
	$db = new Database();
	$db->connect();
	$prenotazioneId = $_POST['prenotazioneId'];
	$corsaId = $_POST['corsaId'];
	$dataPartenza = $_POST['dataPartenza'];
	$prenotazioneTitoloId = $_POST['prenotazioneTitoloId'];
	if (!isset($_POST['prenotazioneTitoloId'])) {
		$sql = "SELECT * FROM `RT_PrenotazioneTitolo` WHERE PrenotazioneId = $prenotazioneId  ORDER BY `RT_PrenotazioneTitolo`.`PrenotazioneTitoloId` DESC";
		$info = $db->query_first($sql);
		$prenotazioneTitoloId = $info['PrenotazioneTitoloId'];
	}

	stampa_titoli_di_viaggio_cartaceo($prenotazioneId, $dataPartenza, $corsaId, 'Andata', $prenotazioneTitoloId, 'E', true, null, true);
	//exit();
}

function getComune($comuneId, $db)
{
	$sql = "Select * FROM Comune WHERE ComuneId = $comuneId";
	$comune = $db->query_first($sql);
	return $comune;
}

function creaNomeTratta($partenzaId, $destinazioneId, $db)
{
	$partenza = getComune($partenzaId, $db);
	$destinazione = getComune($destinazioneId, $db);

	return $partenza['Comune'] . " - " . $destinazione['Comune'];
}

function calcolaOre($orarioPartenza, $orarioArrivo)
{
	// Crea oggetti DateTime per le due variabili
	$partenzaDateTime = new DateTime($orarioPartenza);
	$arrivoDateTime = new DateTime($orarioArrivo);

	// Calcola la differenza tra le due date
	$differenza = $partenzaDateTime->diff($arrivoDateTime);

	// Ottieni il numero totale di ore dalla differenza
	$oreTotali = $differenza->h;

	return $oreTotali;
}

/**
 * Prenota un biglietto a bordo.
 * Gestisce sia la creazione che la modifica di una prenotazione effettuata direttamente a bordo.
 * Riceve i dati tramite $_POST e restituisce l'id della nuova prenotazione in formato JSON.
 */
function prenotaBigliettoABordo()
{
	global $user, $db;

	$db = new Database();
	$db->connect();

	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	// Recupero i dati della prenotazione libera se presente
	$Libera = $_POST['PrenotazioneLibera'];
	$DataPartenzaLibera = $_POST['DataPartenza']; 
	$DataPartenzaLiberaF = date('d/m/Y', strtotime($_POST['DataPartenza']));
	$OrarioPartenzaLibera = $_POST['OrarioPartenzaLibera'];
	$OrarioArrivoLibera = $_POST['OrarioArrivoLibera'];
	$BarcaLibera = $_POST['BarcaLibera'];
	$TitoloLibera = $_POST['TitoloLibera'];
	$LiberaImporto = $_POST['LiberaImporto'];

	// Normalizza $LiberaImporto nel formato 200,00
	if (is_numeric($LiberaImporto)) {
		// Se è già numerico, formatta con 2 decimali e virgola
		$LiberaImporto = number_format($LiberaImporto, 2, ',', '');
	} else {
		// Se contiene un punto come separatore decimale, sostituisci con virgola
		if (strpos($LiberaImporto, '.') !== false) {
			$LiberaImporto = str_replace('.', ',', $LiberaImporto);
		}
		// Se non contiene né punto né virgola, aggiungi ,00
		if (strpos($LiberaImporto, ',') === false && strpos($LiberaImporto, '.') === false) {
			$LiberaImporto .= ',00';
		}
	}

	// Recupero fermata e comune andata
	$tempP = explode('_', $_POST['PickupId']);
	$tempD = explode('_', $_POST['DropoffId']);
	$comunePickup = $tempP[0];
	$fermataPickup = isset($tempP[1]) ? $tempP[1] : null;
	$comuneDropoff = $tempD[0];
	$fermataDropoff = isset($tempD[1]) ? $tempD[1] : null;

	// Recupero fermata e comune ritorno (se viaggio di tipo 2)
	if ($_POST['TipoViaggio'] == 2) {
		$tempPR = explode('_', $_POST['PickupIdR']);
		$tempDR = explode('_', $_POST['DropoffIdR']);
		$comunePickupR = $tempPR[0];
		$fermataPickupR = $tempPR[1];
		$comuneDropoffR = $tempDR[0];
		$fermataDropoffR = $tempDR[1];
	} else {
		$comunePickupR = null;
		$fermataPickupR = null;
		$comuneDropoffR = null;
		$fermataDropoffR = null;
	}

	$dataAndata = $_POST['DataPartenza'];
	$daraRitorno = (isset($_POST['DataPartenzaR']) && $_POST['DataPartenzaR'] != '') ? $_POST['DataPartenzaR'] : null;
	$corsaAndataId = $_POST['CorsaId'];
	$corsaRitornoId = (isset($_POST['CorsaIdR']) && $_POST['CorsaIdR'] != '') ? $_POST['CorsaIdR'] : 0;

	$congiunti = $_POST['Congiunti'];
	$CodicePrenotazione = (isset($_POST['CodicePrenotazione']) && $_POST['CodicePrenotazione'] != '') ? $_POST['CodicePrenotazione'] : null;
	$PrenotazioneId = (isset($_POST['PrenotazioneId']) && $_POST['PrenotazioneId'] != '') ? $_POST['PrenotazioneId'] : null;
	$TipoBigliettoIdData = $_POST['TipoBigliettoIdData'];

	// Inizializzazione array biglietti
	$etichette = array();
	$biglietti_prenotati = array();
	$biglietti_aumento = array();
	$biglietti_riduzione = array();
	$bigliettiId = array();

	// Gestione passeggeri e biglietti
	if (isset($_POST['passangersData'])) {
		$count = 0;
		foreach ($_POST['passangersData'] as $key => $value) {
			$sql = "SELECT * FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = $key";
			$tipo = $db->query_first($sql);

			$etichette[$count] = $key . '_' . $TipoBigliettoIdData[$key];
			$biglietti_prenotati[$count] = $value; 
			$biglietti_aumento[$count] = ($tipo['OccupaPosto'] == 1 && $Libera == 1) ? $LiberaImporto : 0;
			$biglietti_riduzione[$count] = 0;
			$bigliettiId[$count] = $key;
			$count++;
		}

	} else {
		// Default: passeggero a bordo
		$etichette[0] = '29_Passeggero+a+bordo';
		$biglietti_prenotati[0] = $_POST['Posti'];
		$biglietti_aumento[0] = 0;
		$biglietti_riduzione[0] = 0;
		$bigliettiId[0] = '29';
	}

	// Recupero l'autista
	$idAutista = $_POST['AutistaId'];

	// Recupero il gestore dell'operatore associato all'autista
	if (isset($_POST['GestoreIdRef'])) {
		$gestoreIdRef = $_POST['GestoreIdRef'];
	} else {
		$sql = "SELECT o.GestoreId 
				FROM RT_Autisti a
				LEFT JOIN Operatore o ON o.OperatoreId = a.OperatoreId
				WHERE a.AutistiId = $idAutista";
		$row = $db->query_first($sql);
		$gestoreIdRef = $row['GestoreId'];
	}

	// Dati cliente
	$clienteNome = isset($_POST['nome']) ? $_POST['nome'] : 'Prenotazione ' . date('YmdHi');
	$clientePrefisso = isset($_POST['prefisso']) ? $_POST['prefisso'] : '';
	$clienteCellulare = isset($_POST['tel']) ? $_POST['tel'] : '';
	$clienteCellulareFamiliare = '';
	$clienteEmail = $_POST['Email'];
	$clienteSessoId = 3;
	$clienteEta = 18;
	$notaAndata = null;
	$notaRitorno = null;
	$tipoViaggio = $_POST['TipoViaggio'];
	$tipoTour = $_POST['TipoTour'];

	// Costruzione array passeggeri
	$passeggeri = array();
	if (isset($_POST['passangersData'])) {
		$principale = true;
		foreach ($_POST['passangersData'] as $key => $tipoBiglietto) {
			$sql = "SELECT * FROM RT_TipologiaBiglietto WHERE TipologiaBigliettoId = $key";
			$tipo = $db->query_first($sql);
			for ($i = 1; $i <= $tipoBiglietto; $i++) {
				$p = array();
				$p['Cognome'] = $_POST['TipoBigliettoIdData'][$key];
				$p['Nome'] = $_POST['TipoBigliettoIdData'][$key];
				$p['SessoId'] = '3';
				$p['Eta'] = '18';
				$p['TipoBigliettoId'] = $key;
				// Il primo passeggero principale se il tipo biglietto occupa posto
				if ($principale && $tipo['OccupaPosto'] == 1) {
					$p['Principale'] = 1;
					$principale = false;
				} else {
					$p['Principale'] = 0;
				}
				$passeggeri[] = $p;
			}
		}
	} else {
		// Default: passeggeri generici
		for ($i = 1; $i <= $_POST['Posti']; $i++) {
			$p = array();
			$p['Cognome'] = 'Passeggero ' . $i;
			$p['Nome'] = 'Passeggero ' . $i;
			$p['SessoId'] = '3';
			$p['Eta'] = '18';
			$p['TipoBigliettoId'] = '29';
			$p['Principale'] = ($i == 0) ? 1 : 0;
			$passeggeri[$i - 1] = $p;
		}
	}

	// Costruzione array biglietti per la funzione prenota
	$count = 0;
	foreach ($etichette as $entry) {
		$arr_biglietti_prenotati[$entry] = $biglietti_prenotati[$count];
		$arr_biglietti_aumento[$bigliettiId[$count]]['Valore'] = $biglietti_aumento[$count];
		$arr_biglietti_aumento[$bigliettiId[$count]]['Note'] = '';
		$arr_biglietti_riduzione[$bigliettiId[$count]]['Valore'] = $biglietti_riduzione[$count];
		$arr_biglietti_riduzione[$bigliettiId[$count]]['Note'] = '';
		$count++;
	}

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$mobileObj->selectUser($idAutista);
	$_SESSION['modal'] = 1;


	// Se si tratta di una prenotazione libera, aggiorna i dati della prenotazione
	if ($Libera) {
		
		/********Inizio Prenotazione Libera ************/
		if (isset($_POST['PrenotazioneId']) && $_POST['PrenotazioneId'] != '') {
			$modifica = 1;
			$sql = "SELET * FROM RT_Prenotazione WHERE PrenotazioneId = " . $_POST['PrenotazioneId'];
			$OldPrenotazione = $db->query_first($sql);
			$sql = "SELECT * FROM RT_PrenotazionePercorso WHERE PrenotazioneId = " . $_POST['PrenotazioneId'];
			$OldPercorso = $db->query_first($sql);
			$sql = "SELECT * FROM RT_Corsa WHERE CorsaId = ".$OldPercorso['CorsaId'];
			$flottaRowTemp = $db->query_first($sql);
			$OldFlottaId = $flottaRowTemp['FlottaDefaultId'];
			$OldDataPartenzaA = $OldPercorso['CorsaDataPartenza'];
			$OldOrarioDiscesa = date('H:i', strtotime($OldPercorso['DataOraDiscesa']));
			$OldOrarioSalita = date('H:i', strtotime($OldPercorso['DataOraSalita']));
			$OldCorsaPartenzaA = $OldPercorso['CorsaId'];
		} else {
			$modifica = 0;
		}
		
		if ($modifica == 1 && $DataPartenzaLibera == $OldDataPartenzaA && $BarcaLibera == $OldFlottaId
			&& $OrarioPartenzaLibera == $OldOrarioSalita && $OrarioArrivoLibera == $OldOrarioDiscesa) {
			$corsaAndataId = $CorsaAndata = $OldCorsaPartenzaA;
		} else if ($modifica == 1 && ($DataPartenzaLibera != $OldDataPartenzaA || $BarcaLibera != $OldFlottaId
			|| $OrarioPartenzaLibera != $OldOrarioSalita || $OrarioArrivoLibera != $OldOrarioDiscesa)) {
			$corsaAndataId = $CorsaAndata = $OldCorsaPartenzaA;
			$corsaLibera = array();
			if(isset($TitoloLibera)) {
				$corsaLibera['CorsaNome'] = $TitoloLibera;
			} else {
				$corsaLibera['CorsaNome'] = 'Libera ' . $DataPartenzaLiberaF;
			}
			$corsaLibera['FlottaDefaultId'] = $BarcaLibera;
			$sql = "SELECT * FROM RT_Flotta WHERE FlottaId = $BarcaLibera";
			$flotta = $db->query_first($sql);
			$corsaLibera['TipologiaBusDefaultId'] = $flotta['TipologiaBusId'];
			$corsaLibera['AttivaDal'] = $DataPartenzaLibera;
			$corsaLibera['AttivaAl'] = $DataPartenzaLibera;
			$corsaLibera['VendibileDal'] = $DataPartenzaLibera;
			$corsaLibera['VendibileAl'] = $DataPartenzaLibera;
			$corsaLibera['OrarioPartenza'] = $OrarioPartenzaLibera;
			$corsaLibera['OrarioArrivo'] = $OrarioArrivoLibera;
			$corsaLibera = $storico->operazioni_update($corsaLibera, $user);

			$db->update("RT_Corsa", $corsaLibera, "CorsaId=" . $CorsaAndata);
		} else {
			//creazione corsa
			$corsaLibera = array();
			$corsaLibera['LineaId'] = 14;
			if(isset($TitoloLibera)) {
				$corsaLibera['CorsaNome'] = $TitoloLibera;
			} else {
				$corsaLibera['CorsaNome'] = 'Libera ' . $DataPartenzaLiberaF;
			}
			$corsaLibera['FlottaDefaultId'] = $BarcaLibera;
			//recupero tipologia bus dalla flotta
			$sql = "SELECT * FROM RT_Flotta WHERE FlottaId = $BarcaLibera";
			$flotta = $db->query_first($sql);
			$corsaLibera['TipologiaBusDefaultId'] = $flotta['TipologiaBusId'];
			$corsaLibera['CorsaPeso'] = 1;
			$corsaLibera['AttivaDal'] = $DataPartenzaLibera;
			$corsaLibera['AttivaAl'] = $DataPartenzaLibera;
			$corsaLibera['VendibileDal'] = $DataPartenzaLibera;
			$corsaLibera['VendibileAl'] = $DataPartenzaLibera;
			$corsaLibera['OrePrimaStopVendita'] = '00:30:00';
			$corsaLibera['IncludiFeriale'] = 1;
			$corsaLibera['IncludiPrefestivo'] = 1;
			$corsaLibera['IncludiFestivo'] = 1;
			$corsaLibera['OrarioPartenza'] = $OrarioPartenzaLibera;
			$corsaLibera['OrarioArrivo'] = $OrarioArrivoLibera;
			$corsaLibera['NextDay'] = 0;
			$corsaLibera = $storico->operazioni_insert($corsaLibera, $user);
			$corsaAndataId = $CorsaAndata = $db->insert("RT_Corsa", $corsaLibera);
			//inserisco i giorni della settimana della corsa
			for ($i = 1; $i <= 7; $i++) {
				$corsaSettLibera = array();
				$corsaSettLibera['CorsaId'] = $CorsaAndata;
				$corsaSettLibera['SettimanaId'] = $i;
				$corsaSettLibera = $storico->operazioni_insert($corsaSettLibera, $user);
				$db->insert("RT_CorsaSettimana", $corsaSettLibera);
			}
		}

		//verifica esistenza tratta
		$sql = "SELECT Tratta.TrattaId, Fermata1.FermataId AS FermataIdPartenza, Fermata2.FermataId AS FermataIdDestinazione
				FROM RT_Tratta AS Tratta
				JOIN RT_Fermata AS Fermata1 ON Tratta.TrattaId = Fermata1.TrattaId
				JOIN RT_Fermata AS Fermata2 ON Tratta.TrattaId = Fermata2.TrattaId
				WHERE Fermata1.ComuneId = " . $comunePickup . " AND Fermata1.IsPickup = 1 
				AND Fermata2.ComuneId = " . $comuneDropoff . " AND Fermata2.IsDropOff = 1 
				AND Tratta.LineaId = 14";

		$rowTrattaLibera = $db->query_first($sql);
		if (isset($rowTrattaLibera['TrattaId']) && $rowTrattaLibera['TrattaId'] > 0) {
			$fermataPickup = $FermataAndataP = $rowTrattaLibera['FermataIdPartenza'];
			$fermataDropoff = $FermataAndataD = $rowTrattaLibera['FermataIdDestinazione'];
		} else {
			//creazione tratta
			$trattaLibera = array();
			$trattaLibera['LineaId'] = 14;
			$trattaLibera['TrattaNome'] = creaNomeTratta($comunePickup, $comuneDropoff, $db);
			$trattaLibera['TrattaPeso'] = 1;
			$trattaLibera['NodoPeso'] = 1;
			$trattaLibera['TrattaTipoId'] = 1;
			$trattaLibera['MezzoId'] = 1;
			$trattaLibera['TrattaDirezioneId'] = 1;
			$trattaLibera['DaConfermare'] = 0;
			$trattaLibera['KmTratta'] = calcolaOre($OrarioPartenzaLibera, $OrarioArrivoLibera);
			$trattaLibera['TipologiaBusDefaultId'] = null;
			$trattaLibera = $storico->operazioni_insert($trattaLibera, $user);
			$trattaLiberaId = $db->insert("RT_Tratta", $trattaLibera);

			//creazione fermata	partenza
			$fermataLibera = array();
			$fermataLibera['TrattaId'] = $trattaLiberaId;
			$fermataLibera['ComuneId'] = $comunePickup;
			$fermataLibera['FermataNome'] = getComune($comunePickup, $db)['Comune'];
			$fermataLibera['FermataPeso'] = 1;
			$fermataLibera['IsPickup'] = 1;
			$fermataLibera['IsDropOff'] = 0;
			$fermataLibera['IsInterscambio'] = 0;
			$fermataLibera['IsBlackList'] = 0;
			$fermataLibera['IsDaConfermare'] = 0;
			$fermataLibera['ImportanzaTratta'] = 0;
			$fermataLibera['WebSelling'] = 0;
			$fermataLibera['KmInizioTratta'] = 0;
			$fermataLibera = $storico->operazioni_insert($fermataLibera, $user);
			$FermataAndataP = $db->insert("RT_Fermata", $fermataLibera);
			$fermataPickup = $FermataAndataP;

			//creazione fermata	destinazione
			$fermataLibera = array();
			$fermataLibera['TrattaId'] = $trattaLiberaId;
			$fermataLibera['ComuneId'] = $comuneDropoff;
			$fermataLibera['FermataNome'] = getComune($comuneDropoff, $db)['Comune'];
			$fermataLibera['FermataPeso'] = 2;
			$fermataLibera['IsPickup'] = 0;
			$fermataLibera['IsDropOff'] = 1;
			$fermataLibera['IsInterscambio'] = 0;
			$fermataLibera['IsBlackList'] = 0;
			$fermataLibera['IsDaConfermare'] = 0;
			$fermataLibera['ImportanzaTratta'] = 0;
			$fermataLibera['WebSelling'] = 0;
			$fermataLibera['KmInizioTratta'] = $trattaLibera['KmTratta'];
			$fermataLibera = $storico->operazioni_insert($fermataLibera, $user);
			$FermataAndataD = $db->insert("RT_Fermata", $fermataLibera);
			$fermataDropoff = $FermataAndataP;
		}

		//inserimento orari corsa
		//orario partenza
		$orarioLibero = array();
		$orarioLibero['Orario'] = $OrarioPartenzaLibera;
		$orarioLibero['GiorniAggiuntivi'] = 0;
		$orarioLibero['FermataId'] = $FermataAndataP;
		$orarioLibero['CorsaId'] = $CorsaAndata;
		if ($modifica == 1) {
			$sql = "SELECT *
					FROM `RT_Orario`
					WHERE CorsaId = $CorsaAndata
					ORDER BY OrarioId ASC;";
			$rowOrarioLibera = $db->query_first($sql);
			$orarioLibero = $storico->operazioni_update($orarioLibero, $user);
			$db->update("RT_Orario", $orarioLibero, "OrarioId = " . $rowOrarioLibera['OrarioId']);
		} else {
			$orarioLibero = $storico->operazioni_insert($orarioLibero, $user);
			$db->insert("RT_Orario", $orarioLibero);
		}
		//orario destinazione
		$orarioLibero = array();
		$orarioLibero['Orario'] = $OrarioArrivoLibera;
		$orarioLibero['GiorniAggiuntivi'] = 0;
		$orarioLibero['FermataId'] = $FermataAndataD;
		$orarioLibero['CorsaId'] = $CorsaAndata;
		if ($modifica == 1) {
			$sql = "SELECT *
					FROM `RT_Orario`
					WHERE CorsaId = $CorsaAndata
					ORDER BY OrarioId DESC;";
			$rowOrarioLibera = $db->query_first($sql);
			$orarioLibero = $storico->operazioni_update($orarioLibero, $user);
			$db->update("RT_Orario", $orarioLibero, "OrarioId = " . $rowOrarioLibera['OrarioId']);
		} else {
			$orarioLibero = $storico->operazioni_insert($orarioLibero, $user);
			$db->insert("RT_Orario", $orarioLibero);
		}
		/********FINE Prenotazione Libera ************/
	}

	// Se si tratta di una modifica, aggiorna la prenotazione precedente a "Sostituita"
	if (isset($_POST['PrenotazioneId'])) {
		$PrenotazionId = $_POST['PrenotazioneId'];
		$data_prenotazione = array('PrenotazioneStato' => 6);
		$db->update("RT_Prenotazione", $data_prenotazione, "PrenotazioneId = $PrenotazionId");
		$db->update("RT_PrenotazionePercorso", $data_prenotazione, "PrenotazioneId = $PrenotazionId");
	}

	// Effettua la prenotazione
	$nuovaPrenotazioneId = $mobileObj->prenota(
		$notaAndata,
		$notaRitorno,
		$arr_biglietti_prenotati,
		$arr_biglietti_riduzione,
		$arr_biglietti_aumento,
		$daraRitorno,
		$corsaRitornoId,
		$dataAndata,
		$corsaAndataId,
		$comunePickup,
		$fermataPickup,
		$comuneDropoff,
		$fermataDropoff,
		$tipoViaggio,
		$passeggeri,
		$clienteNome,
		$clienteCellulare,
		$clienteCellulareFamiliare,
		$clienteEmail,
		$clienteSessoId,
		$clienteEta,
		true,
		$congiunti,
		$CodicePrenotazione,
		$tipoTour,
		$comunePickupR,
		$fermataPickupR,
		$comuneDropoffR,
		$fermataDropoffR,
		$clientePrefisso,
		$gestoreIdRef,
		$Libera,
		$TitoloLibera
	);

	// Se modifica, copia i movimenti contabili e aggiorna lead Zoho
	if (isset($_POST['PrenotazioneId'])) {
		$data_prenotazione = array(
			'PrenotazioneId' => $nuovaPrenotazioneId,
			'GestoreIdRef' => $gestoreIdRef
		);
		$db->update("RT_PrenotazioneMovimento", $data_prenotazione, "PrenotazioneId = $PrenotazionId");

		// Aggiornamento lead Zoho
		$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = " . $PrenotazionId;
		$p = $db->query_first($sql);
		$dataUpdate = array(
			'ZohoLeadId' => $p['ZohoLeadId'],
			'ZohoDataUpdate' => $p['ZohoDataUpdate']
		);
		$db->update("RT_Prenotazione", $dataUpdate, "PrenotazioneId = $nuovaPrenotazioneId");
	}

	$mobileObj->selectUser($idAutista);

	// Risposta JSON con id della nuova prenotazione
	echo json_encode(['result' => $nuovaPrenotazioneId]);
}

function annullaABordo()
{
	$prenotazioneId = $_POST['PrenotazioneId'];
	$idAutista = $_POST['AutistaId'];

	$db = new Database();
	$db->connect();

	$mobileObj = new Mobile();
	$mobileObj->conn = $db;

	$mobileObj->selectUser($idAutista);
	$mobileObj->annulla($prenotazioneId);
	$mobileObj->selectUser($idAutista);

	echo json_encode(array('result' => $_POST['PrenotazioneId']));
}

function emettiABordo()
{
	global $user;
	$prenotazioneId = $_POST['PrenotazioneId'];
	$autistaId = $_POST['AutistaId'];
	$corsaId = $_POST['CorsaId'];
	$pagamentoId = $_POST['pagamentoId'];
	$dataPartenza = date('Y-m-d', strtotime($_POST['DataPartenza']));
	$scontrinoInvio = $_POST['scontrinoInvio'];


	$db = new Database();
	$db->connect();
	$mobileObj = new Mobile();
	$mobileObj->conn = $db;
	$mobileObj->selectUser($autistaId);
	$movimentoId = $mobileObj->incassoABordo($prenotazioneId, $autistaId, $pagamentoId);
	$output = $mobileObj->EmettiABordo($prenotazioneId, $autistaId);

	$dispo = new Disponibilita();
	$dispo->aggiornaDisponibilita($corsaId, $dataPartenza);

	if($scontrinoInvio == 1) {
		$mobileObj->fiscalGatewayEmettiRicevuta($movimentoId);
		$mobileObj->inviaScontrinoEmail($movimentoId);
	}


	echo json_encode(array('result' => $output));
}

// Valida e marca ticket ANDATA
function validaAndata() {
	global $db;
	$prenotazioneId = isset($_POST['prenotazioneId']) ? intval($_POST['prenotazioneId']) : 0;
	if ($prenotazioneId <= 0) {
		echo json_encode(array('result' => false));
		return;
	}
	$sql = "UPDATE RT_PrenotazionePercorso SET ValidatoMolo = 1 WHERE PrenotazioneId = $prenotazioneId AND Direzione = 'A'";
	$res = $db->query($sql);
	if ($res) {
		echo json_encode(array('result' => true));
	} else {
		echo json_encode(array('result' => false));
	}
}

// Valida e marca ticket RITORNO
function validaRitorno() {
	global $db;
	$prenotazioneId = isset($_POST['prenotazioneId']) ? intval($_POST['prenotazioneId']) : 0;
	if ($prenotazioneId <= 0) {
		echo json_encode(array('result' => false));
		return;
	}
	$sql = "UPDATE RT_PrenotazionePercorso SET ValidatoMolo = 1 WHERE PrenotazioneId = $prenotazioneId AND Direzione = 'R'";
	$res = $db->query($sql);
	if ($res) {
		echo json_encode(array('result' => true));
	} else {
		echo json_encode(array('result' => false));
	}
}
