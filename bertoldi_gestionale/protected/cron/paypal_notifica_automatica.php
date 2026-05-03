<?php
// Imposta il percorso base e include i file necessari
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL);

// Inizializza la configurazione e le classi necessarie
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();

// Include delle classi utilizzate nel sistema
include_once($classespath_ . "class.Ruolo.php");
include_once($classespath_ . "class.Sede.php");
include_once($classespath_ . "class.Gestore.php");
include_once($classespath_ . "class.Nazione.php");
include_once($classespath_ . "class.Regione.php");
include_once($classespath_ . "class.Comune.php");
include_once($classespath_ . "class.Percorso.php");
include_once($classespath_ . "class.Linea.php");
include_once($classespath_ . "class.Corsa.php");
include_once($classespath_ . "class.Tratta.php");
include_once($classespath_ . "class.TrattaTipo.php");
include_once($classespath_ . "class.Mezzo.php");
include_once($classespath_ . "class.TrattaDirezione.php");
include_once($classespath_ . "class.Prenotazione.php");
include_once($classespath_ . "class.Fermata.php");
include_once($classespath_ . "class.PrenotazioneMovimento.php");
include_once($classespath_ . "class.PagamentoTipo.php");
include_once($classespath_ . "class.PrefissoTelefono.php");
include_once($classespath_ . "class.ServiceSalesManago.php");

// Connessione al database
$db = new Database();
$db->connect();

// PRIMA COSA: Memorizza immediatamente la notifica PayPal ricevuta
$notificaData = array();
$notificaData['DataNotifica'] = date('Y-m-d H:i:s');

// Recupera i dati grezzi PRIMA di elaborarli
$raw_post_data = file_get_contents('php://input');

// Memorizza sia $_POST che i dati grezzi
$allData = array();
$allData['POST'] = $_POST;
$allData['RAW_INPUT'] = $raw_post_data;
$allData['REQUEST'] = $_REQUEST;

$notificaData['PostData'] = json_encode($allData, JSON_UNESCAPED_UNICODE);
$notificaId = $db->insert("PayPalNotifica", $notificaData);

// Poi elabora i dati come prima
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();

// Decodifica i dati POST in un array associativo
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}

// Prepara la richiesta per la validazione IPN di PayPal
$req = 'cmd=_notify-validate';
if (function_exists('get_magic_quotes_gpc')) {
    $get_magic_quotes_exists = true;
}
foreach ($myPost as $key => $value) {
    if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
        $value = urlencode(stripslashes($value));
    } else {
        $value = urlencode($value);
    }
    $req .= "&$key=$value";
}

// STEP 2: Invia i dati IPN a PayPal per la validazione
$PAYPAL_LINK = Config::$paypalLink;
$ch = curl_init($PAYPAL_LINK);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
$res = curl_exec($ch);
curl_close($ch);

