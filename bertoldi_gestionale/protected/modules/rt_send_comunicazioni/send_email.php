<?php
global $user;

$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();

$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_.'PHPMailer/class.phpmailer.php');
include_once($classespath_.'PHPMailer/class.smtp.php');

$db = new Database();
$db->connect();

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
	number_format($DatiTotaliArr['TotaleResiduo'], 2, ",", ".") . "&euro;"
	);

$Email = $DatiGeneraliArr['ClienteEmail'];
$Oggetto = str_replace($find, $replace, $pagamento['SubjectEmailTesto']);
$Messaggio = str_replace($find, $replace, $pagamento['MsgEmailTesto']);

//i dati per l'invio sono presenti nella tabella ODC
$sql = "SELECT * FROM Odc WHERE OdcId = $user->OdcId";
$odc = $db->query_first($sql);

$mail = new PHPMailer(); // create the mail
$mail->Subject = $Oggetto;
$mail->AddAddress($Email);
$mail->MsgHTML($Messaggio);
$mail->IsSMTP();
$mail->SMTPDebug  = 2;
$mail->SMTPSecure = 'ssl';// SMTP account password
$mail->SMTPAuth = true;
$mail->IsHTML(true);

// setto il from
$from = $odc['EmailSmtp'];
$fromName = $odc['NomeEmailSmtp'];
$mail->SetFrom($from, $fromName);

$mail->Host = $odc['ServerSmtp'];					// Server SMTP
$mail->Port = $odc['PortaSmtp'];					// Server SMTP Port
$mail->Username = $odc['UserSmtp'];           	// SMTP account username
$mail->Password = $odc['PwdSmtp'];            	// SMTP account password
$mail->Send();
?>