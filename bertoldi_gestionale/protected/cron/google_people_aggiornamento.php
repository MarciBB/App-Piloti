<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_= Config::$modulespath;
$classespath_= Config::$classespath;
$errors = new Errors();

include_once($classespath_."class.ServiceGooglePeople.php");


ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$db = new Database();
$db->connect();

$currentDate = date('Y-m-d H:i:s');

$sql = "SELECT pp.Nome, pp.Cognome, p.ClienteEmail, p.ClienteCellularePrefisso, p.ClienteCellulare, p.ClienteCellulareFamiliare
FROM RT_Prenotazione p
LEFT JOIN RT_PrenotazionePasseggeri pp on p.PrenotazioneId = pp.PrenotazioneId
WHERE (p.ClienteEmail NOT IN (SELECT Email FROM GooglePeople)
OR CONCAT(p.ClienteCellularePrefisso, p.ClienteCellulareFamiliare) NOT IN (SELECT Cellulare FROM GooglePeople))
AND p.PrenotazioneStato = 3 AND pp.Principale = 1";

$rows = $db->fetch_array($sql);

$servicePeople = new ServiceGooglePeople($db);

foreach ($rows as $prenotazioneRow){
	
	$servicePeople->insertPeople($prenotazioneRow['Nome'], 
			$prenotazioneRow['Cognome'], 
			$prenotazioneRow['ClienteEmail'], 
			$prenotazioneRow['ClienteCellularePrefisso'].$prenotazioneRow['ClienteCellulareFamiliare']
	);
	
}



?>