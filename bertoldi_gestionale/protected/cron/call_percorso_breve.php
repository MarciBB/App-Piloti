<?php
 ini_set('display_errors', 1);
  ini_set('error_reporting', E_ERROR);
      ini_set('max_execution_time', 360000); //300 seconds = 5 
$url="http://onebus.braincomputing.net/protected/cron/percorso_breve.php";
GetData($url);
function GetData($url)
{
    
       
	 $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 15,      // timeout on connect
       // CURLOPT_TIMEOUT        => 15,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects

    );

    $ch      = curl_init($url);
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch,CURLINFO_EFFECTIVE_URL );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;

    //change errmsg here to errno
    if ($errmsg)
    {
        echo "CURL:".$errmsg."<BR>";
    }
  
    
    print($content);
}     
?>
