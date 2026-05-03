<?php
include_once('c_path_include.php');
ini_set('display_errors', 1);

/* Configurazione log */
//$file = '/var/log/bertoldi_c_bookingProcessing.log';
//$time_start = microtime(true);

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] START\n', FILE_APPEND | LOCK_EX);
/* LOG */

$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$storico_include=$basepath.'/protected/classes/class.StoricoOperazioni.php';
$operatore_include=$basepath.'/protected/classes/class.Operatore.php';
$dt_include=$basepath.'/protected/classes/class.DT.php';

include_once($config_include);
include_once($database_include);
include_once($dt_include);
include_once($operatore_include);
include_once($storico_include);

global $user;
global $db, $dizionarioEmail;
$config = new Config();
$run = $config->loadCron($type);
global $checkCronBooking;
$checkCronBooking = true;

$classespath_ = $basepath."/protected/classes/";
$modulespath_ = $basepath."/protected/modules/";
include_once($classespath_.'class.Gestore.php');
include_once($classespath_.'class.QRCode.php');
include_once($classespath_.'PHPMailer/class.phpmailer.php');
include_once($classespath_.'PHPMailer/class.smtp.php');
require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdf.php");
require($basepath."/protected/modules/rt_previaggio/pdfclass/fpdi.php");
include_once($classespath_ . "class.ServiceFiscalGateway.php");
include_once($basepath . "/lang/email.php");
include_once($modulespath_."rt_previaggio/stampa_titoli_di_viaggio_func.php");
include_once($modulespath_."rt_previaggio/stampa_titoli_di_viaggio_coupon_func.php");

global $user, $db, $voucherpath, $pdftemplate, $emailtemplate,$content_html;

// inizializzata la connessione
$db = new Database();
$db->connect();

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] include e connessione db eseguita\n', FILE_APPEND | LOCK_EX);
/* LOG */

$sql="update RT_Prenotazione set TotalePagato=TotaleDaPagare, TotaleResiduo=0 where PrenotazioneStato=3 and TotalePagato=0";
$db->query($sql);

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] aggiornamento totali eseguito\n', FILE_APPEND | LOCK_EX);
/* LOG */

// setto il percorso dove salvare i pdf dei biglietti
$voucherpath = $basepath."/protected/pdfvoucher/";

// setto il percorso del template del biglietto
$pdftemplate = $basepath."/protected/modules/rt_previaggio/pdftemplate/voucherweb.pdf";

// setto il percorso del template da usare come contenuto del email di default
$emailtemplate = $basepath."/protected/modules/rt_previaggio/pdftemplate/voucher.html";
$fh = fopen($emailtemplate,"r");
$content_html = fread($fh,filesize($emailtemplate));
fclose($fh);
    
// recupero i titoli di viaggio da inviare via email
$sql = "SELECT
	RT_PrenotazioneTitolo.PrenotazioneTitoloId AS PrenotazioneTitoloId,
	RT_Prenotazione.PrenotazioneStato AS PrenotazioneStato,
	RT_Prenotazione.ClienteEmail AS ClienteEmail,
	RT_PrenotazioneTitolo.PrenotazioneId AS PrenotazioneId,
	RT_PrenotazioneTitolo.PrenotazioneNumeroId AS PrenotazioneNumeroId,
	RT_PrenotazioneDettaglio.CorsaId AS CorsaId,
	RT_PrenotazioneDettaglio.DataInizioItinerario AS DataInizioItinerario,
	RT_Prenotazione.CodicePrenotazione AS CodicePrenotazione,
	RT_Prenotazione.TipoViaggioId,
    RT_PrenotazioneTitolo.DataIns AS DataIns,
	RT_Prenotazione.Lingua AS Lingua
FROM
	(
		(
			(RT_PrenotazioneTitolo)
			INNER JOIN RT_Prenotazione ON RT_PrenotazioneTitolo.PrenotazioneId = RT_Prenotazione.PrenotazioneId
		)
		JOIN RT_PrenotazioneDettaglio ON (
			(
				RT_PrenotazioneDettaglio.PrenotazioneNumero = RT_PrenotazioneTitolo.PrenotazioneNumeroId
			)
		)
	)
