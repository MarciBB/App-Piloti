<?php
$id="97661482";
$password="-Rocc0terminal";
$action="4";
$amt="1.00";
$currencycode="978";
$langid="ITA";
$responseurl="http://www.nomedominio.it/nomecontesto/notify.php";
$errorurl="http://www.nomedominio.it/nomecontesto/error.php";
$trackid="TRCK0001";
$udf1="Descrizione";
$data="id=$id&password=$password&action=$action&amt=$amt&currencycode=$currencycode&langid=$langid&responseurl=$responseurl&errorurl=$errorurl&trackid=$trackid&udf1=$udf1";
$curl_handle=curl_init();
curl_setopt($curl_handle,CURLOPT_URL,'https://test.monetaonline.it/monetaweb/hosted/init/http');
curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
curl_setopt($curl_handle, CURLOPT_POST, 1);
curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
$buffer = curl_exec($curl_handle);

//print_r($buffer);

curl_close($curl_handle);
$token=explode(":",$buffer,2);
$paymentid=$token[0];
$paymenturl=$token[1];
echo"<a href=\"$paymenturl?PaymentID=$paymentid\">Buy now</a>";
?>