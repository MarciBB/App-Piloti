<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.DT.php");
include_once($classespath_.'PHPMailer/class.phpmailer.php');
include_once($classespath_.'PHPMailer/class.smtp.php');

use Stripe\Stripe;

global $ModuloId;
global $user;

$ModuloId=45; // modulo base mediazione

if(is_object($user)) {
	$permessi=$user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi)>0) {   
		if (!empty($_POST)) {
			switch($_POST['action']) {
				case "AggiungiCoupon":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						aggiungiCoupon();
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni		
				break;
				case "GetCodiceCoupon":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						getCodiceCoupon();
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;
				case "ModificaCoupon":
					$FunzioneId=4;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						aggiungiCoupon($_POST['CouponId']);
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
					break;
			} 

		} elseif (!empty($_REQUEST)) {

				switch ($_REQUEST['do']) {
					case "stripeLink":
						$FunzioneId = 2;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							stripeLink($_REQUEST['couponId']);
						else
							Errors::$ErrorePermessiModuloFunzione;
						break;
					case "stripeSendLink":
						$FunzioneId = 2;
						$permesso = $user->ControllModuloFunzionePermesso($ModuloId, $FunzioneId);
						if (sizeof($permesso))
							stripeSendLink($_REQUEST['couponId']);
						else
							Errors::$ErrorePermessiModuloFunzione;
						break;
				}
		} else {
			Errors::$ErrorePermessiModulo;
		}
	}
}
// se l'utente non è loggato
else {
	header("Location: /logout.php");
}

function stripeSendLink($couponId) {
	global $user, $dizionario, $db, $dizionarioEmail;
	
	//recupero coupon
	$sql = "SELECT * FROM RT_Coupon WHERE CouponId = $couponId";
	$coupon = $db->query_first($sql);

	if(isset($coupon['MembershipClubCode']) && $coupon['MembershipClubCode'] == '') {
		$sql = "SELECT * FROM RT_MembershipClub WHERE MembershipClubId = '".$coupon['MembershipClubCode']."'";
		$mebmer = $db->query_first($sql);
		$clienteNome = $mebmer['Nome']. ' ' . $mebmer['CognomeRagioneSociale'];
	} else {
		$clienteNome = isset($coupon['Lingua']) ? $dizionarioEmail[$coupon['Lingua']]['cliente'] : 'Cliente';
	}

	if(isset($coupon['VenditaEmail']) && $coupon['VenditaEmail'] != '') {
		//recupero link per pagamento
		$sessionId = stripeLink($couponId, false);
		$url = 'https://' . $_SERVER['SERVER_NAME'].'/pagamento_stripe_link.php?session_id=' . $sessionId;

		//i dati per l'invio sono presenti nella tabella ODC
		$sql = "SELECT * FROM Odc WHERE OdcId = 1";
		$odc = $db->query_first($sql);
	
		// setto il percorso del template da usare come contenuto del email in base alla lingua della prenotazione
		$pagamentoFileName = (!isset($coupon['Lingua']) || $coupon['Lingua'] == 'it') ? 'pagamento_online_coupon.html' : 'pagamento_online_coupon_'.$coupon['Lingua'].'.html';
		$emailtemplate = $_SERVER['DOCUMENT_ROOT']."/protected/modules/rt_previaggio/pdftemplate/$pagamentoFileName";
		//$emailtemplate = $_SERVER['DOCUMENT_ROOT']."/protected/modules/rt_previaggio/pdftemplate/pagamento_online_cliente.html";
		$fh = fopen($emailtemplate,"r");
		$content_html = fread($fh,filesize($emailtemplate));
		fclose($fh);
		$content_html = str_replace("[__NOMECLIENTE__]", $clienteNome, $content_html);
		$content_html = str_replace("[__URL__]", $url, $content_html);
		$content_html = str_replace("[__TOTALE__]", number_format($coupon['Importo'], 2, ',', '.') . ' &euro;', $content_html);

		$mail= new PHPMailer(); // create the mail
		$mail->Subject = "Bertoldi Boats - ".$dizionarioEmail[$coupon['Lingua']]['pagamento_coupon_link'];
		$mail->AddAddress($coupon['VenditaEmail'], $clienteNome);
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

		echo  "ok";
		exit();
	} else {
		echo  "no";
		exit();
	}

}

