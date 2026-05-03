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

$ModuloId=36; // modulo base mediazione

if(is_object($user)) {
	$permessi=$user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi)>0) {   
		if (!empty($_POST)) {
			switch($_POST['action']) {
				case "AggiungiAutobus":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						aggiungiAutobus();
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni		
				break;
				case "ModificaAutobus":
					$FunzioneId=4;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						aggiungiAutobus($_POST['BusId']);
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
// se l'utente non � loggato
else {
	header("Location: /logout.php");
}

function aggiungiAutobus($BusId = null) {
	
	global $user;
	$db = new Database();
	$db->connect();
	
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	
	$autobus = $_POST['Autobus'];
	
	
	if (!isset($BusId)) {
		$autobus = $storico->operazioni_insert($autobus, $user);
		$result = $db->insert("RT_Flotta", $autobus);
	} else {
		$autobus = $storico->operazioni_update($autobus, $user);
		$result = $db->update("RT_Flotta", $autobus, "FlottaId=".$BusId." AND OdcIdRef=".$user->OdcId);
	}

	if($result) {
		echo json_encode(array('result'=>true));
	} else {
		echo json_encode(array('result'=>false));
	}
}
?>