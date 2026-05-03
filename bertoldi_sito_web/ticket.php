<?php 
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");

$config = new Config();
$run = $config->load();
header("location: http://".Config::$ServerBOName."/protected/modules/rt_previaggio/ticket.php?code=".$_GET['code']);
?>