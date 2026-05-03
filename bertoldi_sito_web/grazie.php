<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
$PageTitle = $dizionario['grazie']['titolo'];
$PageDescription = $dizionario['index']['meta_descr'];
$PageKeywords = $dizionario['index']['meta_key'];
$config = new Config();
$run = $config->load();
$classespath_ = Config::$classespath;
$db = new Database();
$conn = $db->connect();  
include_once($basepath."/include/meta.php");
use Stripe\Stripe;

$error = true;
$cliente_nome;

if(isset($_SESSION['USER'])) {
    $userInfo = $_SESSION['USER'];
}

getGestoreByCode();

$tipo = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

if ($tipo == 'coupon') {
    // --- GESTIONE COUPON ---
    if (isset($_REQUEST['OrderId']) and ($_REQUEST['OrderId']!='')  ) {
        if (isset($_REQUEST['em']) and ($_REQUEST['em']!='')  ) {
            $CouponId = trim($_REQUEST['OrderId']);
            $email = trim($_REQUEST['em']);
            $sql = "SELECT
                RT_Coupon.VenditaEmail,
                RT_Coupon.CouponId,
                RT_Coupon.MembershipClubCode,
                mc.CognomeRagioneSociale,
                mc.Nome AS NomeClub
            FROM
                RT_Coupon
                LEFT JOIN RT_MembershipClub mc ON RT_Coupon.MembershipClubCode = mc.MembershipClubCode
                INNER JOIN RT_CouponTransazione ON RT_Coupon.CouponId = RT_CouponTransazione.CouponId
            WHERE
                RT_CouponTransazione.CodiceTransazione IS NOT NULL
                AND RT_CouponTransazione.payment_status = 'Completed'
                AND RT_Coupon.VenditaEmail = '$email'
                AND RT_Coupon.CouponId = $CouponId";

            $row = $db->query_first($sql);

            if (!empty($row['CouponId'])) {
                if (!empty($row['MembershipClubCode']) && !empty($row['CognomeRagioneSociale'])) {
                    $cliente_nome = $row['NomeClub'] . ' ' . $row['CognomeRagioneSociale'];
                } else {
                    $cliente_nome = "Utente Ospite";
                }
                $error = false;
            } 
            
            $sessionId = null;
            if(isset($_REQUEST['session_id'])) {
                $sessionId = $_REQUEST['session_id'];
            }
            if(isset($sessionId)) {
                $sql = "SELECT * FROM RT_CouponTransazione WHERE CouponId = $CouponId";
                $tempRow = $db->query_first($sql);
                
                if(!isset($tempRow['CouponId'])) {
                    Stripe::setApiKey($config::$StripeSecretKey);
                    $session = \Stripe\Checkout\Session::retrieve($sessionId);
                    if(isset($session->customer)) {
                        $customer = \Stripe\Customer::retrieve($session->customer);
                        $CodiceTransazione = $customer->invoice_prefix;
                        $payer_email = $customer->email;
                        $payer_id = $customer->id;
                    } else {
                        $CodiceTransazione = $session->payment_intent;
                        $payer_email = $session->customer_details->email;
                        $payer_id = $session->customer_details->email;
                    }
                    
                    $sql = "SELECT 
                        RT_Coupon.*, 
                        mc.CognomeRagioneSociale as Cognome, 
                        mc.Nome as Nome
                    FROM RT_Coupon 
                    LEFT JOIN RT_MembershipClub mc ON RT_Coupon.MembershipClubCode = mc.MembershipClubCode 
                    WHERE RT_Coupon.CouponId = $CouponId";

                    $rowCoupon = $db->query_first($sql);

                    $amountTotal = (int)$session->amount_total;
                    if ($amountTotal > 0) {
                        $paymentStatus = 'Completed';
                    } else {
                        $paymentStatus = 'Error';
                    }
                    
                    $transazione['CouponId'] = $CouponId;
                    $transazione['TipoPagamentoId'] = 22;
                    $transazione['CodiceTransazione'] = $CodiceTransazione;
                    $transazione['address_status'] = 'confirmed';
                    $transazione['payer_status'] = 'verified';
                    $transazione['first_name'] = $rowCoupon['Nome'];
                    $transazione['last_name'] = $rowCoupon['Cognome'];
                    $transazione['payer_email'] = $payer_email;
                    $transazione['payer_id'] = $payer_id;
                    $transazione['payment_status'] = $paymentStatus;
                    $transazione['payment_type'] = 'instant';
                    $transazione['mc_gross'] = $session->amount_total/100;
                    $transazione['ImportoCoupon'] = $rowCoupon['Valore'];
                    $transazione['Notificata'] = 0;
                    $transazione['OpeIns']=Config::$userOperatoreId;
                    $transazione['SedeIns']=Config::$userSedeId;
                    $transazione['DataIns']=date('Y-m-d H:i:s');
                    $transazione['IpIns']=getenv('REMOTE_ADDR');
                    $transazione['OdcIdRef']=Config::$userOdcId;
                    $transazione['GestoreIdRef']=Config::$userGestoreId;
                    $transazione['Cancella']=0;
                    $transazione['Stato']=1;
                    $db->insert("RT_CouponTransazione", $transazione);
                }
            }
        } else {
            $reply = "REDIRECT=" . "https://booking.bertoldiboats.com/errore.php";
            ?>
            <script>
                self.location="errore.php";    
            </script>   
            <?php 
        }
    } else {
        ?>
        <script>
            self.location="errore.php";    
        </script>   
        <?php 
    }
} else {
    // --- GESTIONE PRENOTAZIONE STANDARD ---
    if (isset($_REQUEST['OrderId']) and ($_REQUEST['OrderId']!='')  ) {
        if (isset($_REQUEST['em']) and ($_REQUEST['em']!='')  ) {
            $PrenotazioneId = trim($_REQUEST['OrderId']);
            $email = trim($_REQUEST['em']);
            $sql = "SELECT
                RT_Prenotazione.ClienteEmail,
                RT_Prenotazione.PrenotazioneId,
                RT_Prenotazione.ClienteNome
                FROM
                RT_Prenotazione
                INNER JOIN RT_PrenotazioneTransazione ON RT_Prenotazione.PrenotazioneId = RT_PrenotazioneTransazione.PrenotazioneId
                WHERE
                RT_PrenotazioneTransazione.CodiceTransazione IS NOT NULL AND
                RT_PrenotazioneTransazione.payment_status = 'Completed' and ClienteEmail = '$email' and RT_Prenotazione.PrenotazioneId = $PrenotazioneId";
            
            $row=$db->query_first($sql);
            if (!empty($row['PrenotazioneId'])) {
                $cliente_nome = $row['ClienteNome'];
                $error=false;
            }
            
            $sessionId = null;
            if(isset($_REQUEST['session_id'])) {
                $sessionId = $_REQUEST['session_id'];
            }
            if(isset($sessionId)) {
                $sql = "SELECT * FROM RT_PrenotazioneTransazione WHERE PrenotazioneId = $PrenotazioneId";
                $tempRow = $db->query_first($sql);
                
                if(!isset($tempRow['PrenotazioneId'])) {
                    Stripe::setApiKey($config::$StripeSecretKey);
                    $session = \Stripe\Checkout\Session::retrieve($sessionId);
                    if(isset($session->customer)) {
                        $customer = \Stripe\Customer::retrieve($session->customer);
                        $CodiceTransazione = $customer->invoice_prefix;
                        $payer_email = $customer->email;
                        $payer_id = $customer->id;
                    } else {
                        $CodiceTransazione = $session->payment_intent;
                        $payer_email = $session->customer_details->email;
                        $payer_id = $session->customer_details->email;
                    }
                    
                    $sql = "SELECT * FROM RT_PrenotazionePasseggeri
                            where Principale = 1 AND PrenotazioneId = $PrenotazioneId";
                    $rowPasseggero = $db->query_first($sql);
                    
                    $sql = "SELECT
                                RT_Prenotazione.ClienteEmail,
                                RT_Prenotazione.PrenotazioneId,
                                RT_Prenotazione.ClienteNome,
                                RT_Prenotazione.TotaleDaPagare
                            FROM RT_Prenotazione
                            WHERE RT_Prenotazione.PrenotazioneId = $PrenotazioneId";
                    $rowP = $db->query_first($sql);

                    $amountTotal = (int)$session->amount_total;
                    if ($amountTotal > 0) {
                        $paymentStatus = 'Completed';
                    } else {
                        $paymentStatus = 'Error';
                    }
                    
                    $transazione['PrenotazioneId'] = $PrenotazioneId;
                    $transazione['TipoPagamentoId'] = 22;
                    $transazione['CodiceTransazione'] = $CodiceTransazione;
                    $transazione['address_status'] = 'confirmed';
                    $transazione['payer_status'] = 'verified';
                    $transazione['first_name'] = $rowPasseggero['Nome'];
                    $transazione['last_name'] = $rowPasseggero['Cognome'];
                    $transazione['payer_email'] = $payer_email;
                    $transazione['payer_id'] = $payer_id;
                    $transazione['payment_status'] = $paymentStatus;
                    $transazione['payment_type'] = 'instant';
                    $transazione['mc_gross'] = $session->amount_total/100;
                    $transazione['ImportoPrenotazione'] = $rowP['TotaleDaPagare'];
                    $transazione['Notificata'] = 0;
                    $transazione['OpeIns']=Config::$userOperatoreId;
                    $transazione['SedeIns']=Config::$userSedeId;
                    $transazione['DataIns']=date('Y-m-d H:i:s');
                    $transazione['IpIns']=getenv('REMOTE_ADDR');
                    $transazione['OdcIdRef']=Config::$userOdcId;
                    $transazione['GestoreIdRef']=Config::$userGestoreId;
                    $transazione['Cancella']=0;
                    $transazione['Stato']=1;
                    $transazioneId = $db->insert("RT_PrenotazioneTransazione", $transazione);

                    if(isset($transazioneId)) {
                        $error = false;
                    }

                    if (isset($transazioneId) && Config::$salesmanago_enabled) {
                        $sql = "SELECT * FROM RT_SalesManagoEvent WHERE PrenotazioneId = $PrenotazioneId";
                        $event = $db->query_first($sql);
                        if (isset($event) && isset($event['Email'])) {
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
                                'web stripe',
                                $consentDetails
                            );
                        }
                    }
                }
            } else {
                $error = false;
                // PAYPAL: Gestione ritorno da PayPal
                if (isset($_GET['tx']) && isset($_GET['st']) && $_GET['st'] == 'Completed') {
                    $txn_id = $_GET['tx'];
                    $payment_status = $_GET['st'];
                    
                    // Verifica se la transazione PayPal non esiste già
                    $sql = "SELECT * FROM RT_PrenotazioneTransazione WHERE PrenotazioneId = $PrenotazioneId AND CodiceTransazione = '$txn_id'";
                    $tempRow = $db->query_first($sql);
                    
                    if(!isset($tempRow['PrenotazioneId'])) {
                        // Transazione non esiste - inseriscila
                        
                        // Recupera dati passeggero principale
                        $sql = "SELECT * FROM RT_PrenotazionePasseggeri
                                WHERE Principale = 1 AND PrenotazioneId = $PrenotazioneId";
                        $rowPasseggero = $db->query_first($sql);
                        
                        // Recupera dati prenotazione
                        $sql = "SELECT
                                    RT_Prenotazione.ClienteEmail,
                                    RT_Prenotazione.PrenotazioneId,
                                    RT_Prenotazione.ClienteNome,
                                    RT_Prenotazione.TotaleDaPagare
                                FROM RT_Prenotazione
                                WHERE RT_Prenotazione.PrenotazioneId = $PrenotazioneId";
                        $rowP = $db->query_first($sql);
                        
                        // Recupera parametri aggiuntivi PayPal dall'URL
                        $amount = isset($_GET['amt']) ? floatval($_GET['amt']) : $rowP['TotaleDaPagare'];
                        $payer_email = isset($_GET['payer_email']) ? $_GET['payer_email'] : $rowP['ClienteEmail'];
                        
                        // Prepara dati transazione PayPal
                        $transazione = array();
                        $transazione['PrenotazioneId'] = $PrenotazioneId;
                        $transazione['TipoPagamentoId'] = 1; // PayPal
                        $transazione['CodiceTransazione'] = $txn_id;
                        $transazione['address_status'] = 'confirmed';
                        $transazione['payer_status'] = 'verified';
                        $transazione['first_name'] = isset($rowPasseggero['Nome']) ? $rowPasseggero['Nome'] : '';
                        $transazione['last_name'] = isset($rowPasseggero['Cognome']) ? $rowPasseggero['Cognome'] : '';
                        $transazione['payer_email'] = $payer_email;
                        $transazione['payer_id'] = isset($_GET['payer_id']) ? $_GET['payer_id'] : '';
                        $transazione['payment_status'] = $payment_status;
                        $transazione['payment_type'] = 'instant';
                        $transazione['mc_gross'] = $amount;
                        $transazione['ImportoPrenotazione'] = $rowP['TotaleDaPagare'];
                        $transazione['Notificata'] = 0;
                        $transazione['OpeIns'] = Config::$userOperatoreId;
                        $transazione['SedeIns'] = Config::$userSedeId;
                        $transazione['DataIns'] = date('Y-m-d H:i:s');
                        $transazione['IpIns'] = getenv('REMOTE_ADDR');
                        $transazione['OdcIdRef'] = Config::$userOdcId;
                        $transazione['GestoreIdRef'] = Config::$userGestoreId;
                        $transazione['Cancella'] = 0;
                        $transazione['Stato'] = 1;
                        
                        $transazioneId = $db->insert("RT_PrenotazioneTransazione", $transazione);
                        
                        if ($transazioneId) {
                            // Aggiorna stato prenotazione
                            $updateData = array();
                            $updateData['PrenotazioneStato'] = 3; // Confermata e Pagata
                            $updateData['PrenotazioneStatoPagamento'] = 2; // Pagata
                            $updateData['Pagato'] = 1;
                            $updateData['TotalePagato'] = $amount;
                            $updateData['TotaleResiduo'] = 0;
                            
                            $db->update("RT_Prenotazione", $updateData, "PrenotazioneId = $PrenotazioneId");
                            
                            // Imposta variabili per visualizzazione successo
                            $cliente_nome = $rowP['ClienteNome'];
                            $error = false;
                            
                            // SalesManago event se abilitato
                            if (Config::$salesmanago_enabled) {
                                $sql = "SELECT * FROM RT_SalesManagoEvent WHERE PrenotazioneId = $PrenotazioneId";
                                $event = $db->query_first($sql);
                                if (isset($event) && isset($event['Email'])) {
                                    $salesManago = new ServiceSalesManago($db);
                                    $sql = "SELECT Lingua, ConsensoPrivacy, ConsensoMarketing, ConsensoProfilazione FROM RT_Prenotazione WHERE PrenotazioneId = $PrenotazioneId";
                                    $rowP2 = $db->query_first($sql);
                                    $langEv2 = ($event['Lang'] == 'it' ? 'it' : $event['Lang']);
                                    $consentDetails2 = array();
                                    $agreementDate2 = round(microtime(true) * 1000);
                                    $ipAddr2 = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
                                    if ($langEv2 == 'en') {
                                        $acceptPrivacy2 = (isset($rowP2['ConsensoPrivacy']) && $rowP2['ConsensoPrivacy'] == 1);
                                        $acceptMarketing2 = (isset($rowP2['ConsensoMarketing']) && $rowP2['ConsensoMarketing'] == 1);
                                        $acceptProfilazione2 = (isset($rowP2['ConsensoProfilazione']) && $rowP2['ConsensoProfilazione'] == 1);
                                        $consentDetails2[] = array('consentName' => 'PRIVACY_ENG', 'consentAccept' => $acceptPrivacy2, 'agreementDate' => $agreementDate2, 'ip' => $ipAddr2, 'optOut' => !$acceptPrivacy2, 'consentDescriptionId' => 5496);
                                        $consentDetails2[] = array('consentName' => 'MARKETING_ENG', 'consentAccept' => $acceptMarketing2, 'agreementDate' => $agreementDate2, 'ip' => $ipAddr2, 'optOut' => !$acceptMarketing2, 'consentDescriptionId' => 5497);
                                        $consentDetails2[] = array('consentName' => 'PROFILAZIONE_ENG', 'consentAccept' => $acceptProfilazione2, 'agreementDate' => $agreementDate2, 'ip' => $ipAddr2, 'optOut' => !$acceptProfilazione2, 'consentDescriptionId' => 5498);
                                    } else {
                                        $acceptPrivacy2 = (isset($rowP2['ConsensoPrivacy']) && $rowP2['ConsensoPrivacy'] == 1);
                                        $acceptMarketing2 = (isset($rowP2['ConsensoMarketing']) && $rowP2['ConsensoMarketing'] == 1);
                                        $acceptProfilazione2 = (isset($rowP2['ConsensoProfilazione']) && $rowP2['ConsensoProfilazione'] == 1);
                                        $consentDetails2[] = array('consentName' => 'PRIVACY_ITA', 'consentAccept' => $acceptPrivacy2, 'agreementDate' => $agreementDate2, 'ip' => $ipAddr2, 'optOut' => !$acceptPrivacy2, 'consentDescriptionId' => 5493);
                                        $consentDetails2[] = array('consentName' => 'MARKETING_ITA', 'consentAccept' => $acceptMarketing2, 'agreementDate' => $agreementDate2, 'ip' => $ipAddr2, 'optOut' => !$acceptMarketing2, 'consentDescriptionId' => 5494);
                                        $consentDetails2[] = array('consentName' => 'PROFILAZIONE_ITA', 'consentAccept' => $acceptProfilazione2, 'agreementDate' => $agreementDate2, 'ip' => $ipAddr2, 'optOut' => !$acceptProfilazione2, 'consentDescriptionId' => 5495);
                                    }
                                    $result = $salesManago->sendEvent($PrenotazioneId, 
                                        ServiceSalesManago::EVENT_PURCHASE, 
                                        $event['Email'], 
                                        $event['Products'],
                                        $event['Value'],
                                        $langEv2,
                                        $event['Detail1'], 
                                        $event['Detail2'],
                                        $event['ContactPhone'],
                                        $event['ContactName'],
                                        $event['ContactCountry'],
                                        null,
                                        'web paypal',
                                        $consentDetails2
                                    );
                                }
                            }
                        }
                    } else {
                        // Transazione già esiste - mostra successo
                        $cliente_nome = $rowP['ClienteNome'];
                        $error = false;
                    }
                } else if (isset($_GET['tx']) && isset($_GET['st']) && $_GET['st'] != 'Completed') {
                    // PayPal return ma pagamento non completato
                    $paypal_pending = true;
                    $error = false;
                }
            }
            
        } else {
            $reply = "REDIRECT=" . "https://booking.bertoldiboats.com/errore.php";
            ?>
            <script>
                self.location="errore.php";    
            </script>   
            <?php 
        }
    } else {
        ?>
        <script>
            self.location="errore.php";    
        </script>   
        <?php 
    }
}
?>