WHERE
	RT_PrenotazioneTitolo.InviaTitolo = 1
AND RT_Prenotazione.ClienteEmail IS NOT NULL
AND RT_Prenotazione.ClienteEmail <> ''  AND RT_Prenotazione.ClienteEmail is not null
AND RT_PrenotazioneTitolo.TipoTitolo = 'E'
GROUP BY
RT_PrenotazioneTitolo.PrenotazioneNumeroId
ORDER BY
PrenotazioneId ASC";

$titoli = $db->fetch_array($sql);
print("<pre>");
print_r($titoli);


/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] recupero i titoli di viaggio da inviare via email\n', FILE_APPEND | LOCK_EX);
/* LOG */

$prenotazioneId_precedente = null;
$pdf_precedente = null;
$email_precendente = null;

$send = false;

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] inizio for each titoli\n', FILE_APPEND | LOCK_EX);
/* LOG */
echo "inizio ciclo a \n";
$dataAttuale = date('Y-m-d H:i');
foreach ($titoli as $titolo) {

	// setto il percorso del template da usare come contenuto del email in base alla lingua della prenotazione
	$vaucherFileName = (!isset($titolo['Lingua']) || $titolo['Lingua'] == 'it') ? 'voucher.html' : 'voucher_'.$titolo['Lingua'].'.html';
	$emailtemplate = $basepath."/protected/modules/rt_previaggio/pdftemplate/$vaucherFileName";
    $fh = fopen($emailtemplate,"r");
    $content_html = fread($fh,filesize($emailtemplate));
    fclose($fh);
	
	$dataIns = $titolo['DataIns'];
	$datetime = new DateTime($dataIns);
	
	if ($datetime->format('Y-m-d H:i') !== $dataAttuale) {
		$prenotazioneId = $titolo['PrenotazioneId'];
		$titoloId = $titolo['PrenotazioneTitoloId'];
		$corsaId = $titolo['CorsaId'];
		$codiceItinerario = $titolo['CodicePrenotazione'];
		$dataPartenza = $titolo['DataInizioItinerario'];
		$tipo_titolo = "E";
		
		echo "ciclo $codiceItinerario $titoloId \n";
		
		if ($prenotazioneId != $prenotazioneId_precedente) {
			echo("if 1\n");
			if ($send == true) {
						 echo("invia email $prenotazioneId_precedente \n");
						 
				$sqlCodiceItinerario = "Select * from RT_Prenotazione WHERE PrenotazioneId = ".$prenotazioneId_precedente;
				$codiceEmail = $db->query_first($sqlCodiceItinerario);
				invioEmail($pdf_precedente, $prenotazioneId_precedente, $email_precendente,$codiceEmail['CodicePrenotazione'], $db);
				$send = false;
				/* LOG */
				//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] invio email prenotazione '.$codiceItinerario.' \n', FILE_APPEND | LOCK_EX);
				/* LOG */
				
				$updInviaTitolo=$db->update('RT_PrenotazioneTitolo', $dataTitolo,"PrenotazioneId=$prenotazioneId_precedente");
			}
			
			$pdf_precedente = new FPDI();
			$pdf_precedente->AddPage();
			
			$prenotazioneId_precedente = $prenotazioneId;
			$email_precendente = $titolo['ClienteEmail'];
			$user = getUser($prenotazioneId);
				  
			
			$send = true;
		} else {
			$pdf_precedente->AddPage();
		}
		stampa_titoli_di_viaggio($prenotazioneId, $dataPartenza, $corsaId, 'Andata', $titoloId, $tipo_titolo, false, $pdf_precedente);
		$dataTitolo=array();
		$dataTitolo['InviaTitolo'] = 2;
		$dataTitolo['DataInvioTitolo'] = date('Y-m-d H:i:s');

		$updInviaTitolo = $db->update('RT_PrenotazioneTitolo', $dataTitolo,"PrenotazioneTitoloId = $titoloId");
    }         
}
echo "fine ciclo \n";
/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] fine for each titoli\n', FILE_APPEND | LOCK_EX);
/* LOG */

