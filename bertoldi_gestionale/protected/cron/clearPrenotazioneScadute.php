<?php
include_once("main_include.php");
$classespath_=$basepath."/protected/classes";
$modulespath_=$basepath."/protected/modules";
$config=new Config();
$run=$config->load(); 


$x=clearPrenotazioniScadute();
$x=restorePrenotazioniScadute();
$x=  annullaPrenotazioniScaduteWeb();

function clearPrenotazioniScadute()
{
global $user,$db;
$db=new Database();
$db->connect();

$currentDate = date('Y-m-d H:i:s');

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

function restorePrenotazioniScadute()
{
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


function annullaPrenotazioniScaduteWeb()
{
global $user,$db;
$db=new Database();
$db->connect();
$dup['PrenotazioneStato']=4;
$now=Date('Y-m-d H:i:s');
$dt=new DT($now,'Y-m-d H:i:s');
$datacorrente=$dt->getDate('Y-m-d H:i:s');
$dt->addMinutes(-30);
$DataCalcolata=$dt->getDate('Y-m-d H:i:s');
echo($DataCalcolata);


$up1=$db->update("RT_Prenotazione", $dup,"PrenotazioneStato=11 and DataIns<'$DataCalcolata'");
$up2=$db->update("RT_PrenotazionePercorso", $dup,"PrenotazioneStato=11 and DataIns<'$DataCalcolata'");
$db->close();
return true;
}


?>