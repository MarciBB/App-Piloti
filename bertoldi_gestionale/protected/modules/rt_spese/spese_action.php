<?php


$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.DT.php");
include_once($classespath_."class.Fornitore.php");

global $ModuloId;
global $user;

$ModuloId=55; // modulo base mediazione
if(is_object($user)) {
	$permessi=$user->get_permessi_modulo($ModuloId);
	if (sizeof($permessi)>0) { 
		if (!empty($_GET)) {
			switch($_GET['action']) {
				case "CancellaSpesa":
					$FunzioneId = 4;
					$permesso = $user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						eliminaSpesa();
					else
						Errors::$ErrorePermessiModuloFunzione;
					break;
			}
		}
		if (!empty($_POST)) {
			switch($_POST['action']) {
				case "AggiungiSpesa":
					$FunzioneId=2;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						aggiungiSpesa();
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni		
				break;
				case "ModificaSpesa":
					$FunzioneId=4;
					$permesso=$user->ControllModuloFunzionePermesso($ModuloId,$FunzioneId);
					if (sizeof($permesso))
						aggiungiSpesa($_POST['SpesaId']);
					else
						Errors::$ErrorePermessiModuloFunzione;
					// verifica i permessi per l'azione e il modulo specificato ed eseguo le operazioni
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

function eliminaSpesa()
{
	global $user, $db;
	$storico = new StoricoOperazioni();
	$storico->conn = $db;

	$spesaId = $_GET['SpesaId'];
	
	$data['Stato'] = 0;
	$data['Cancella'] = 1;
	$data = $storico->operazioni_update($data, $user);

	$res = $db->update('RT_Spese', $data, "SpesaId=".$spesaId);
	echo json_encode(array('result'=>true));
}

function aggiungiSpesa($SpesaId = null) {
	
	global $user;
	$db = new Database();
	$db->connect();
	
	$storico = new StoricoOperazioni();
	$storico->conn = $db;
	
	$dt = new DT();
	
	$spesa = $_POST['Spesa'];
	if($spesa['Data'] != '') {
		$spesa['Data'] = $dt->format($spesa['Data'], "d/m/Y", "Y-m-d");
	} else {
		$spesa['Data'] = 'NULL';
	}

	if (!isset($SpesaId)) {
		$spesa = $storico->operazioni_insert($spesa, $user);
		$result = $db->insert("RT_Spese", $spesa);
	} else {
		$spesa = $storico->operazioni_update($spesa, $user);
		$result = $db->update("RT_Spese", $spesa, "SpesaId=".$SpesaId." AND OdcIdRef=".$user->OdcId);
	}
	
	if($result) {
		echo json_encode(array('result'=>true));
	} else {
		echo json_encode(array('result'=>false));
	}
}

?>