// invia l'ultima email quando sono finiti i titoli
if (isset($prenotazioneId_precedente)) {
	invioEmail($pdf_precedente, $prenotazioneId_precedente, $email_precendente,$codiceItinerario, $db);
	$updInviaTitolo=$db->update('RT_PrenotazioneTitolo', $dataTitolo,"PrenotazioneId=$prenotazioneId_precedente");
	echo "invio email ultima $prenotazioneId_precedente \n";
	/* LOG */
	//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] invio email ultimo titolo'.$codiceItinerario.' \n', FILE_APPEND | LOCK_EX);
	/* LOG */
}

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] invia l\'ultima email quando sono finiti i titoli\n', FILE_APPEND | LOCK_EX);
/* LOG */

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] END - Tempo esecuzione: ' . microtime(true) - $time_start . '\n', FILE_APPEND | LOCK_EX);
/* LOG */


//invio coupon di rimborso via email
// recupero i titoli di viaggio da inviare via email
$sql = "SELECT
	RT_PrenotazioneTitolo.PrenotazioneTitoloId AS PrenotazioneTitoloId,
	RT_Prenotazione.PrenotazioneStato AS PrenotazioneStato,
	RT_Prenotazione.ClienteEmail AS ClienteEmail,
	RT_PrenotazioneTitolo.PrenotazioneId AS PrenotazioneId,
	RT_PrenotazioneTitolo.PrenotazioneNumeroId AS PrenotazioneNumeroId,
	RT_PrenotazioneDettaglio.CorsaId AS CorsaId,
	RT_PrenotazioneDettaglio.DataInizioItinerario AS DataInizioItinerario,
	RT_Prenotazione.CodicePrenotazione AS CodicePrenotazione,
	RT_Prenotazione.TipoViaggioId,
    RT_PrenotazioneTitolo.DataIns AS DataIns,
	RT_Prenotazione.Lingua
FROM
	RT_PrenotazioneTitolo
	INNER JOIN RT_Prenotazione ON RT_PrenotazioneTitolo.PrenotazioneId = RT_Prenotazione.PrenotazioneId
	INNER JOIN RT_PrenotazioneDettaglio ON RT_PrenotazioneDettaglio.PrenotazioneNumero = RT_PrenotazioneTitolo.PrenotazioneNumeroId
	INNER JOIN RT_Coupon ON RT_Coupon.TitoloRimborsatoId = RT_PrenotazioneTitolo.PrenotazioneTitoloId
WHERE
	RT_PrenotazioneTitolo.InviaTitolo = 1
	AND RT_Prenotazione.ClienteEmail IS NOT NULL
	AND RT_Prenotazione.ClienteEmail <> ''
	AND RT_PrenotazioneTitolo.TipoTitolo = 'R'
	AND RT_Coupon.CouponId IS NOT NULL
GROUP BY
	RT_PrenotazioneTitolo.PrenotazioneNumeroId
ORDER BY
	PrenotazioneId ASC;";

$titoli = $db->fetch_array($sql);
print("<pre>");
print_r($titoli);


$prenotazioneId_precedente = null;
$pdf_precedente = null;
$email_precendente = null;

$send = false;


echo "inizio ciclo per rimborsi coupon\n";
$dataAttuale = date('Y-m-d H:i');

$emailtemplateCoupon = $basepath."/protected/modules/rt_previaggio/pdftemplate/coupon.html";
 
