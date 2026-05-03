<?php
$basepath="/srv/www/htdocs/rocco.braincomputing.com";
$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$date_include=$basepath.'/protected/classes/class.DT.php';
include_once($config_include);
include_once($database_include);
include_once($date_include);
$config=new Config();
$run=$config->load();
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
?>
