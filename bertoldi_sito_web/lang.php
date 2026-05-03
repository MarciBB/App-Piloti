<?php

$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load();
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();
include_once($classespath_."class.UtenteWeb.php");
include_once($classespath_."class.NotificaAutomaticaMessaggiInvio.php");
include_once($classespath_."class.StoricoOperazioni.php");

global $db;
$db = new Database();
$db->connect();


if (!empty($_POST) &&  !empty($_POST['lang'])) {
	$_SESSION['lang'] = $_POST['lang'];
	echo json_encode(array('result'=>$_SESSION['lang']));
} else {
	echo json_encode(array('result'=>'de'));
}
