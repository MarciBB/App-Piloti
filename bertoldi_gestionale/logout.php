<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();

global $user;

if ((isset($_REQUEST['do'])) and ($_REQUEST['do']=='logout'))
    {
 $db= new Database();
    $db->connect();
    $user->conn=$db;
    if(isset($user) && is_object($user)){
		$esci = $user->RegistraUscita();
    }
}
    

/*unset($_SESSION['OPERATORE']);*/
session_destroy();
header("location: index.php");
?>