function stripeLink($couponId, $post = true)
{
	global $user, $dizionario;
	$db = new Database();
	$db->connect();

	ini_set('display_errors', 0);
	ini_set('error_reporting', E_ALL);

	$sql = "SELECT * FROM RT_Coupon WHERE CouponId = $couponId";
	$coupon = $db->query_first($sql);

	
	$daPagare = $coupon['Importo'];
	$cliente_email = $coupon['VenditaEmail'];
	if (!isset($cliente_email) || $cliente_email == '') {
		$cliente_email = 'noemail';
	}

	$ImportoTotale_final_banca = $daPagare;

	$url_sito = Config::$UrlDominio;
	$bank_pagina_grazie = Config::$UrlDominio . "grazie.php";
	$bank_pagina_grazie .= "?OrderId=" . $couponId . "&em=" . $cliente_email . "&type=coupon";

	Stripe::setApiKey(Config::$StripeLinkSecretKey);
	$session = \Stripe\Checkout\Session::create([
		//'payment_method_types' => ['card','paypal','amazon_pay'],
		'line_items' => [[
			'price_data' => [
				'currency' => 'eur',
				'product_data' => [
					'name' => 'Coupon Bertoldi Boats',
				],
				'unit_amount' => intval($ImportoTotale_final_banca * 100),
			],
			'quantity' => 1,
		]],
		'mode' => 'payment',
		'success_url' => $bank_pagina_grazie . '&session_id={CHECKOUT_SESSION_ID}',
		'cancel_url' => $url_sito,
	]);

	if($post) {
		echo  $session->id;
		exit();
	} else {
		return $session->id;
	}

}


