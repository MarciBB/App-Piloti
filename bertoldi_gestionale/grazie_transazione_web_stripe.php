<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_= Config::$modulespath;
$classespath_= Config::$classespath;
$errors = new Errors();
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
use Stripe\Stripe;

global $user, $dizionario;
$PrenotazioneId=null;
if (!isset($_REQUEST['prenotazioneId']))
{
    die("Nessuna prenotazione");
}
else
    $PrenotazioneId=$_REQUEST['prenotazioneId'];
if (!isset($_REQUEST['session_id'])) {
	die("Nessuna prenotazione");
} else {
	$db = new Database();
	$db->connect();
	
	$sessionId = $_REQUEST['session_id'];
	$sql = "SELECT * FROM RT_PrenotazioneTransazione WHERE PrenotazioneId = $PrenotazioneId";
	
	$tempRow = $db->query_first($sql);

	if(!isset($tempRow['PrenotazioneId'])) {
		Stripe::setApiKey($config::$StripeSecretKey);
		$session = \Stripe\Checkout\Session::retrieve($sessionId);

		if(isset($session->customer)) {
			$customer = \Stripe\Customer::retrieve($session->customer);
			$email = $customer->email;
			$codiceTransazione = $customer->invoice_prefix;
			$payer_id = $customer->id;
		} else {
			$email = $session->customer_details->email;
			$codiceTransazione = $session->payment_intent;
			$payer_id = $email;
		}
		
		$sql = "SELECT * FROM RT_PrenotazionePasseggeri
		where Principale = 1 AND PrenotazioneId = $PrenotazioneId";
		$rowPasseggero = $db->query_first($sql);
		 
		$sql = "SELECT
		RT_Prenotazione.ClienteEmail,
		RT_Prenotazione.PrenotazioneId,
		RT_Prenotazione.ClienteNome,
		RT_Prenotazione.TotaleDaPagare
		FROM RT_Prenotazione
		WHERE RT_Prenotazione.PrenotazioneId = $PrenotazioneId";
		$rowP = $db->query_first($sql);
		 
		$transazione['PrenotazioneId'] = $PrenotazioneId;
		$transazione['TipoPagamentoId'] = 22;
		$transazione['CodiceTransazione'] = $codiceTransazione;
		$transazione['address_status'] = 'confirmed';
		$transazione['payer_status'] = 'verified';
		$transazione['first_name'] = $rowPasseggero['Nome'];
		$transazione['last_name'] = $rowPasseggero['Cognome'];
		$transazione['payer_email'] = $email;
		$transazione['payer_id'] = $payer_id;
		$transazione['payment_status'] = 'Completed';
		$transazione['payment_type'] = 'instant';
		$transazione['mc_gross'] = $session->amount_total/100;
		$transazione['ImportoPrenotazione'] = $rowP['TotaleDaPagare'];
		$transazione['Notificata'] = 0;
		
		$transazione['Cancella']=0;
		$transazione['Stato']=1;
		$db->insert("RT_PrenotazioneTransazione", $transazione);
	}
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>                            
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			<title><?=Config::$application_name?> Ver. <?=Config::$application_version?> - <?=Config::$application_company?></title>
			    
			<link rel="stylesheet" type="text/css" href="/css/reset.css" />
			<link rel="stylesheet" type="text/css" href="/css/style.css" />
			<link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css"/>
          	<script type="text/javascript" src="/js/jquery.min.js"></script>
			<script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
		</head>
		<style>
			#body{
				margin:20px;
			}
                        .pg_grazie{
                            padding-top:50px;
                        }
		</style>
		<body style="text-align: center;">
                    <div class="pg_grazie"><span id="body"><h2><p><strong>PAGAMENTO AVVENUTO CON SUCCESSO</strong></p><p>Il pagamento &egrave; andato a buon fine. <br />E' ora possibile chiudere la finestra corrente e stampare i titoli di viaggio.</p></h2></span></div>
		</body>
	</html>
	
	
<?php
}
?>