// STEP 3: Analizza il risultato della validazione IPN
if (strcmp($res, "VERIFIED") == 0) {
    
    // USA $myPost invece di $_POST per consistenza e con controlli isset()
    $custom = isset($myPost['custom']) ? $myPost['custom'] : '';
    $CodiceTransazione = isset($myPost['txn_id']) ? $myPost['txn_id'] : '';
    $mc_gross = isset($myPost['mc_gross']) ? $myPost['mc_gross'] : '';
    $payment_status = isset($myPost['payment_status']) ? $myPost['payment_status'] : '';
    
    // Fallback a $_POST se $myPost è vuoto, ma sempre con isset()
    if (empty($custom) && isset($_POST['custom'])) {
        $custom = $_POST['custom'];
    }
    if (empty($CodiceTransazione) && isset($_POST['txn_id'])) {
        $CodiceTransazione = $_POST['txn_id'];
    }
    if (empty($mc_gross) && isset($_POST['mc_gross'])) {
        $mc_gross = $_POST['mc_gross'];
    }
    if (empty($payment_status) && isset($_POST['payment_status'])) {
        $payment_status = $_POST['payment_status'];
    }
    
    // Fallback finale a $_REQUEST se necessario
    if (empty($custom) && isset($_REQUEST['custom'])) {
        $custom = $_REQUEST['custom'];
    }
    if (empty($CodiceTransazione) && isset($_REQUEST['txn_id'])) {
        $CodiceTransazione = $_REQUEST['txn_id'];
    }
    if (empty($mc_gross) && isset($_REQUEST['mc_gross'])) {
        $mc_gross = $_REQUEST['mc_gross'];
    }
    if (empty($payment_status) && isset($_REQUEST['payment_status'])) {
        $payment_status = $_REQUEST['payment_status'];
    }
    
    // Determina se la notifica riguarda un coupon o una prenotazione
    if (strpos($custom, 'COUPON_') === 0) {
        // Gestione pagamento coupon
        $CouponId = str_replace('COUPON_', '', $custom);
        
        // Recupera il coupon dal database
        $sql = "SELECT * FROM RT_Coupon WHERE CouponId = $CouponId AND DaVendere = 1";
        $coupon = $db->query_first($sql);
        
        if (empty($coupon['CouponId'])) {
            die("COUPON NON TROVATO");
        }
        
        $totale_coupon = $coupon['Importo'];
        
        // Verifica che la transazione non sia già stata registrata
        $sql = "SELECT CouponTransazioneId FROM RT_CouponTransazione WHERE CodiceTransazione = '$CodiceTransazione'";
        $row = $db->query_first($sql);
        
        if (empty($row['CouponTransazioneId'])) {
            
            // Se il pagamento è completato, registra la transazione e aggiorna lo stato del coupon
            if (($payment_status == 'Completed') || ($payment_status == 'completed')) {
                $data = null;
                $data['CouponId'] = $CouponId;
                $data['TipoPagamentoId'] = 1;
                $data['CodiceTransazione'] = $CodiceTransazione;
                
                // USA isset() per tutti i campi $_POST
                $data['payment_type'] = isset($_POST['payment_type']) ? $_POST['payment_type'] : '';
                $data['payment_status'] = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
                $data['address_status'] = isset($_POST['address_status']) ? $_POST['address_status'] : '';
                $data['payer_status'] = isset($_POST['payer_status']) ? $_POST['payer_status'] : '';
                $data['first_name'] = isset($_POST['first_name']) ? $_POST['first_name'] : '';
                $data['last_name'] = isset($_POST['last_name']) ? $_POST['last_name'] : '';
                $data['payer_email'] = isset($_POST['payer_email']) ? $_POST['payer_email'] : '';
                $data['payer_id'] = isset($_POST['payer_id']) ? $_POST['payer_id'] : '';
                $data['mc_gross'] = isset($_POST['mc_gross']) ? $_POST['mc_gross'] : '';
                
                $data['ImportoCoupon'] = $totale_coupon;
                $data['DataIns'] = date('Y-m-d H:i:s');
                $data['OpeIns'] = 5;
                $data['SedeIns'] = 1;
                $data['IpIns'] = $_SERVER['REMOTE_ADDR'];
                $data['OdcIdRef'] = 1;
                $data['GestoreIdRef'] = 1;
                $data['Stato'] = 1;
                $data['Cancella'] = 0;
                $transactionId = $db->insert("RT_CouponTransazione", $data);
                
                // Aggiorna lo stato del coupon a pagato
                $updateCoupon = array();
                $updateCoupon['VenditaStato'] = 2; // Pagato
                $updateCoupon['DataAgg'] = date('Y-m-d H:i:s');
                $updateCoupon['OpeAgg'] = 5;
                $updateCoupon['SedeAgg'] = 1;
                $updateCoupon['IpAgg'] = $_SERVER['REMOTE_ADDR'];
                $db->update("RT_Coupon", $updateCoupon, "CouponId = $CouponId");
                
            } else {
                // Se la transazione non è completata, non fa nulla (codice commentato)
            }
        }
        
    } else {
        // Gestione pagamento prenotazione
        $PrenotazioneId = $custom;
        
        // Recupera la prenotazione dal database
        $sql = "SELECT * FROM RT_Prenotazione WHERE Stato = 1 AND Cancella = 0 AND (PrenotazioneStato = 1 OR PrenotazioneStato = 11 OR PrenotazioneStato = 13) AND PrenotazioneId = $PrenotazioneId";
        $prenotazione = $db->query_first($sql);
        
        if (empty($prenotazione['PrenotazioneId'])) {
            die("PRENOTAZIONE NON TROVATA");
        }
        
        // Calcola i totali della prenotazione (gestione multi-prenotazione)
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
        
        // Verifica che la transazione non sia già stata registrata
        $sql = "SELECT PrenotazioneTransazioneId FROM RT_PrenotazioneTransazione WHERE CodiceTransazione = '$CodiceTransazione'";
        $row = $db->query_first($sql);
        
        if (empty($row['PrenotazioneTransazioneId'])) {
            
            if (($payment_status == 'Completed') || ($payment_status == 'completed')) {
                // Registra la transazione come completata
                $data = null;
                $data['PrenotazioneId'] = $PrenotazioneId;
                $data['TipoPagamentoId'] = 1;
                $data['CodiceTransazione'] = $CodiceTransazione;
                
                // USA isset() per tutti i campi $_POST
                $data['payment_type'] = isset($_POST['payment_type']) ? $_POST['payment_type'] : '';
                $data['payment_status'] = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
                $data['address_status'] = isset($_POST['address_status']) ? $_POST['address_status'] : '';
                $data['payer_status'] = isset($_POST['payer_status']) ? $_POST['payer_status'] : '';
                $data['first_name'] = isset($_POST['first_name']) ? $_POST['first_name'] : '';
                $data['last_name'] = isset($_POST['last_name']) ? $_POST['last_name'] : '';
                $data['payer_email'] = isset($_POST['payer_email']) ? $_POST['payer_email'] : '';
                $data['payer_id'] = isset($_POST['payer_id']) ? $_POST['payer_id'] : '';
                $data['mc_gross'] = isset($_POST['mc_gross']) ? $_POST['mc_gross'] : '';
                
                $data['ImportoPrenotazione'] = $totale_prenotazione;
                $data['DataIns'] = date('Y-m-d H:i:s');
                $data['OpeIns'] = 5;
                $data['SedeIns'] = 1;
                $data['IpIns'] = $_SERVER['REMOTE_ADDR'];
                $data['OdcIdRef'] = 1;
                $data['GestoreIdRef'] = 1;
                $transactionId = $db->insert("RT_PrenotazioneTransazione", $data);

                if(!isset($transactionId) || $transactionId === false) {
                    // Registra la transazione come completata
                    $data = null;
                    $data['PrenotazioneId'] = $PrenotazioneId;
                    $data['TipoPagamentoId'] = 1;
                    $data['CodiceTransazione'] = $CodiceTransazione;
                    
                    // USA isset() per tutti i campi $_POST
                    $data['payment_type'] = 'instant';
                    $data['payment_status'] = 'Completed';
                    $data['address_status'] = '';
                    $data['payer_status'] = 'unverified';
                    $data['first_name'] = '';
                    $data['last_name'] = '';
                    $data['payer_email'] = '';
                    $data['payer_id'] = '';
                    $data['mc_gross'] = $totale_prenotazione;
                    
                    $data['ImportoPrenotazione'] = $totale_prenotazione;
                    $data['DataIns'] = date('Y-m-d H:i:s');
                    $data['OpeIns'] = 5;
                    $data['SedeIns'] = 1;
                    $data['IpIns'] = $_SERVER['REMOTE_ADDR'];
                    $data['OdcIdRef'] = 1;
                    $data['GestoreIdRef'] = 1;
                    $transactionId = $db->insert("RT_PrenotazioneTransazione", $data);
                }   
                
                // Invio evento a SalesManago se presente
                $sql = "SELECT * FROM RT_SalesManagoEvent WHERE PrenotazioneId = $PrenotazioneId";
                $event = $db->query_first($sql);
                if (!empty($event)) {
                    $salesManago = new ServiceSalesManago($db);
                    $result = $salesManago->sendEvent($PrenotazioneId, 
                        ServiceSalesManago::EVENT_PURCHASE, 
                        $event['Email'], 
                        $event['Products'],
                        $event['Value'],
                        $event['Lang'] == 'it' ? 'it' : $event['Lang'],
                        $event['Detail1'], 
                        $event['Detail2'],
                        $event['ContactPhone'],
                        $event['ContactName'],
                        $event['ContactCountry'],
                        '', // Descrizione non presente
						'office paypal'
                    );

                    $notificaData['DataNotifica'] = date('Y-m-d H:i:s');
                    $notificaData['PostData'] = json_encode($_POST);
                    $notificaId = $db->insert("PayPalNotifica", $notificaData);
                }
                
            } else {
                // Se la transazione non è completata, la registra in una tabella separata
                $data = null;
                $data['PrenotazioneId'] = $PrenotazioneId;
                $data['TipoPagamentoId'] = 1;
                $data['CodiceTransazione'] = $CodiceTransazione;
                
                $data['payment_type'] = isset($_POST['payment_type']) ? $_POST['payment_type'] : '';
                $data['payment_status'] = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
                $data['address_status'] = isset($_POST['address_status']) ? $_POST['address_status'] : '';
                $data['payer_status'] = isset($_POST['payer_status']) ? $_POST['payer_status'] : '';
                $data['first_name'] = isset($_POST['first_name']) ? $_POST['first_name'] : '';
                $data['last_name'] = isset($_POST['last_name']) ? $_POST['last_name'] : '';
                $data['payer_email'] = isset($_POST['payer_email']) ? $_POST['payer_email'] : '';
                $data['payer_id'] = isset($_POST['payer_id']) ? $_POST['payer_id'] : '';
                $data['mc_gross'] = isset($_POST['mc_gross']) ? $_POST['mc_gross'] : '';
                
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

} elseif (strcmp($res, "INVALID") == 0) {
    
    // SALVA I DATI INVALID PER ANALISI
    $invalidData = array();
    $invalidData['DataNotifica'] = date('Y-m-d H:i:s');
    $invalidData['RispostaPayPal'] = 'INVALID';
    $invalidData['IpMittente'] = $_SERVER['REMOTE_ADDR'];
    $invalidData['UserAgent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    
    // Memorizza tutti i dati ricevuti
    $allInvalidData = array();
    $allInvalidData['POST'] = $_POST;
    $allInvalidData['RAW_INPUT'] = $raw_post_data;
    $allInvalidData['REQUEST'] = $_REQUEST;
    $allInvalidData['SERVER'] = $_SERVER;
    $allInvalidData['HEADERS'] = getallheaders(); // Se disponibile
    
    $invalidData['DatiCompleti'] = json_encode($allInvalidData, JSON_UNESCAPED_UNICODE);
    
    // Tenta di estrarre informazioni chiave per l'analisi
    $invalidData['TentativoCustom'] = isset($_POST['custom']) ? $_POST['custom'] : '';
    $invalidData['TentativoTxnId'] = isset($_POST['txn_id']) ? $_POST['txn_id'] : '';
    $invalidData['TentativoAmount'] = isset($_POST['mc_gross']) ? $_POST['mc_gross'] : '';
    $invalidData['TentativoEmail'] = isset($_POST['payer_email']) ? $_POST['payer_email'] : '';
    $invalidData['TentativoPaymentStatus'] = isset($_POST['payment_status']) ? $_POST['payment_status'] : '';
    
    // Inserisci nella tabella delle notifiche invalid
    $db->insert("PayPalNotificaInvalid", $invalidData);
    
    // Log aggiuntivo per debugging
    error_log("PayPal INVALID IPN ricevuto da IP: " . $_SERVER['REMOTE_ADDR'] . " - Data: " . date('Y-m-d H:i:s'));
    
    die("error invalid");
    
} else {
    
    // CASO RARO: PayPal ha restituito qualcos'altro
    $errorData = array();
    $errorData['DataNotifica'] = date('Y-m-d H:i:s');
    $errorData['RispostaPayPal'] = $res; // Salva la risposta esatta
    $errorData['IpMittente'] = $_SERVER['REMOTE_ADDR'];
    
    $allErrorData = array();
    $allErrorData['POST'] = $_POST;
    $allErrorData['RAW_INPUT'] = $raw_post_data;
    $allErrorData['REQUEST'] = $_REQUEST;
    $allErrorData['CURL_RESPONSE'] = $res;
    
    $errorData['DatiCompleti'] = json_encode($allErrorData, JSON_UNESCAPED_UNICODE);
    
    // Inserisci nella tabella degli errori
    $db->insert("PayPalNotificaErrore", $errorData);
    
    error_log("PayPal risposta inattesa: " . $res . " - IP: " . $_SERVER['REMOTE_ADDR']);
    
    die("error unknown response");
}
?>
