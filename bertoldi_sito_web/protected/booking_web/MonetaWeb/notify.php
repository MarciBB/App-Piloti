<?php

$paymentID = $_REQUEST['paymentid'];
$result = $_REQUEST['result'];
$auth = $_REQUEST['auth'];
$ref = $_REQUEST['ref'];
$tranid = $_REQUEST['tranid'];
$trackid = $_REQUEST['trackid'];
$details = $_REQUEST['udf1'];
$responsecode = $_REQUEST['responsecode'];

$reply = "REDIRECT=" . "http://www.nomedominio.it/nomecontesto/result.php?paymentid=" . $paymentID;

echo $reply;

?>