foreach ($titoli as $titolo) {

	// setto il percorso del template da usare come contenuto del email in base alla lingua della prenotazione
	$couponFileName = (!isset($titolo['Lingua']) || $titolo['Lingua'] == 'it') ? 'coupon.html' : 'coupon_'.$titolo['Lingua'].'.html';
	$emailtemplateCoupon = $basepath."/protected/modules/rt_previaggio/pdftemplate/$couponFileName";
	
	$dataIns = $titolo['DataIns'];
	$datetime = new DateTime($dataIns);
	echo $titolo['PrenotazioneTitoloId'];
	if ($datetime->format('Y-m-d H:i') !== $dataAttuale) {
		$prenotazioneId = $titolo['PrenotazioneId'];
		$titoloId = $titolo['PrenotazioneTitoloId'];
		$corsaId = $titolo['CorsaId'];
		$codiceItinerario = $titolo['CodicePrenotazione'];
		$dataPartenza = $titolo['DataInizioItinerario'];
		$tipo_titolo = "R";
		
		echo "ciclo $codiceItinerario $titoloId \n";
		
		if ($prenotazioneId != $prenotazioneId_precedente) {
			echo("if 1\n");
			if ($send == true) {
						 echo("invia email $prenotazioneId_precedente \n");
						 
				$sqlCodiceItinerario = "Select * from RT_Prenotazione WHERE PrenotazioneId = ".$prenotazioneId_precedente;
				$codiceEmail = $db->query_first($sqlCodiceItinerario);
				invioEmailCoupon($pdf_precedente, $prenotazioneId_precedente, $email_precendente,$codiceEmail['CodicePrenotazione'], $db, $emailtemplateCoupon);
				$send = false;
				/* LOG */
				//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] invio email prenotazione '.$codiceItinerario.' \n', FILE_APPEND | LOCK_EX);
				/* LOG */
				
				$updInviaTitolo=$db->update('RT_PrenotazioneTitolo', $dataTitolo,"PrenotazioneId=$prenotazioneId_precedente");
			}
			
			$pdf_precedente = new FPDI();
			$pdf_precedente->AddPage();
			
			$prenotazioneId_precedente = $prenotazioneId;
			$email_precendente = $titolo['ClienteEmail'];
			$user = getUser($prenotazioneId);
				  
			
			$send = true;
		} else {
			echo("else 1\n");
			$pdf_precedente->AddPage();
		}
		stampa_titoli_di_viaggio_coupon($prenotazioneId, $dataPartenza, $corsaId, 'Andata', $titoloId, $tipo_titolo, false, $pdf_precedente);
		$dataTitolo=array();
		$dataTitolo['InviaTitolo']=2;
		$dataTitolo['DataInvioTitolo']=date('Y-m-d H:i:s');

		$updInviaTitolo = $db->update('RT_PrenotazioneTitolo', $dataTitolo,"PrenotazioneTitoloId = $titoloId");
    }         
}
echo "fine ciclo \n";

//invio coupon di acquisto via email
// recupero i titoli di viaggio da inviare via email
$sql = "SELECT * FROM RT_Coupon 
		WHERE
		Stato = 1
		AND Cancella = 0
		AND VenditaStato = 2
		AND VenditaNotifica = 0";

$coupons = $db->fetch_array($sql);
print("<pre>");
print_r($coupons);


$pdf_precedente = null;
echo "inizio ciclo per coupon acquistati\n";
$dataAttuale = date('Y-m-d H:i');

$emailtemplateCoupon = $basepath."/protected/modules/rt_previaggio/pdftemplate/coupon_acquisto.html";
 
foreach ($coupons as $coupon) {

	// setto il percorso del template da usare come contenuto del email in base alla lingua del coupon
	$couponFileName = (!isset($coupon['Lingua']) || $coupon['Lingua'] == 'it') ? 'coupon_acquisto.html' : 'coupon_acquisto'.$titolo['Lingua'].'.html';
	$emailtemplateCoupon = $basepath."/protected/modules/rt_previaggio/pdftemplate/$couponFileName";
		
	
	$dataIns = $coupon['DataIns'];
	$datetime = new DateTime($dataIns);
	echo $coupon['CouponId'];
	if ($datetime->format('Y-m-d H:i') !== $dataAttuale) {
		$couponId = $coupon['CouponId'];
		

		$pdf_precedente = new FPDI();
		$pdf_precedente->AddPage();
		stampa_coupon($couponId,  false, $pdf_precedente);

				echo("invia email $couponId \n");
				$user = getUserByCoupon($couponId);
				invioEmailCouponById($pdf_precedente, $coupon['CouponId'],$coupon['VenditaEmail'], $db, $emailtemplateCoupon);
				
				// Verifica se è un buono regalo e invia email al destinatario
				if (isset($coupon['VenditaBuonoRegalo']) && $coupon['VenditaBuonoRegalo'] == '1' && 
					!empty($coupon['VenditaEmailDestinatario'])) {
					
					sleep(2); // Pausa tra invii
					
					// Determina il template del buono regalo in base alla lingua
					$buonoRegaloFileName = (!isset($coupon['Lingua']) || $coupon['Lingua'] == 'it') ? 
						'coupon_buono_regalo.html' : 
						'coupon_buono_regalo_'.$coupon['Lingua'].'.html';
					
					$emailtemplateBuonoRegalo = $basepath."/protected/modules/rt_previaggio/pdftemplate/$buonoRegaloFileName";
					
					// Invio email buono regalo al destinatario
					invioEmailCouponBuonoRegaloById($pdf_precedente, $coupon['CouponId'], $coupon['VenditaEmailDestinatario'], $db, $emailtemplateBuonoRegalo);
				}

				$dataCoupon = array();
				$dataCoupon['VenditaNotifica'] = 1;
				$dataCoupon['VenditaNotificaData'] = date('Y-m-d H:i:s');
				$updInviaTitolo=$db->update('RT_Coupon', $dataCoupon,"CouponId = $couponId");

    }         
}
echo "fine ciclo \n";

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] fine for each titoli\n', FILE_APPEND | LOCK_EX);
/* LOG */