function aggiungiCoupon($CouponId = null) {
	
	global $user;
	$db = new Database();
	$db->connect();
	
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	
	$dt = new DT();
	
	$lineaArray = $_POST['CouponLineaId'];
	$partenzaArray = $_POST['CouponPartenzaId'];
	$destinazioneArray = $_POST['CouponDestinazioneId'];

	$coupon = $_POST['Coupon'];
	$tipoCreazione = $_POST['tipoCreazione'];
	$tipoCoupon = $_POST['tipoCoupon'];
	$numeroCoupon = $_POST['numeroCoupon'];
	if($coupon['Percentuale'] == ''){
		$coupon['Percentuale'] = 0;
	}
	if($coupon['Importo'] == ''){
		$coupon['Importo'] = 0;
	}
	$coupon['Importo'] = str_replace(',', '.', $coupon['Importo']);
	$coupon['Percentuale'] = str_replace(',', '.', $coupon['Percentuale']);
	
	if(trim($coupon['ValidoDa']) == "") {
		$coupon['ValidoDa'] = 'null';
	} else {
		$date = str_replace('/', '-', $coupon['ValidoDa']);
		$coupon['ValidoDa'] = date("Y-m-d", strtotime($date) );
	}
	if(trim($coupon['ValidoA']) == "") {
		$coupon['ValidoA'] = 'null';
	}  else {
		$date = str_replace('/', '-', $coupon['ValidoA']);
		$coupon['ValidoA'] = date("Y-m-d", strtotime($date) );
	}

	if(!is_array($partenzaArray) && $partenzaArray == 0) {
		$coupon['PartenzaId'] = 'null';
	} else if (!is_array($partenzaArray) && $partenzaArray != 0){
		$coupon['PartenzaId'] = $partenzaArray;
	} else {
		$arrayString = implode(',', $partenzaArray);
		$coupon['PartenzaId'] = $arrayString;
	}

	if(!is_array($destinazioneArray) && $destinazioneArray == 0) {
		$coupon['DestinazioneId'] = 'null';
	} else if (!is_array($destinazioneArray) && $destinazioneArray != 0){
		$coupon['DestinazioneId'] = $destinazioneArray;
	} else {
		$arrayString = implode(',', $destinazioneArray);
		$coupon['DestinazioneId'] = $arrayString;
	}

	if(!is_array($lineaArray) && $lineaArray == 0) {
		$coupon['LineaId'] = 'null';
	} else if (!is_array($lineaArray) && $lineaArray != 0){
		$coupon['LineaId'] = $lineaArray;
	} else {
		$arrayString = implode(',', $lineaArray);
		$coupon['LineaId'] = $arrayString;
	}

	if($coupon['GestoreId'] == 0) {
		$coupon['GestoreId'] = 'null';
	}

	if(isset($coupon['VenditaEmail']) && $coupon['VenditaEmail'] != '') {
		$sql = "select * from RT_MembershipClub where Email = '".$coupon['VenditaEmail']."' AND Stato = 1 AND Cancella = 0";
		$member = $db->query_first($sql);
		if(isset($member['MembershipClubCode'])) {
			$coupon['MembershipClubCode'] = $member['MembershipClubCode'];

			$sql = "select idnazione from RT_MembershipClub m
					left join Comune c on c.ComuneId = m.ComuneResidenzaId
					left join Provincia p on p.ProvinciaId = c.provincia
					left join Regione r on r.RegioneId = p.RegioneId
					where m.MembershipClubCode = '".$member['MembershipClubCode']."'";
			$nazione = $db->query_first($sql);
			if(isset($nazione['idnazione']) && $nazione['idnazione'] == 1) {
				$coupon['Lingua'] = 'it';
			} else {
				$coupon['Lingua'] = 'en';
			}
		}
	}

	$dalle_ore = isset($_POST['FasceOrarie']['dalle']['ora']) ? $_POST['FasceOrarie']['dalle']['ora'] : [];
	$dalle_min = isset($_POST['FasceOrarie']['dalle']['min']) ? $_POST['FasceOrarie']['dalle']['min'] : [];
	$alle_ore = isset($_POST['FasceOrarie']['alle']['ora']) ? $_POST['FasceOrarie']['alle']['ora'] : [];
	$alle_min = isset($_POST['FasceOrarie']['alle']['min']) ? $_POST['FasceOrarie']['alle']['min'] : [];
	$fasceCount = count($dalle_ore); // presuppone che tutti gli array abbiano la stessa lunghezza

	if (!isset($CouponId)) {
		if($tipoCreazione == 'one'){
			$coupon['Valore'] = $coupon['Importo'];
			$coupon = $storico->operazioni_insert($coupon, $user);
			$result = $db->insert("RT_Coupon", $coupon);
			$couponId = $result; // recupera l'id appena inserito

            // Salva le fasce orarie
            for ($i = 0; $i < $fasceCount; $i++) {
				$oraInizio = str_pad($dalle_ore[$i], 2, '0', STR_PAD_LEFT) . ':' . str_pad($dalle_min[$i], 2, '0', STR_PAD_LEFT);
				$oraFine   = str_pad($alle_ore[$i], 2, '0', STR_PAD_LEFT) . ':' . str_pad($alle_min[$i], 2, '0', STR_PAD_LEFT);

				if($oraInizio <  $oraFine) {
					$db->insert('RT_CouponFasciaOraria', [
						'CouponId' => $couponId,
						'OraInizio' => $oraInizio,
						'OraFine' => $oraFine
					]);
				}
			}
		} else {
			for($i = 0; $i<$numeroCoupon; $i++){
				$tempCoupon = null;
				$tempCoupon = $coupon;
				$tempCoupon['Codice'] = generaCodice();
				$tempCoupon['Valore'] = $coupon['Importo'];
				$tempCoupon = $storico->operazioni_insert($tempCoupon, $user);
				$result = $db->insert("RT_Coupon", $tempCoupon);
				$couponId = $result; // recupera l'id appena inserito

				// Salva le fasce orarie
				for ($i = 0; $i < $fasceCount; $i++) {
					$oraInizio = str_pad($dalle_ore[$i], 2, '0', STR_PAD_LEFT) . ':' . str_pad($dalle_min[$i], 2, '0', STR_PAD_LEFT);
					$oraFine   = str_pad($alle_ore[$i], 2, '0', STR_PAD_LEFT) . ':' . str_pad($alle_min[$i], 2, '0', STR_PAD_LEFT);

					if($oraInizio <  $oraFine) {
						$db->insert('RT_CouponFasciaOraria', [
							'CouponId' => $couponId,
							'OraInizio' => $oraInizio,
							'OraFine' => $oraFine
						]);
					}
				}
			}
		}
		
	} else {
		$sql = "select * from RT_Coupon where CouponId = $CouponId";
		$old = $db->query_first($sql);
		$coupon = $storico->operazioni_update($coupon, $user);
		$coupon['Valore'] = $coupon['Importo'] - ($old['Importo'] - $old['Valore']);
		if($coupon['Valore'] == 0 && $coupon['MaxUtilizzi'] > $old['MaxUtilizzi']) {
			$coupon['Valore'] = $coupon['Importo'];
		}
		$result = $db->update("RT_Coupon", $coupon, "CouponId=".$CouponId." AND OdcIdRef=".$user->OdcId);
	
		// Cancella fasce orarie vecchie
        $db->query("DELETE FROM RT_CouponFasciaOraria WHERE CouponId = $CouponId");
        // Inserisci le nuove fasce orarie
		for ($i = 0; $i < $fasceCount; $i++) {
			$oraInizio = str_pad($dalle_ore[$i], 2, '0', STR_PAD_LEFT) . ':' . str_pad($dalle_min[$i], 2, '0', STR_PAD_LEFT);
			$oraFine   = str_pad($alle_ore[$i], 2, '0', STR_PAD_LEFT) . ':' . str_pad($alle_min[$i], 2, '0', STR_PAD_LEFT);

			if($oraInizio <  $oraFine) {
				$db->insert('RT_CouponFasciaOraria', [
					'CouponId' => $CouponId,
					'OraInizio' => $oraInizio,
					'OraFine' => $oraFine
				]);
			}
		}
	}
	
	if($result) {
		echo json_encode(array('result'=>true));
	} else {
		echo json_encode(array('result'=>false));
	}
}

function generaCodice(){
	$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
	$string = '';
	$random_string_length = 10;
	for ($i = 0; $i < $random_string_length; $i++) {
		$string .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $string;
}

function getCodiceCoupon(){
	$string = generaCodice();
	echo json_encode(array('code'=>$string));
}

?>