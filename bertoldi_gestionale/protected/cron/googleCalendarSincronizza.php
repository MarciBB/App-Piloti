<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
//$basepath=$_SERVER['DOCUMENT_ROOT'];
//include_once($basepath."/main_include.php");
 ini_set('display_errors', 1);
 ini_set('error_reporting', E_ERROR);
 ini_set('max_execution_time', 36000); //300 seconds = 5 

 $classespath_=$basepath."/protected/classes/";
 $modulespath_=$basepath."/protected/modules/";
 
 include_once($classespath_."class.ServiceGoogleCalendar.php");

global $user, $db;

$db = new Database();
$db->connect();
echo "Connessione al db OK";

$service = new ServiceGoogleCalendar($db);

echo  "<br>Creazione flotta calendari";
$result = $service->createCalendarsForFlotta();
var_dump($result);

echo  "<br>Creazione eventi";
$return = $service->fetchCorsaEvents();
var_dump($result);
?>