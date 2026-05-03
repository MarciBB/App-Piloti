<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
//$basepath=$_SERVER['DOCUMENT_ROOT'];
//include_once($basepath."/main_include.php");
ini_set('display_errors', 1);
ini_set('error_reporting', E_ERROR);
ini_set('max_execution_time', 36000); //300 seconds = 5

$classespath_=$basepath."/protected/classes/";
$modulespath_=$basepath."/protected/modules/";



include_once($classespath_.'class.Gestore.php');
include_once($classespath_.'class.QRCode.php');
include_once($classespath_.'PHPMailer/class.phpmailer.php');
include_once($classespath_.'PHPMailer/class.smtp.php');


global $user, $db, $voucherpath, $pdftemplate, $emailtemplate,$content_html;

// inizializzata la connessione
$db = new Database();
$db->connect();

$email_from = "info@braincomputing.com";
$oggetto = "Conferma prenotazione / Booking Confirm n. ".$CodiceItinerario;
invioEmail();
// ELENCO FUNZIONI
function invioEmail() {
    ini_set('display_errors', 1);
    echo "Invio Email";
	
	$email_from = "info@braincomputing.com";
	$oggetto = "Test invio email";
	$email_to = 'test@gmail.com';
	$content_html = 'Test di invio email '.date('Y-m-d');
	mail_attachment($email_from, $email_to, $oggetto, $content_html, null);

	
}

/* invia l'email */
function mail_attachment($from, $to, $subject, $message, $attachment) {
	global $db, $user;
	ini_set('display_errors', 1);
	//i dati per l'invio sono presenti nella tabella ODC
	$sql = "SELECT * FROM Odc";
	echo $sql;
	$odc = $db->query_first($sql);
	
    $mail= new PHPMailer(); // create the mail
    $mail->Subject = $subject;
	$mail->AddAddress($to);
	$mail->MsgHTML($message);
	$mail->IsSMTP();
	$mail->SMTPDebug  = 4;
	$mail->SMTPSecure = 'ssl';// SMTP account password
	$mail->SMTPAuth=true;
	$mail->IsHTML(true);
	
    // setto il from    
    $from = 'booking@onebus.it';//$odc['EmailSmtp'];
    $fromName = 'Onebus S.r.l.';
	$mail->SetFrom($from, $fromName);  

	$mail->Host = 'ex4.mail.ovh.net';//$odc['ServerSmtp'];				// Server SMTP
	$mail->Port = '587';//$odc['PortaSmtp'];				// Server SMTP Port
	$mail->Username = 'booking@onebus.it';//$odc['UserSmtp'];           	// SMTP account username
	$mail->Password = 'Website380.';//$odc['PwdSmtp'];      

	var_dump($mail);

    // SMTP account password
	$r = $mail->Send();  
	var_dump($r);
}

?>
