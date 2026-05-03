<?php
global $user;

$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();

$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_.'class.Sms.php');

$db = new Database();
$db->connect();

$Sms = new Sms();
$Sms->conn = $db;

$PagamentoId = $_POST['pagamentoId'];

$sql = "SELECT * FROM RT_PagamentoTipo WHERE PagamentoTipoId = $PagamentoId";
$pagamento = $db->query_first($sql);

$prenotazione_wizard = unserialize($_SESSION['PRENOTAZIONE_WIZARD']);
$prenotazione_wizard->conn = $db;
$prenotazione_wizard->inizializzaDatiGenerali();
$DatiGeneraliArr = $prenotazione_wizard->DatiGenerali;
$DatiTotaliArr = $prenotazione_wizard->GetTotaliPrenotazione();

$find = array(
		'{CLIENTE}',
		'{CODICE_PRENOTAZIONE}',
		'{TOTALE}'
);

$replace = array(
		$DatiGeneraliArr['ClienteNome'],
		$DatiGeneraliArr['CodicePrenotazione'],
		number_format($DatiTotaliArr['TotaleResiduo'], 2, ",", ".") . " euro"
);

$Prefisso = $DatiGeneraliArr['ClienteCellularePrefisso'];
$Cellulare = $DatiGeneraliArr['ClienteCellulare'];
$Messaggio = str_replace($find, $replace, $pagamento['MsgSMSTesto']);

$Sms->SmsCliccaTel($Prefisso, $Cellulare, $Messaggio);
?>