<?php
//selezione lingua da browser
// $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
// switch ($lang){
// 	case "it":
// 		//echo "PAGE IT";
// 		$dizionario_sel="it";
// 		break;
// 	case "de":
// 		//echo "PAGE DE";
// 		$dizionario_sel="de";
// 		break;
// 	default:
// 		//echo "PAGE EN - Setting Default";
// 		$dizionario_sel="it";
// 		break;
// }
// $_SESSION['lang'] = $dizionario_sel;
//ini_set('memory_limit', '-1');
$basepath=$_SERVER['DOCUMENT_ROOT'];
$session_include=$basepath.'/protected/include/session.inc.php';
$errors_include=$basepath.'/protected/classes/class.Errors.php';
$config_include=$basepath.'/custom/reserved/class.Config.php';
$database_include=$basepath.'/protected/classes/class.Database.php';
$operatore_include=$basepath.'/protected/classes/class.Operatore.php';
$email_include=$basepath.'/protected/classes/class.Email.php';
$html_page_include=$basepath.'/protected/classes/class.Html_Page.php';
$storico_operazioni=$basepath.'/protected/classes/class.StoricoOperazioni.php';
$date_time=$basepath.'/protected/classes/class.DT.php';
$check_session_include=$basepath.'/protected/include/OperatoreCheckSession.php';
$addon_include=$basepath.'/protected/classes/class.AddOn.php';
$stripeSession=$basepath.'/protected/classes/stripe-php/lib/Checkout/Session.php';

include_once($session_include);
include_once($errors_include);
include_once($config_include);
include_once($database_include);
include_once($operatore_include);
include_once($html_page_include);
include_once($storico_operazioni);
include_once($date_time);
include_once($check_session_include);
include_once($email_include);
include_once($addon_include);

//include stripe
require $basepath.'/protected/classes/stripe-php/init.php';
use Stripe\Stripe;
include_once($stripeSession);


global $user;
global $HtmlCommon;
$HtmlCommon=new Html_page();
global $errore;
$errore=new Errors();
global $db;



$config=new Config();
$run=$config->load(); 
$modulespath_=Config::$modulespath;
$classespath_=Config::$classespath;
$errors=new Errors();

if(!isset($_SESSION['LINGUA'])){
	$_SESSION['LINGUA'] = 'it';
}
$dizionario_selezionato = $basepath.'/lang/'.$_SESSION['LINGUA']."/dizionario.php";
include($dizionario_selezionato);
$dizionario_email = $basepath.'/lang/email.php';
include($dizionario_email);


if(isset($_SESSION['OPERATORE'])) {
    $user = unserialize($_SESSION['OPERATORE']);
    
    if(isset($user->Cognome)) {
    
        $db = new Database();
        $db->connect();
        $user->conn = $db;
    
        $to_time = strtotime($user->DataScadenzaSessione);
        $from_time = strtotime(date('Y-m-d H:i:s'));
        $secondi_residui = round(($to_time - $from_time));
    } else {
        unset($_SESSION['OPERATORE']); 
        header("Location: principale.php?start=1");
        exit;
    }
}

function array_decode_list($array_to_decode)
{

    $cod=Config::$codificaUTF;
    
    
     if ($cod==true)
     {
           $new_row=array();
                foreach ($array_to_decode as $r)
                {
                    $new_row[]= utf8_encode($r);
                }

      return $new_row; 
     }
     else
         return $array_to_decode;
 
   

}
function caratteri_accentati($r)
{
    
    //$r=  str_replace("ì","&igrave;", $r);
    
    
    return $r;
}
?>