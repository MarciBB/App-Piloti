<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.DT.php");

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
		} // end verifica permessi
		else {
			Errors::$ErrorePermessiModulo;
		}
	}
}
// se l'utente non è loggato
else {
	header("Location: /logout.php");
}


function aggiungiCoupon($CouponId = null) {
	
	global $user;
	$db = new Database();
	$db->connect();
	
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	
	$dt = new DT();
	
	$coupon = $_POST['Coupon'];
	$tipoCreazione = $_POST['tipoCreazione'];
	$numeroCoupon = $_POST['numeroCoupon'];
	
	if (!isset($CouponId)) {
		if($tipoCreazione == 'one'){
			$coupon['Valore'] = $coupon['Importo'];
			$coupon = $storico->operazioni_insert($coupon, $user);
			$result = $db->insert("RT_Coupon", $coupon);
		} else {
			for($i = 0; $i<$numeroCoupon; $i++){
				$tempCoupon = null;
				$tempCoupon = $coupon;
				$tempCoupon['Codice'] = generaCodice();
				$tempCoupon['Valore'] = $coupon['Importo'];
				$tempCoupon = $storico->operazioni_insert($tempCoupon, $user);
				$result = $db->insert("RT_Coupon", $tempCoupon);
			}
		}
		
	} else {
		$coupon = $storico->operazioni_update($coupon, $user);
		$result = $db->update("RT_Coupon", $coupon, "CouponId=".$CouponId." AND OdcIdRef=".$user->OdcId);
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