// invia l'ultima email quando sono finiti i titoli
if (isset($prenotazioneId_precedente)) {
	invioEmailCoupon($pdf_precedente, $prenotazioneId_precedente, $email_precendente,$codiceItinerario, $db, $emailtemplateCoupon);
	$updInviaTitolo=$db->update('RT_PrenotazioneTitolo', $dataTitolo,"PrenotazioneId=$prenotazioneId_precedente");
	echo "invio email ultima $prenotazioneId_precedente \n";
	/* LOG */
	//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] invio email ultimo titolo'.$codiceItinerario.' \n', FILE_APPEND | LOCK_EX);
	/* LOG */
}

//invio scontrino elettronico via email
$sql = "SELECT m.*, p.Lingua FROM RT_PrenotazioneMovimento m
			LEFT JOIN RT_Prenotazione p on p.PrenotazioneId = m.PrenotazioneId
			WHERE m.ScontrinoTipo = 1 AND m.ScontrinoId IS NOT NULL AND m.ScontrinoId <> '' AND m.ScontrinoNotifica = 1";
$rows = $db->fetch_array($sql);
$emailtemplate = $basepath."/protected/modules/rt_previaggio/pdftemplate/scontrino.html";

foreach ($rows as $row) {
	// setto il percorso del template da usare come contenuto del email in base alla lingua della prenotazione
	$scontrinoFileName = (!isset($row['Lingua']) || $row['Lingua'] == 'it') ? 'scontrino.html' : 'scontrino_'.$row['Lingua'].'.html';
	$emailtemplate = $basepath."/protected/modules/rt_previaggio/pdftemplate/$scontrinoFileName";

	$result = inviaScontrinoEmail($row['PrenotazioneMovimentoId'], $emailtemplate);
	
	if($result) {
		$dataMovimento = array();
		$dataMovimento['ScontrinoNotifica'] = 2;
		$updInviaTitolo=$db->update('RT_PrenotazioneMovimento', $dataMovimento, "PrenotazioneMovimentoId = ".$row['PrenotazioneMovimentoId']);
	}
}


//invio email per notificare una nuova prenotazione da confermare
echo "invio email per notificare una nuova prenotazione da confermare\n";
$emailtemplate = $basepath."/protected/modules/rt_previaggio/pdftemplate/da_confermare_operatore.html";
$sql = "SELECT  GROUP_CONCAT(DISTINCT Email SEPARATOR '; ') Emails
					FROM Operatore
					WHERE GestoreId = 1 AND Stato = 1
							AND Cancella = 0";
$rows = $db->fetch_array($sql);
$emailOperatori = $rows[0]['Emails'];
var_dump($emailOperatori);
$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneStato = 2 AND NotificaDaConfermare = 0";
$rows = $db->fetch_array($sql);
var_dump($emailOperatori);
foreach ($rows as $row) {
	inviaNotificaConfermareEmail($row, $emailtemplate, $emailOperatori);
	$dataPrenotazione = array();
	$dataPrenotazione['NotificaDaConfermare'] = 1;
	$db->update('RT_Prenotazione', $dataPrenotazione, "PrenotazioneId = ".$row['PrenotazioneId']);
}