</head>
<body class="main-bg" id="home-applicativo">
    
    <!-- Top Header
        ================================================== -->
        <?php include_once($basepath."/include/top_header.php"); ?>
        
        
    <div class="main-container">
        <div class="content">
            <div style="margin-bottom:10px;" class="benvenuto-user"><?=$dizionario['prenota']['benvenuto']?> <b><?php 
                if(isset($user)){
                        echo $user['Nome'].' '.$user['CognomeRagioneSociale'];
                } else {
                        echo $dizionario['prenota']['ospite'];
                } ?>
                </b>
                <?php
                    if(isset($_SESSION['gestore']) && $_SESSION['gestore'] != -1){
                        echo '<br>'.$dizionario['prenota']['prenotazione_gestita_da'].'<b>'.$_SESSION['gestore']['RagioneSociale']."</b>";
                    }
                ?>
            </div>
            <div style="margin-bottom:10px;margin-top:0px;">
                <h3 style="margin-top:0px;"><b><?=$PageTitle?></b></h3>
            </div>
            <div class="ticket-list">
                
                <div class="filters" style="text-align:center !important;">
                  <span><?=$PageTitle?></span>
                </div>
                <div class="allTicket">
                    <div class="row">
                        <div class="col-md-12">
                            <div style="margin:15px !important;">
                            
								<?php if ($tipo == 'coupon') { ?>
									<div class="info">
										<div class="eight columns alpha" style="text-align: right; float: right;
											width: 300px;
											margin-right: 100px;">
										</div><!-- eight -->
										<div class="seven columns omega">
											<?php  if ($error==false) { ?>
												<p class="desc"><?=$dizionario['grazie']['messaggio_coupon'];?></p>
											<?php } else { ?>
												<p class="desc"><?=$dizionario['grazie']['messaggio_ok1'];?></p>
											<?php } ?>
										</div><!-- eight -->
										<br/><br/>
										<div class="clear"></div>
									</div>
								<?php } else { ?>
									<?php  if ($error==false) { ?>
										<p class="big_text">
											<span><?=$dizionario['grazie']['messaggio_ok'];?></span>
											<br/>
											<?=$dizionario['grazie']['messaggio_ok2'];?>
										</p>
										<div class="info">
											<div class="eight columns alpha" style="text-align: right; float: right;
												width: 300px;
												margin-right: 100px;">
											</div><!-- eight -->
											<div class="seven columns omega">
												<p class="desc">
												<?=$dizionario['grazie']['messaggio_stampa'];?>
												</p>
												
											</div><!-- eight -->
											<br/><br/>
											<div class="clear"></div>
										</div><!-- info_andata -->
									<?php } else { ?>
										<p class="big_text">
											<span><?=$dizionario['grazie']['messaggio_ok1'];?></span>
											<br/>
											<?=$dizionario['grazie']['messaggio_ok3'];?>
										</p>
										
										<div class="info">
											<div class="eight columns alpha" style="text-align: right; float: right;
												width: 300px;
												margin-right: 100px;">
											</div><!-- eight -->
											<div class="seven columns omega">
												<p class="desc"><?=$dizionario['grazie']['messaggio_stampa'];?></p>
											</div><!-- eight -->
											<br/><br/>
											<div class="clear"></div>
										</div><!-- info_andata -->
									<?php } ?>
                                <?php } ?>
                                <div class="submit_holder">
                                    <div class="prezzo_show">
                                        <b><?=$dizionario['grazie']['grazie'];?></b>
                                    </div><!-- prezzo_show --><br/><br/>
                                    <button class="btn btn-primary w-full btn-big" type="button" onclick="window.location.href='/<?= $_SESSION['code_gestore'] ?>'">
                                        <?=$dizionario['grazie']['nuova'];?>
                                    </button>
                                </div><!-- submit_holder -->
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom
        ================================================== -->
        <?php include_once($basepath."/include/bottom.php"); ?>
             
        
        <!-- Footer
        ================================================== -->
        <?php include_once($basepath."/include/footer.php"); ?>    
        
    </div>
    <!-- #page -->
  
       <?php include_once($basepath."/include/html_close.php"); ?>
            </script>

