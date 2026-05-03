<?php
// ini_set('session.gc_maxlifetime', 30);
//$basepath=$_SERVER['DOCUMENT_ROOT'];
$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$session_include=$basepath.'/protected/include/session.inc.php';
$date_time=$basepath.'/protected/classes/class.DT.php';
include_once($config_include);
include_once($database_include);
include_once($date_time);


global $db,$OperatoreId,$OdcId,$GestoreId,$SedeId;


$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;


$OdcId=1;
$GestoreId=1;
$OperatoreId=42;
$SedeId=36;




?>

