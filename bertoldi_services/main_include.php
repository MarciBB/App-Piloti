<?php

$basepath=$_SERVER['DOCUMENT_ROOT'];
//$session_include=$basepath.'/protected/include/session.inc.php';
$errors_include=$basepath.'/protected/classes/class.Errors.php';
$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';

//include_once($session_include);
include_once($errors_include);
include_once($config_include);
include_once($database_include);

global $errore;
$errore=new Errors();
global $db;


$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();


//     $db=new Database();
//     $db->connect();
//     $user->conn=$db;
?>