<?php
$basepath=$_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$config=new Config();
$run=$config->load();
$classespath_=Config::$classespath;
$db=new Database();
$conn=$db->connect();    

$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
  $keyval = explode ('=', $keyval);
  if (count($keyval) == 2)
     $myPost[$keyval[0]] = urldecode($keyval[1]);
}
// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
   $get_magic_quotes_exists = true;
} 
foreach ($myPost as $key => $value) {        
   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
        $value = urlencode(stripslashes($value)); 
   } else {
        $value = urlencode($value);
   }
   $req .= "&$key=$value";
}
 
 
// STEP 2: Post IPN data back to paypal to validate
 $PAYPAL_LINK=Config::$PayPalUrl;
$ch = curl_init($PAYPAL_LINK);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
 
// In wamp like environments that do not come bundled with root authority certificates,
// please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path 
// of the certificate as shown below.
// curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
if( !($res = curl_exec($ch)) ) {
    // error_log("Got " . curl_error($ch) . " when processing IPN data");
    curl_close($ch);
    exit;
}
curl_close($ch);
 
 
// STEP 3: Inspect IPN validation result and act accordingly
 
if (strcmp ($res, "VERIFIED") == 0) {



  $custom = $_POST['custom'];

// Controllo se è un coupon o una prenotazione normale
if (strpos($custom, 'COUPON_') === 0) {
    // Gestione pagamento coupon
    $CouponId = str_replace('COUPON_', '', $custom);
    $CodiceTransazione = $_POST['txn_id'];
    $mc_gross = $_POST['mc_gross'];
    
    // Recupero dati coupon
    $sql = "SELECT * FROM RT_Coupon WHERE CouponId = $CouponId AND DaVendere = 1 AND (VenditaStato = 0 OR VenditaStato = 1)";
    $coupon = $db->query_first($sql);
    
    if (empty($coupon['CouponId'])) {
        die();
    }
    
    $importo_coupon = $coupon['Importo'];
    
    // Verifico se l'importo nel db corrisponde all'importo della transazione
    if ($importo_coupon != $mc_gross) {
        die();
    }

    // Verifico che non esista il codice transazione
    $s = "SELECT CouponTransazioneId FROM RT_CouponTransazione WHERE CodiceTransazione='$CodiceTransazione'";
    $row = $db->query_first($s);

    if (empty($row['CouponTransazioneId'])) {
        // Verifico che lo stato sia completed   
        $payment_status = $_POST['payment_status'];
        if ($payment_status == 'Completed') {
            // Inserisco transazione coupon
            $data = null;
            $data['CouponId'] = $CouponId;
            $data['TipoPagamentoId'] = 1;
            $data['CodiceTransazione'] = $CodiceTransazione;
            $data['payment_type'] = $_POST['payment_type'];
            $data['payment_status'] = $_POST['payment_status'];
            $data['address_status'] = $_POST['address_status'];
            $data['payer_status'] = $_POST['payer_status'];
            $data['first_name'] = $_POST['first_name'];
            $data['last_name'] = $_POST['last_name'];
            $data['payer_email'] = $_POST['payer_email'];
            $data['payer_id'] = $_POST['payer_id'];
            $data['mc_gross'] = $_POST['mc_gross'];
            $data['ImportoCoupon'] = $importo_coupon;
            $data['DataIns'] = date('Y-m-d H:i:s');
            $data['OpeIns'] = 5;
            $data['SedeIns'] = 1;
            $data['IpIns'] = $_SERVER['REMOTE_ADDR'];
            $data['OdcIdRef'] = 1;
            $data['GestoreIdRef'] = 1;
            $data['Stato'] = 1;
            $data['Cancella'] = 0;
            $transactionId = $db->insert("RT_CouponTransazione", $data);
            
            // Aggiorno stato coupon a pagato
            $updateCoupon = array();
            $updateCoupon['VenditaStato'] = 2; // Pagato
            $updateCoupon['DataAgg'] = date('Y-m-d H:i:s');
            $updateCoupon['OpeAgg'] = 5;
            $updateCoupon['SedeAgg'] = 1;
            $updateCoupon['IpAgg'] = $_SERVER['REMOTE_ADDR'];
            $db->update("RT_Coupon", $updateCoupon, "CouponId = $CouponId");
            
        } else {
            // Pagamento non completato - inserisco solo la transazione
            $data = null;
            $data['CouponId'] = $CouponId;
            $data['TipoPagamentoId'] = 1;
            $data['CodiceTransazione'] = $CodiceTransazione;
            $data['payment_type'] = $_POST['payment_type'];
            $data['payment_status'] = $_POST['payment_status'];
            $data['address_status'] = $_POST['address_status'];
            $data['payer_status'] = $_POST['payer_status'];
            $data['first_name'] = $_POST['first_name'];
            $data['last_name'] = $_POST['last_name'];
            $data['payer_email'] = $_POST['payer_email'];
            $data['payer_id'] = $_POST['payer_id'];
            $data['mc_gross'] = $_POST['mc_gross'];
            $data['ImportoCoupon'] = $importo_coupon;
            $data['DataIns'] = date('Y-m-d H:i:s');
            $data['OpeIns'] = 5;
            $data['SedeIns'] = 1;
            $data['IpIns'] = $_SERVER['REMOTE_ADDR'];
            $data['OdcIdRef'] = 1;
            $data['GestoreIdRef'] = 1;
            $data['Stato'] = 1;
            $data['Cancella'] = 0;
            $transactionId = $db->insert("RT_CouponTransazione", $data);
        }
    }
    
} else {
    // Gestione prenotazione normale (codice esistente)
    $PrenotazioneId = $custom;
    $CodiceTransazione = $_POST['txn_id'];
    $mc_gross = $_POST['mc_gross'];
    
    $sql = "SELECT * FROM RT_Prenotazione WHERE Stato = 1 AND Cancella = 0 AND (PrenotazioneStato = 1 OR PrenotazioneStato = 11) AND PrenotazioneId = $PrenotazioneId";
    $prenotazione = $db->query_first($sql);
    
    if (empty($prenotazione['PrenotazioneId'])) {
        die();
    }
    
    if ($prenotazione['Multi']) {
        $sql = "SELECT SUM(TotalePrenotazione) TotalePrenotazione, SUM(TotaleDaPagare) TotaleDaPagare, SUM(TotaleDaPagareMulti) TotaleDaPagareMulti, SUM(TotalePagato) TotalePagato, SUM(TotaleResiduo) TotaleResiduo FROM RT_Prenotazione WHERE CodicePrenotazione = '" . $prenotazione['CodicePrenotazione'] . "' AND PrenotazioneStato = " . $prenotazione['PrenotazioneStato'];
        $totaliImporti = $db->query_first($sql);
        
        if ($totaliImporti['TotaleDaPagareMulti'] != 0) {
            $totaliImporti['TotaleDaPagare'] = $totaliImporti['TotaleDaPagareMulti'];
        }
    } else {
        $sql = "SELECT SUM(TotalePrenotazione) TotalePrenotazione, SUM(TotaleDaPagare) TotaleDaPagare, SUM(TotalePagato) TotalePagato, SUM(TotaleResiduo) TotaleResiduo FROM RT_Prenotazione WHERE PrenotazioneId = " . $prenotazione['PrenotazioneId'];
        $totaliImporti = $db->query_first($sql);
    }
    
    $totale_prenotazione = $totaliImporti['TotaleDaPagare'];
    // verifico se l'importo nel db corrisponde all'importo della transazione
    if ($totale_prenotazione != $mc_gross)
        die();

    // verifico che non esista il codice transazione
    $s = "select PrenotazioneTransazioneWeb from RT_PrenotazioneTransazione where CodiceTransazione='$CodiceTransazione'";
    $row = $db->query_first($s);

    if (empty($row['PrenotazioneTransazioneWeb'])) {
        //verifico che lo stato sia completed   
        $payment_status = $_POST['payment_status'];
        if ($payment_status == 'Completed') {
            $data = null;
            $data['PrenotazioneId'] = $PrenotazioneId;
            $data['TipoPagamentoId'] = 1;
            $data['CodiceTransazione'] = $CodiceTransazione;
            $data['payment_type'] = $_POST['payment_type'];
            $data['payment_status'] = $_POST['payment_status'];
            $data['address_status'] = $_POST['address_status'];
            $data['payer_status'] = $_POST['payer_status'];
            $data['first_name'] = $_POST['first_name'];
            $data['last_name'] = $_POST['last_name'];
            $data['payer_email'] = $_POST['payer_email'];
            $data['payer_id'] = $_POST['payer_id'];
            $data['mc_gross'] = $_POST['mc_gross'];
            $data['ImportoPrenotazione'] = $totale_prenotazione;
            $data['DataIns'] = date('Y-m-d H:i:s');
            $data['OpeIns'] = 5;
            $data['SedeIns'] = 1;
            $data['IpIns'] = $_SERVER['REMOTE_ADDR'];
            $data['OdcIdRef'] = 1;
            $data['GestoreIdRef'] = 1;
            $transactionId = $db->insert("RT_PrenotazioneTransazione", $data);
        
            if (Config::$salesmanago_enabled) {
                $sql = "SELECT * FROM RT_SalesManagoEvent WHERE PrenotazioneId = $PrenotazioneId";
                $event = $db->query_first($sql);
                if (!empty($event)) {
                    $salesManago = new ServiceSalesManago($db);
                    $sql = "SELECT Lingua, ConsensoPrivacy, ConsensoMarketing, ConsensoProfilazione FROM RT_Prenotazione WHERE PrenotazioneId = $PrenotazioneId";
                    $rowP = $db->query_first($sql);
                    $langEv = ($event['Lang'] == 'it' ? 'it' : $event['Lang']);
                    $consentDetails = array();
                    $agreementDate = round(microtime(true) * 1000);
                    $ipAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
                    if ($langEv != 'it') {
                        $acceptPrivacy = (isset($rowP['ConsensoPrivacy']) && $rowP['ConsensoPrivacy'] == 1);
                        $acceptMarketing = (isset($rowP['ConsensoMarketing']) && $rowP['ConsensoMarketing'] == 1);
                        $acceptProfilazione = (isset($rowP['ConsensoProfilazione']) && $rowP['ConsensoProfilazione'] == 1);
                        $consentDetails[] = array('consentName' => 'PRIVACY_ENG', 'consentAccept' => $acceptPrivacy, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5496);
                        $consentDetails[] = array('consentName' => 'MARKETING_ENG', 'consentAccept' => $acceptMarketing, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5497);
                        $consentDetails[] = array('consentName' => 'PROFILAZIONE_ENG', 'consentAccept' => $acceptProfilazione, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5498);
                    } else {
                        $acceptPrivacy = (isset($rowP['ConsensoPrivacy']) && $rowP['ConsensoPrivacy'] == 1);
                        $acceptMarketing = (isset($rowP['ConsensoMarketing']) && $rowP['ConsensoMarketing'] == 1);
                        $acceptProfilazione = (isset($rowP['ConsensoProfilazione']) && $rowP['ConsensoProfilazione'] == 1);
                        $consentDetails[] = array('consentName' => 'PRIVACY_ITA', 'consentAccept' => $acceptPrivacy, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5493);
                        $consentDetails[] = array('consentName' => 'MARKETING_ITA', 'consentAccept' => $acceptMarketing, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5494);
                        $consentDetails[] = array('consentName' => 'PROFILAZIONE_ITA', 'consentAccept' => $acceptProfilazione, 'agreementDate' => $agreementDate, 'ip' => $ipAddr, 'optOut' => false, 'consentDescriptionId' => 5495);
                    }
                    $result = $salesManago->sendEvent($PrenotazioneId, 
                        ServiceSalesManago::EVENT_PURCHASE, 
                        $event['Email'], 
                        $event['Products'],
                        $event['Value'],
                        $langEv,
                        $event['Detail1'], 
                        $event['Detail2'],
                        $event['ContactPhone'],
                        $event['ContactName'],
                        $event['ContactCountry'],
                        null,
                        'web',
                        $consentDetails
                    );
                }
            }
        
        } else {
            $data = null;
            $data['PrenotazioneId'] = $PrenotazioneId;
            $data['TipoPagamentoId'] = 1;
            $data['CodiceTransazione'] = $CodiceTransazione;
            $data['payment_type'] = $_POST['payment_type'];
            $data['payment_status'] = $_POST['payment_status'];
            $data['address_status'] = $_POST['address_status'];
            $data['payer_status'] = $_POST['payer_status'];
            $data['first_name'] = $_POST['first_name'];
            $data['last_name'] = $_POST['last_name'];
            $data['payer_email'] = $_POST['payer_email'];
            $data['payer_id'] = $_POST['payer_id'];
            $data['mc_gross'] = $_POST['mc_gross'];
            $data['ImportoPrenotazione'] = $totale_prenotazione;
            $data['DataIns'] = date('Y-m-d H:i:s');
            $data['OpeIns'] = 5;
            $data['SedeIns'] = 1;
            $data['IpIns'] = $_SERVER['REMOTE_ADDR'];
            $data['OdcIdRef'] = 1;
            $data['GestoreIdRef'] = 1;
            $transactionId = $db->insert("RT_PrenotazioneTransazioneNonCompleta", $data);
        }
    }
}
} elseif (strcmp ($res, "INVALID") == 0) {
    // log for manual investigation
}
//}

?>
