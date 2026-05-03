<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");

$config = new Config();


// Timezeone needs to be set
date_default_timezone_set ( 'Europe/Berlin' );
$dateTime = date ( "Y:m:d-H:i:s" );
function getDateTime() {
	global $dateTime;
	return $dateTime;
}
/*
 * Function that calculates the hash of the following parameters:
 * - Store Id
 * - Date/Time(see $dateTime above)
 * - chargetotal
 * - currency (numeric ISO value)
 * - shared secret
 */
function createHash($chargetotal, $currency) {
	// Please change the store Id to your individual Store ID
	$storeId = Config::$telecashStoreId;
	// NOTE: Please DO NOT hardcode the secret in that script. For example read it from a database.
	$sharedSecret = Config::$telecashSharedSecret;
	$stringToHash = $storeId . getDateTime () . $chargetotal . $currency . $sharedSecret;
	$ascii = bin2hex ( $stringToHash );
	return hash ( "sha256", $ascii );
}