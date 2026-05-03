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
include_once($classespath_."class.ServiceGoogleCalendar.php");



$db = new Database();
$db->connect();


$service = new ServiceGoogleCalendar($db);
echo "Connessione al db OK";

echo  "<br>Creazione flotta calendari";
$result = $service->createCalendarsForFlotta();
var_dump($result);

echo  "<br>Creazione eventi";
$result = $service->fetchCorsaEvents();
var_dump($result);

?>