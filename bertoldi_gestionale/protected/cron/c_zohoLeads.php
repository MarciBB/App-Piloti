<?php
/* Configurazione log */
include_once('c_path_include.php');

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

global $user, $db;
$config = new Config();
$run=$config->loadCron($type);

$classespath_ = $basepath."/protected/classes/";
$modulespath_ = $basepath."/protected/modules/";
include_once($classespath_."class.ServiceZoho.php");



$db = new Database();
$db->connect();


$service = new ServiceZoho();
echo "Connessione al db OK";

$sql = "SELECT 
		*
	FROM
		RT_Prenotazione
	WHERE
		(ZohoLeadId IS NULL
			OR (ZohoLeadId IS NOT NULL
			AND DataAgg IS NOT NULL
			AND DataAgg > ZohoDataUpdate))
			AND DataIns >= '2025-01-21 17:00:00'";

$prenotazioni = $db->fetch_array($sql);
foreach($prenotazioni as $p) {
	var_dump($p['PrenotazioneId']);
	$service->insertLeadPrenotazione($db, $p['PrenotazioneId']);
}
echo "Fine Processo Zoho";

?>