// ELENCO FUNZIONI
function invioEmail($pdf, $prenotazioneId, $email_to, $CodiceItinerario, $db) {
    
    sleep(3);
    
	global $voucherpath, $emailtemplate, $content_html;

	//recupero prenotazione
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$prenotazioneId;
	$prenotazione = $db->query_first($sql);
	
	$email_from = "info@braincomputing.com";
	$oggetto = "Bertoldi Boats - ".$dizionarioEmail[$prenotazione['Lingua']]['conferma']."  n. ".$CodiceItinerario;
	
	$file_name = "prenotazione_".$CodiceItinerario.".pdf";
	$pdf->Output($voucherpath.$file_name, 'F');
	
	$email_to = trim($email_to);
        if (!empty($email_to)) {
		mail_attachment($email_from, $email_to, $oggetto, $content_html, $voucherpath.$file_name, $bcc);
	}
	
}


function invioEmailCoupon($pdf, $prenotazioneId, $email_to, $CodiceItinerario, $db, $emailtemplate) {
    
    sleep(2);
    
	global $voucherpath, $dizionarioEmail;

	//recupero prenotazione
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$prenotazioneId;
	$prenotazione = $db->query_first($sql);

	// setto il percorso del template da usare come contenuto del email
	$fh = fopen($emailtemplate,"r");
	$content_html = fread($fh,filesize($emailtemplate));
	fclose($fh);
	
	$email_from = "info@bertoldiboats.com";
	$oggetto = "Bertoldi Boats - - ".$dizionarioEmail[$prenotazione['Lingua']]['coupon']." ".$CodiceItinerario;
	
	$file_name = "coupon_".$CodiceItinerario.".pdf";
	$pdf->Output($voucherpath.$file_name, 'F');
	
	$email_to = trim($email_to);
	$bcc = null;
        if (!empty($email_to)) {
		mail_attachment($email_from, $email_to, $oggetto, $content_html, $voucherpath.$file_name, $bcc);
	}
	
}

function invioEmailCouponById($pdf, $couponId, $email_to, $db, $emailtemplate) {

    sleep(2);

    global $voucherpath, $dizionarioEmail;

    // Recupero coupon
    $sql = "SELECT * FROM RT_Coupon WHERE CouponId = ".$couponId;
    $coupon = $db->query_first($sql);

    // setto il percorso del template da usare come contenuto dell'email
    $fh = fopen($emailtemplate,"r");
    $content_html = fread($fh,filesize($emailtemplate));
    fclose($fh);

    $email_from = "info@bertoldiboats.com";
    // Usa la lingua del coupon se presente, altrimenti 'it'
    $lingua = isset($coupon['Lingua']) ? $coupon['Lingua'] : 'it';
    $oggetto = "Bertoldi Boats - ".$dizionarioEmail[$lingua]['coupon_acquisto']." ".$coupon['Codice'];

    $file_name = "coupon_".$coupon['Codice'].".pdf";
    $pdf->Output($voucherpath.$file_name, 'F');

    $email_to = trim($email_to);
    $bcc = null;
    if (!empty($email_to)) {
        mail_attachment($email_from, $email_to, $oggetto, $content_html, $voucherpath.$file_name, $bcc);
    }
}


function invioEmailCouponBuonoRegaloById($pdf, $couponId, $email_to, $db, $emailtemplate) {

    sleep(2);

    global $voucherpath, $dizionarioEmail;

    // Recupero coupon
    $sql = "SELECT * FROM RT_Coupon WHERE CouponId = ".$couponId;
    $coupon = $db->query_first($sql);

    // setto il percorso del template da usare come contenuto dell'email
    $fh = fopen($emailtemplate,"r");
    $content_html = fread($fh,filesize($emailtemplate));
    fclose($fh);
    
    // Sostituisci i placeholder nel template
    $content_html = str_replace("[__IMPORTO__]", $coupon['Importo'], $content_html);
    $content_html = str_replace("[__CODICE__]", $coupon['Codice'], $content_html);
    $content_html = str_replace("[__MESSAGGIO__]", 
        isset($coupon['VenditaMessaggioDestinatario']) ? $coupon['VenditaMessaggioDestinatario'] : '-', 
        $content_html);

    $email_from = "info@bertoldiboats.com";
    // Usa la lingua del coupon se presente, altrimenti 'it'
    $lingua = isset($coupon['Lingua']) ? $coupon['Lingua'] : 'it';
    $oggetto = "Bertoldi Boats - ".$dizionarioEmail[$lingua]['buono_regalo'];

    $file_name = "coupon_".$coupon['Codice'].".pdf";
    $pdf->Output($voucherpath.$file_name, 'F');

    $email_to = trim($email_to);
    $bcc = null;
    if (!empty($email_to)) {
        mail_attachment($email_from, $email_to, $oggetto, $content_html, $voucherpath.$file_name, $bcc);
    }
}


