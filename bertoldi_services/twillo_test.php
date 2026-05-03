<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$basepath=$_SERVER['DOCUMENT_ROOT'];
include($basepath."/main_include.php");

require_once $basepath.'/protected/classes/twilio-php-main/twilio-php-main/src/Twilio/autoload.php';

$config=new Config();
$run=$config->load(); 

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

use Twilio\Rest\Client;

$account_sid = Config::$twilloSid;
$auth_token = Config::$twilloAuthToken;
$sid    = 'AC95b0cd4fd9645098d2921c1ccef0a2e9';
$token  = 'a2ddf6febccd9fe193aee5cf8e45ae69';
$twilio = new Client($sid, $token);

$message = $twilio->messages
                  ->create("whatsapp:+393460639351", // to
                           [
                               "from" => "whatsapp:+14155238886",
                               "body" => "Hello there!"
                           ]
                  );

print($message->sid);
?>