</body>
</html>

<script type="text/javascript">
$(document).ready(function() {
    <?php if ($tipo != 'coupon') { ?>
        <?php if ($error == false) { ?>

            // Invio evento purchase per prenotazioni completate con successo
            if (typeof dataLayer !== 'undefined') {
                <?php
                // Recupera dati completi della prenotazione per l'evento purchase
                if (isset($PrenotazioneId)) {
                    $sql = "SELECT 
                                p.*,
                                p.TotaleDaPagare AS ImportoPagato,
                                p.CodicePrenotazione AS CodiceTransazione,
                                l.LineaNome,
                                l.LineaDescrizione,
                                c.CorsaNome,
                                ppd.CorsaDataPartenza as CorsaData,
                                ppd.CorsaOrarioPartenza as CorsaOra,
                                p.TotalePaxPrenotati AS NumeroPasseggeri
                            FROM
                                RT_Prenotazione p
                                    LEFT JOIN
                                RT_PrenotazionePercorso ppd ON p.PrenotazioneId = ppd.PrenotazioneId
                                    LEFT JOIN
                                RT_Linea l ON ppd.LineaId = l.LineaId
                                    LEFT JOIN
                                RT_Corsa c ON ppd.CorsaId = c.CorsaId
                            WHERE p.PrenotazioneId = $PrenotazioneId
                            GROUP BY p.PrenotazioneId";
                    $bookingData = $db->query_first($sql);
                    
                    if (!empty($bookingData)) {
                ?>
                dataLayer.push({
                    event: "purchase",
                    user_email: '<?= addslashes($bookingData['ClienteEmail']) ?>',
                    ecommerce: {
                        transaction_id: "<?= $bookingData['CodiceTransazione'] ?>",
                        value: <?= floatval($bookingData['ImportoPagato']) ?>,
                        currency: "EUR",
                        items: [{
                            item_id: "BOAT_<?= $bookingData['PrenotazioneId'] ?>",
                            item_name: "<?= addslashes($bookingData['LineaNome']) ?>",
                            affiliation: "Bertoldi Boats",
                            item_category: "Tour Barca",
                            item_category2: "<?= $bookingData['TipoTour'] == 1 ? 'Tour Privato' : 'Tour Gruppo' ?>",
                            item_category3: "<?= addslashes($bookingData['CorsaNome']) ?>",
                            item_variant: "<?= date('d/m/Y', strtotime($bookingData['CorsaData'])) ?> - <?= $bookingData['CorsaOra'] ?>",
                            price: <?= floatval($bookingData['ImportoPagato']) ?>,
                            quantity: <?= intval($bookingData['NumeroPasseggeri']) ?>,
                            location_id: "lago_garda",
                            custom_parameters: {
                                booking_date: "<?= $bookingData['CorsaData'] ?>",
                                booking_time: "<?= $bookingData['CorsaOra'] ?>",
                                passengers: <?= intval($bookingData['NumeroPasseggeri']) ?>,
                                tour_type: "<?= $bookingData['TipoTour'] == 1 ? 'private' : 'group' ?>"
                            }
                        }]
                    }
                });
                <?php 
                    }
                }
                ?>
            }
        <?php } ?>
    <?php } else { ?>
        <?php if ($error == false && isset($CouponId)) { ?>
            // Invio evento purchase per coupon completati con successo
            if (typeof dataLayer !== 'undefined') {
                <?php
                // Recupera dati completi del coupon per l'evento purchase
                $sql = "SELECT 
                            c.*, 
                            ct.mc_gross as ImportoPagato,
                            ct.CodiceTransazione
                        FROM RT_Coupon c
                        LEFT JOIN RT_CouponTransazione ct ON c.CouponId = ct.CouponId
                        WHERE c.CouponId = $CouponId";
                $couponData = $db->query_first($sql);
                
                if (!empty($couponData)) {
                ?>
                dataLayer.push({
                    event: "purchase",
                    user_email: '<?= addslashes($couponData['VenditaEmail']) ?>',
                    ecommerce: {
                        transaction_id: "<?= $couponData['CodiceTransazione'] ?>",
                        value: <?= floatval($couponData['ImportoPagato']) ?>,
                        currency: "EUR",
                        items: [{
                            item_id: "COUPON_<?= $couponData['CouponId'] ?>",
                            item_name: "Buono Regalo Bertoldi Boats",
                            affiliation: "Bertoldi Boats",
                            item_category: "Gift Voucher",
                            item_category2: "Buono Regalo",
                            price: <?= floatval($couponData['ImportoPagato']) ?>,
                            quantity: 1,
                            location_id: "lago_garda",
                            custom_parameters: {
                                voucher_value: <?= floatval($couponData['Valore']) ?>,
                                voucher_code: "<?= $couponData['CodiceCoupon'] ?>"
                            }
                        }]
                    }
                });
                <?php 
                }
                ?>
            }
        <?php } ?>
    <?php } ?>
});
</script>


