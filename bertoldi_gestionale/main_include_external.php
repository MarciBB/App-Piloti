<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
$errors_include=$basepath.'/protected/classes/class.Errors.php';
$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$operatore_include=$basepath.'/protected/classes/class.Operatore.php';
$email_include=$basepath.'/protected/classes/class.Email.php';
$session_include=$basepath.'/protected/include/session.inc.php';
$html_page_include=$basepath.'/protected/classes/class.Html_Page.php';
$storico_operazioni=$basepath.'/protected/classes/class.StoricoOperazioni.php';
$date_time=$basepath.'/protected/classes/class.DT.php';
$check_session_include=$basepath.'/protected/include/OperatoreCheckSession.php';
include_once($errors_include);
include_once($session_include);
include_once($config_include);
include_once($database_include);
include_once($operatore_include);
include_once($html_page_include);
include_once($storico_operazioni);
include_once($date_time);
include_once($check_session_include);
include_once($email_include);
global $user;
if(isset($_SESSION['OPERATORE'])) {
    $user=unserialize($_SESSION['OPERATORE']);
    if ($user->ServerLogin!=Config::$ServerName)
    {
        header("Location: /logout.php");
    }        
    
}
global $HtmlCommon;
$HtmlCommon=new Html_page();
global $errore;
$errore=new Errors();
global $db;
?>