/* prendo i dati dell'utente */
function getUser($prenotazioneId) {
	global $db;

	$sql = "SELECT OpeIns,SedeIns,GestoreIdRef,OdcIdRef FROM RT_Prenotazione WHERE PrenotazioneId = $prenotazioneId";

	$row = $db->query_first($sql);
	$operatoreId = $row['OpeIns'];

	$user = new Operatore();
	$user->conn = $db;

	$user->OperatoreId=$row['OpeIns'];
	$user->GestoreId=$row['GestoreIdRef'];
	$user->SedeId=$row['SedeIns'];
	$user->OdcId=$row['OdcIdRef'];

	return $user;
}

function getUserByCoupon($couponId) {
    global $db;
    
    $sql = "SELECT OpeIns,SedeIns,GestoreIdRef,OdcIdRef FROM RT_Coupon WHERE CouponId = $couponId";
    echo $sql;
    $row = $db->query_first($sql);
    $operatoreId = $row['OpeIns'];
    
    $user = new Operatore();
    $user->conn = $db;
    
    $user->OperatoreId=$row['OpeIns'];
    $user->GestoreId=$row['GestoreIdRef'];
    $user->SedeId=$row['SedeIns'];
    $user->OdcId=$row['OdcIdRef']; 
    
    return $user;
    //$user->inizializza($operatoreId);
    
}

/* invia l'email */
function mail_attachment($from, $to, $subject, $message, $attachment, $bcc = null) {
  
	global $db, $user;
	
	//i dati per l'invio sono presenti nella tabella ODC
	$sql = "SELECT * FROM Odc WHERE OdcId = $user->OdcId";
	$odc = $db->query_first($sql);
	
    $mail= new PHPMailer(); // create the mail
    $mail->Subject = $subject;
	$mail->AddAddress($to);
	if(isset($bcc)) {
	    $mail->AddBCC($bcc);
	}
	$mail->MsgHTML($message);
	$mail->IsSMTP();
	$mail->SMTPDebug  = null;
	$mail->SMTPSecure = 'ssl';// SMTP account password
	$mail->SMTPAuth = true;
	$mail->IsHTML(true);
	
	// inserisce l'allegato
	$mail->AddAttachment($attachment);
        
    // setto il from    
    $from = $odc['EmailSmtp'];
    $fromName = $odc['NomeEmailSmtp'];
	$mail->SetFrom($from, $fromName);  
	$mail->Host = $odc['ServerSmtp'];				// Server SMTP
	$mail->Port = $odc['PortaSmtp'];				// Server SMTP Port
	$mail->Username = $odc['UserSmtp'];           	// SMTP account username
	$mail->Password = $odc['PwdSmtp'];      

    // SMTP account password
	$mail->Send();  
}

