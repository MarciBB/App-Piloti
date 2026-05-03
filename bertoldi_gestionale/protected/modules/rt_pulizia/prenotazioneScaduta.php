<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_= Config::$modulespath;
$classespath_= Config::$classespath;
$errors = new Errors();
include_once($classespath_."class.Ruolo.php");
include_once($classespath_."class.Sede.php");
include_once($classespath_."class.Gestore.php");
include_once($classespath_."class.Nazione.php");
include_once($classespath_."class.Regione.php");
include_once($classespath_."class.Comune.php");
include_once($classespath_."class.Percorso.php");
include_once($classespath_."class.Linea.php");
include_once($classespath_."class.Corsa.php");
include_once($classespath_."class.Tratta.php");
include_once($classespath_."class.TrattaTipo.php");
include_once($classespath_."class.Mezzo.php");
include_once($classespath_."class.TrattaDirezione.php");
include_once($classespath_."class.Prenotazione.php");
include_once($classespath_."class.Fermata.php");
include_once($classespath_."class.PrenotazioneMovimento.php");
include_once($classespath_."class.PagamentoTipo.php");
include_once($classespath_."class.PrefissoTelefono.php");

$db = new Database();
$db->connect();

$currentDate = date('Y-m-d H:i:s');

$sql = "SELECT p.PrenotazioneId, p.ScadenzaPrenotazione FROM RT_Prenotazione p 
		WHERE 
		p.PrenotazioneStato != 14 AND 
		p.Pagato=0 AND 
		p.ScadenzaPrenotazione IS NOT NULL 
		AND p.ScadenzaPrenotazione != '0000-00-00 00:00:00'
		AND p.ScadenzaPrenotazione < '$currentDate'";
$prenotazioni = $db->fetch_array($sql);

foreach ($prenotazioni as $prenotazione) {
	$data['PrenotazioneStato'] = 14;
	$db->update("RT_Prenotazione", $data, "PrenotazioneId=".$prenotazione['PrenotazioneId']);
}

?>