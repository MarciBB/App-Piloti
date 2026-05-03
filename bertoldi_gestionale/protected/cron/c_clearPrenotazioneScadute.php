<?php
include_once('c_path_include.php');
ini_set('display_errors', 1);

/* Configurazione log */
//$file = '/var/log/csreisen_c_clearPrenotazioneScadute.log';
$time_start = microtime(true);

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

$classespath_ = $basepath."/protected/classes/";
$modulespath_ = $basepath."/protected/modules/";

global $user;
global $db;

$config = new Config();
$run = $config->load(); 

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] configurazioni eseguite\n', FILE_APPEND | LOCK_EX);
/* LOG */

$x = clearPrenotazioniScadute();

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] clearPrenotazioniScadute eseguito\n', FILE_APPEND | LOCK_EX);
/* LOG */

$x = restorePrenotazioniScadute();

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] restorePrenotazioniScadute eseguito\n', FILE_APPEND | LOCK_EX);
/* LOG */

$x = annullaPrenotazioniScaduteWeb();

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] annullaPrenotazioniScaduteWeb eseguito\n', FILE_APPEND | LOCK_EX);
/* LOG */

/* LOG */
//file_put_contents($file, '[' . date('d/m/Y H:i:s') . '] END - Tempo esecuzione: ' . microtime(true) - $time_start . '\n', FILE_APPEND | LOCK_EX);
/* LOG */

// ELENCO FUNZIONI
function clearPrenotazioniScadute() {
	global $user,$db;
	$db=new Database();
	$db->connect();

	$currentDate = date('Y-m-d H:i:s');
	print($currentDate);

	$sql = "SELECT p.PrenotazioneId, p.ScadenzaPrenotazione FROM RT_Prenotazione p 
			WHERE 
			p.PrenotazioneStato != 14 AND 
			p.PrenotazioneStato != 3 AND 
			p.PrenotazioneStato != 4 AND
			p.PrenotazioneStato != 5 AND
			p.PrenotazioneStato != 6 AND
			p.PrenotazioneStato != 7 AND
			p.PrenotazioneStato != 8 AND
			p.PrenotazioneStato != 9 AND
			p.PrenotazioneStato != 10 AND
			p.PrenotazioneStato != 12 AND
			p.PrenotazioneStato != 13 AND
			p.PrenotazioneStato != 15 AND 
			p.Pagato=0 AND 
					p.ABordo=0 AND 
			p.ScadenzaPrenotazione IS NOT NULL 
			AND p.ScadenzaPrenotazione != '0000-00-00 00:00:00'
			AND p.ScadenzaPrenotazione < '$currentDate'";
	$prenotazioni = $db->fetch_array($sql);

	foreach ($prenotazioni as $prenotazione) {
	   
		
		$data['PrenotazioneStato'] = 14;
		$db->update("RT_Prenotazione", $data, "PrenotazioneId=".$prenotazione['PrenotazioneId']);
		$db->update("RT_PrenotazionePercorso", $data, "PrenotazioneId=".$prenotazione['PrenotazioneId']);
	}
	$db->close();
	return true;
}

function restorePrenotazioniScadute() {
	global $user,$db;
	$db=new Database();
	$db->connect();

	$currentDate = date('Y-m-d H:i:s');

	$sql = "SELECT p.PrenotazioneId, p.ScadenzaPrenotazione FROM RT_Prenotazione p 
			WHERE 
			p.PrenotazioneStato = 14 AND 
					((p.ScadenzaPrenotazione >= '$currentDate' or p.ABordo=1))";
	$prenotazioni = $db->fetch_array($sql);

	foreach ($prenotazioni as $prenotazione) {
	   
		
		$data['PrenotazioneStato'] = 1;
		$db->update("RT_Prenotazione", $data, "PrenotazioneId=".$prenotazione['PrenotazioneId']);
			$db->update("RT_PrenotazionePercorso", $data, "PrenotazioneId=".$prenotazione['PrenotazioneId']);
	}
	$db->close();
	return true;
}


function annullaPrenotazioniScaduteWeb() {
	global $user,$db;
	$db=new Database();
	$db->connect();
	$dup['PrenotazioneStato'] = 4;
	$now=Date('Y-m-d H:i:s');
	$dt=new DT($now,'Y-m-d H:i:s');
	$datacorrente=$dt->getDate('Y-m-d H:i:s');
	$minutiAnnullamentoScadute = Config::$minutiAnnullamentoScadute;
	$dt->addMinutes(-$minutiAnnullamentoScadute);
	$DataCalcolata=$dt->getDate();
	echo("data calcolata".$DataCalcolata);

	//annulla prenotazioni scadute dal web
	$up1=$db->update("RT_Prenotazione", $dup,"PrenotazioneStato=11 and DataIns<'$DataCalcolata'");
	$up2=$db->update("RT_PrenotazionePercorso", $dup,"PrenotazioneStato=11 and DataIns<'$DataCalcolata'");

	//annulla prenotazioni scadute dall'agenzia non verificata'
	$up3=$db->update("RT_Prenotazione", $dup,"PrenotazioneStato=13 and DataIns<'$DataCalcolata'");
	$up4=$db->update("RT_PrenotazionePercorso", $dup,"PrenotazioneStato=13 and DataIns<'$DataCalcolata'");

	$db->close();
	return true;
}
?>