//invia email per inviare lo scontrino in modo automatico se attivo nel movimento contabile
function inviaScontrinoEmail($movimentoId, $emailtemplate) {
	global $db, $user, $dizionarioEmail;

	$service = new ServiceFiscalGateway(Config::$fiscalGatewayUrl, Config::$fiscalGatewayAuthentication, Config::$fiscalGatewayAccountCode, Config::$fiscalGatewayStoreId);

	//recupero movimento 
	$sql = "SELECT * FROM RT_PrenotazioneMovimento WHERE PrenotazioneMovimentoId = ".$movimentoId;
	$movimento = $db->query_first($sql);

	//recupero prenotazione
	$sql = "SELECT * FROM RT_Prenotazione WHERE PrenotazioneId = ".$movimento['PrenotazioneId'];
	$prenotazione = $db->query_first($sql);

	$result = $service->getBillDownloadUrl($movimento['ScontrinoId']);

	if(!isset($result['response']['result']['url']) || empty($result['response']['result']['url'])) {
		return false; // se non c'è l'url non invio nulla
	}

	//i dati per l'invio sono presenti nella tabella ODC
	$sql = "SELECT * FROM Odc WHERE OdcId = 1";
	$odc = $db->query_first($sql);

	// setto il percorso del template da usare come contenuto del email
	$fh = fopen($emailtemplate,"r");
	$content_html = fread($fh,filesize($emailtemplate));
	fclose($fh);
	$content_html = str_replace("[__URL__]", $result['response']['result']['url'], $content_html);

	$mail= new PHPMailer(); // create the mail
	$mail->Subject = "Bertoldi Boats - ".$dizionarioEmail[$prenotazione['Lingua']]['scontrino']." ".$prenotazione['CodicePrenotazione'];
	$mail->AddAddress($prenotazione['ClienteEmail']);
	$mail->MsgHTML($content_html);
	$mail->IsSMTP();
	$mail->SMTPDebug  = null;
	$mail->SMTPSecure = 'ssl';// SMTP account password
	$mail->SMTPAuth = true;
	$mail->IsHTML(true);
		
	// setto il from    
	$from = $odc['EmailSmtp'];
	$fromName = $odc['NomeEmailSmtp'];
	$mail->SetFrom($from, $fromName);  
	$mail->Host = $odc['ServerSmtp'];				// Server SMTP
	$mail->Port = $odc['PortaSmtp'];				// Server SMTP Port
	$mail->Username = $odc['UserSmtp'];           	// SMTP account username
	$mail->Password = $odc['PwdSmtp'];      

	// SMTP account password
	$mail->Send();

	//aggiorno la data di invio della ricevuta via email
	$data = [
		'ScontrinoDataInvio' => date("Y-m-d H:i:s"),
	];
	$resultUpdate = $db->update("RT_PrenotazioneMovimento", $data, "PrenotazioneMovimentoId = $movimentoId");
	
	return true;
}


//invia email per notificare una nuova prenotazione da confermare
function inviaNotificaConfermareEmail($prenotazione, $emailtemplate, $emailOperatori) {
	global $db, $user, $dizionarioEmail;

	//i dati per l'invio sono presenti nella tabella ODC
	$sql = "SELECT * FROM Odc WHERE OdcId = 1";
	$odc = $db->query_first($sql);

	// setto il percorso del template da usare come contenuto del email
	$fh = fopen($emailtemplate,"r");
	$content_html = fread($fh,filesize($emailtemplate));
	fclose($fh);
	$content_html = str_replace("[__CODICE__]", $prenotazione['CodicePrenotazione'], $content_html);

	$mail= new PHPMailer(); // create the mail
	$mail->Subject = "Bertoldi Boats - ".$dizionarioEmail[$prenotazione['Lingua']]['da_confermare']." ".$prenotazione['CodicePrenotazione'];
	// Separi gli indirizzi usando il punto e virgola
	$emailList = explode(';', $emailOperatori);

	foreach ($emailList as $email) {
		$email = trim($email); // rimuove eventuali spazi prima/dopo
		if (!empty($email)) {
			$mail->AddAddress($email);
		}
	}
	$mail->MsgHTML($content_html);
	$mail->IsSMTP();
	$mail->SMTPDebug  = null;
	$mail->SMTPSecure = 'ssl';// SMTP account password
	$mail->SMTPAuth = true;
	$mail->IsHTML(true);
		
	// setto il from    
	$from = $odc['EmailSmtp'];
	$fromName = $odc['NomeEmailSmtp'];
	$mail->SetFrom($from, $fromName);  
	$mail->Host = $odc['ServerSmtp'];				// Server SMTP
	$mail->Port = $odc['PortaSmtp'];				// Server SMTP Port
	$mail->Username = $odc['UserSmtp'];           	// SMTP account username
	$mail->Password = $odc['PwdSmtp'];      

	// SMTP account password
	$mail->Send();  
	
	return true;
}

?>
