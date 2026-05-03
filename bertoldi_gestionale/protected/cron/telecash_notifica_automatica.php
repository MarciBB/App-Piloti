<?php
ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL);

$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_= Config::$modulespath;
$classespath_= Config::$classespath;
$errors = new Errors();
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.PrenotazioneMovimento.php");
include_once($classespath_."class.PagamentoTipo.php");
include_once($classespath_."class.PrefissoTelefono.php");

$db = new Database();
$db->connect();

// $raw_post_data = file_get_contents('php://input');
// $raw_post_array = explode('&', $raw_post_data);
// $myPost = array();

// foreach ($raw_post_array as $keyval) {
// 	$keyval = explode ('=', $keyval);
// 	if (count($keyval) == 2)
// 		$myPost[$keyval[0]] = urldecode($keyval[1]);
// }

// // read the post from PayPal system and add 'cmd'
// $req = 'cmd=_notify-validate';
// if(function_exists('get_magic_quotes_gpc')) {
// 	$get_magic_quotes_exists = true;
// }
// foreach ($myPost as $key => $value) {
// 	if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
// 		$value = urlencode(stripslashes($value));
// 	} else {
// 		$value = urlencode($value);
// 	}
// 	$req .= "&$key=$value";
// }

// // STEP 2: Post IPN data back to paypal to validate
// $PAYPAL_LINK = Config::$paypalLink;
// $ch = curl_init($PAYPAL_LINK);
// curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
// curl_setopt($ch, CURLOPT_POST, 1);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
// curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

// // In wamp like environments that do not come bundled with root authority certificates,
// // please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
// // of the certificate as shown below.
// // curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
// if( !($res = curl_exec($ch)) ) {
// 	// error_log("Got " . curl_error($ch) . " when processing IPN data");
// 	curl_close($ch);
// 	exit;
// }
// curl_close($ch);


// STEP 3: Inspect IPN validation result and act accordingly
// if (strcmp ($_POST['status'], "APPROVED") == 0) {
	
	$PrenotazioneId = $_POST['custom'];
	$CodiceTransazione = $_POST['oid'];
	$mc_gross = $_POST['chargetotal'];

	$sql = "SELECT * FROM RT_Prenotazione WHERE Stato = 1 AND Cancella = 0 AND (PrenotazioneStato = 1 OR PrenotazioneStato = 11 OR PrenotazioneStato = 13 OR (PrenotazioneStato = 3 AND TotaleResiduo > 0)) AND PrenotazioneId = $PrenotazioneId";

	$prenotazione = $db->query_first($sql);
	
	if (empty($prenotazione['PrenotazioneId'])) {
		die();
	}
	
	if ($prenotazione['Multi']) {
		$sql = "SELECT SUM(TotalePrenotazione) TotalePrenotazione, SUM(TotaleDaPagare) TotaleDaPagare, SUM(TotaleDaPagareMulti) TotaleDaPagareMulti, SUM(TotalePagato) TotalePagato, SUM(TotaleResiduo) TotaleResiduo FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $prenotazione['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $prenotazione['PrenotazioneStato'];
		$totaliImporti = $db->query_first($sql);
		
		if ($totaliImporti['TotaleDaPagareMulti'] != 0) {
			$totaliImporti['TotaleDaPagare'] = $totaliImporti['TotaleDaPagareMulti'];
		}
	} else {
		$sql = "SELECT SUM(TotalePrenotazione) TotalePrenotazione, SUM(TotaleDaPagare) TotaleDaPagare, SUM(TotalePagato) TotalePagato, SUM(TotaleResiduo) TotaleResiduo FROM RT_Prenotazione WHERE PrenotazioneId = " . $prenotazione['PrenotazioneId'];
		$totaliImporti = $db->query_first($sql);
	}
	
	if($prenotazione['PrenotazioneStato'] == 3){
		$totale_prenotazione = $totaliImporti['TotaleResiduo'];
	} else {
		$totale_prenotazione = $totaliImporti['TotaleResiduo'];
	}
	

	// verifico se l'importo nel db corrisponde all'importo della transazione
// 	if ($totale_prenotazione != $mc_gross) {
// 		die();
// 	}

	// verifico che non esista il codice transazione
	$sql = "SELECT PrenotazioneTransazioneWeb FROM RT_PrenotazioneTransazione WHERE CodiceTransazione = '$CodiceTransazione'";
	$row = $db->query_first($sql);

	if (empty($row['PrenotazioneTransazioneWeb']))
	{
		$payment_status = $_POST['status'];
		//verifico che lo stato sia completed
		if ($payment_status == 'APPROVED' || $payment_status == 'GENEHMIGT')
		{
			$data=null;
			$data['PrenotazioneId'] = $PrenotazioneId;
			$data['TipoPagamentoId'] = 17;
			$data['CodiceTransazione'] = $CodiceTransazione;
			$data['payment_type'] = $_POST['ccbrand'];
			$data['payment_status'] = $_POST['status'];
			$data['address_status'] = 'unconfirmed';
			$data['payer_status'] = 'unverified';
			$data['first_name'] = $prenotazione['ClienteNome'];
			$data['last_name'] = $prenotazione['ClienteNome'];
			$data['payer_email'] = $prenotazione['ClienteEmail'];
			$data['payer_id'] = $_POST['refnumber'];
			$data['mc_gross'] = $mc_gross;
			$data['ImportoPrenotazione'] = $totale_prenotazione;
			$transactionId = $db->insert("RT_PrenotazioneTransazione", $data);
		} else {
			$data=null;
			$data['PrenotazioneId'] = $PrenotazioneId;
			$data['TipoPagamentoId'] = 17;
			$data['CodiceTransazione'] = $CodiceTransazione;
			$data['payment_type'] = $_POST['ccbrand'];
			$data['payment_status'] = $_POST['status'];
			$data['address_status'] = 'unconfirmed';
			$data['payer_status'] = 'unverified';
			$data['first_name'] = $prenotazione['ClienteNome'];
			$data['last_name'] = $prenotazione['ClienteNome'];
			$data['payer_email'] = $prenotazione['ClienteEmail'];
			$data['payer_id'] = $_POST['refnumber'];
			$data['mc_gross'] = $mc_gross;
			$data['ImportoPrenotazione'] = $totale_prenotazione;
			$transactionId = $db->insert("RT_PrenotazioneTransazioneNonCompleta", $data);
		}
	}

// } elseif (strcmp ($res, "INVALID") == 0) {
// 	// log for manual investigation
// }
?>