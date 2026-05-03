<?php
//Autore: Marco Casaburi - Modificato per acquisto coupon
//Data ultima modifica: 17/07/2025
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath."/main_include.php");
use Stripe\Stripe;

$PageTitle = $dizionario['coupon']['titolo'];
$PageDescription = $dizionario['coupon']['descr'];
$PageKeywords = $dizionario['coupon']['key'];
$page_title = $PageTitle;

$config = new Config();
$run = $config->load();
$classespath_ = Config::$classespath;
$db = new Database();
$conn = $db->connect();

ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

// Controllo che i dati del coupon siano stati inviati
if(!isset($_POST['email_acquirente']) || !isset($_POST['pagamento'])) {
    header("Location: /acquista-coupon.php");
    exit;
}

// Recupero dati dal POST
$email_acquirente = $_POST['email_acquirente'];
$tipo_coupon = $_POST['tipo_coupon'];
$email_destinatario = isset($_POST['email_destinatario']) ? $_POST['email_destinatario'] : null;
$messaggio_destinatario = isset($_POST['messaggio_destinatario']) ? $_POST['messaggio_destinatario'] : null;
$importo_predefinito = isset($_POST['importo']) ? $_POST['importo'] : null;
$importo_custom = isset($_POST['importo_custom']) ? $_POST['importo_custom'] : null;
$metodo_pagamento = $_POST['pagamento'];

// Determino l'importo finale
if($importo_custom && $importo_custom >= 10 && $importo_custom <= 1000) {
    $importo_finale = $importo_custom;
} else if($importo_predefinito) {
    $importo_finale = $importo_predefinito;
} else {
    header("Location: /acquista-coupon.php");
    exit;
}

// Determino se è un buono regalo
$buono_regalo = ($tipo_coupon === 'regalo') ? 1 : 0;

// Genero codice coupon univoco
function generaCodiceCoupon() {
    global $db;
    do {
        $caratteri = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $codice = '';
        for ($i = 0; $i < 10; $i++) {
            $codice .= $caratteri[rand(0, strlen($caratteri) - 1)];
        }
        $sql = "SELECT CouponId FROM RT_Coupon WHERE Codice = '$codice'";
        $exists = $db->query_first($sql);
    } while($exists);
    return $codice;
}

$codice_coupon = generaCodiceCoupon();

// Recupero user dalla sessione
$membershipClubCode = null;
if(isset($_SESSION['USER'])){
   $user = $_SESSION['USER'];
   if(isset($user['MembershipClubCode']) && !empty($user['MembershipClubCode'])){
       $membershipClubCode = $user['MembershipClubCode'];
   }
}

// Inserisco il coupon nel database
$dataCoupon = array();
$dataCoupon['CouponNome'] = ($buono_regalo ? 'Buono Regalo Bertoldi Boats' : 'Coupon Bertoldi Boats') . ' - ' . date('d/m/Y');
$dataCoupon['Codice'] = $codice_coupon;
$dataCoupon['Importo'] = $importo_finale;
$dataCoupon['Valore'] = $importo_finale;
$dataCoupon['MaxUtilizzi'] = 1;
$dataCoupon['Utilizzi'] = 0;
$dataCoupon['DaVendere'] = 1;
$dataCoupon['VenditaStato'] = 1; // In attesa di pagamento
$dataCoupon['VenditaEmail'] = $email_acquirente;
$dataCoupon['VenditaEmailDestinatario'] = $email_destinatario;
$dataCoupon['VenditaMessaggioDestinatario'] = $messaggio_destinatario;
$dataCoupon['VenditaBuonoRegalo'] = $buono_regalo;
$dataCoupon['ValidoA'] = date('Y-m-d', strtotime('+1 year'));
$dataCoupon['Lingua'] = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'it';
$dataCoupon['MembershipClubCode'] = $membershipClubCode; // Aggiungo MembershipClubCode
$dataCoupon['DataIns'] = date('Y-m-d H:i:s');
$dataCoupon['OpeIns'] = 5;
$dataCoupon['SedeIns'] = 1;
$dataCoupon['IpIns'] = $_SERVER['REMOTE_ADDR'];
$dataCoupon['OdcIdRef'] = 1;
$dataCoupon['GestoreIdRef'] = 1;
$dataCoupon['Cancella'] = 0;
$dataCoupon['Stato'] = 1;

$couponId = $db->insert("RT_Coupon", $dataCoupon);

if(!$couponId) {
    die("Errore durante la creazione del coupon");
}

include_once($basepath . "/include/meta.php");
?>

<body class="main-bg" id="home-applicativo">
    <!-- Top Header -->
    <?php include_once($basepath."/include/top_header.php"); ?>
    
    <div class="main-container">
        <div class="content">
            <div style="margin-bottom:10px;" class="benvenuto-user">
                <?=$dizionario['coupon']['titolo']?>
            </div>
            
            <div class="ticket-list">
                <div class="allTicket">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="coupon-splash-container">
                                <div class="coupon-splash-header">
                                    <h3><?=$dizionario['coupon']['elaborazione_pagamento']?></h3>
                                    <p><?=$dizionario['coupon']['reindirizzamento_pagamento']?></p>
                                </div>
                                
                                <div class="coupon-splash-details">
                                    <div class="coupon-detail-row">
                                        <strong><?=$dizionario['coupon']['tipo_coupon']?>:</strong>
                                        <span><?=$buono_regalo ? $dizionario['coupon']['buono_regalo'] : $dizionario['coupon']['uso_personale']?></span>
                                    </div>
                                    <div class="coupon-detail-row">
                                        <strong><?=$dizionario['coupon']['codice']?>:</strong>
                                        <span><?=$codice_coupon?></span>
                                    </div>
                                    <div class="coupon-detail-row">
                                        <strong><?=$dizionario['coupon']['importo']?>:</strong>
                                        <span>€ <?=number_format($importo_finale, 2, ',', '.')?></span>
                                    </div>
                                    <div class="coupon-detail-row">
                                        <strong><?=$dizionario['coupon']['email_acquirente']?>:</strong>
                                        <span><?=$email_acquirente?></span>
                                    </div>
                                    <?php if($buono_regalo && $email_destinatario): ?>
                                    <div class="coupon-detail-row">
                                        <strong><?=$dizionario['coupon']['email_destinatario']?>:</strong>
                                        <span><?=$email_destinatario?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="coupon-splash-loader">
                                    <div class="loader"></div>
                                    <p><?=$dizionario['coupon']['elaborazione_corso']?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Bottom -->
        <?php include_once($basepath."/include/bottom.php"); ?>
        
        <!-- Footer -->
        <?php include_once($basepath."/include/footer.php"); ?>    
        
    </div>
    
    <?php include_once($basepath."/include/html_close.php"); ?>   

</body>

<style>
.coupon-splash-container {
    background: #fff;
    border-radius: 8px;
    padding: 40px;
    margin: 30px auto;
    max-width: 600px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    text-align: center;
}

.coupon-splash-header {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.coupon-splash-header h3 {
    color: #007bff;
    margin-bottom: 10px;
    font-size: 24px;
}

.coupon-splash-header p {
    color: #666;
    margin-bottom: 0;
}

.coupon-splash-details {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 30px;
    text-align: left;
}

.coupon-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #dee2e6;
}

.coupon-detail-row:last-child {
    border-bottom: none;
}

.coupon-detail-row strong {
    color: #333;
    font-weight: 600;
}

.coupon-detail-row span {
    color: #666;
}

.coupon-splash-loader {
    text-align: center;
}

.loader {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.coupon-splash-loader p {
    color: #666;
    margin: 0;
}

@media (max-width: 768px) {
    .coupon-splash-container {
        margin: 20px;
        padding: 25px;
    }
    
    .coupon-detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}
</style>

<?php
// Determino il metodo di pagamento e preparo i form
$url_sito = Config::$UrlDominio;
$bank_pagina_grazie = $url_sito."/grazie.php?OrderId=".$couponId."&em=".$email_acquirente."&type=coupon";
$base_url = $url_sito;

if ($metodo_pagamento == "paypal") {
    $PAYPAL_LINK = Config::$PayPalUrl;
    $PAYPAL_MAIL = Config::$PayPalEmail;
    
    echo "
    <form id=\"form_paypal\" target=\"_top\" name=\"form_paypal\" action=\"$PAYPAL_LINK\" method=\"post\">
        <input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
        <input type=\"hidden\" name=\"business\" value=\"$PAYPAL_MAIL\">
        <input type=\"hidden\" name=\"item_name\" value=\"Coupon Bertoldi Boats\">
        <input type=\"hidden\" name=\"item_number\" value=\"COUPON_$couponId\">
        <input type=\"hidden\" name=\"amount\" value=\"$importo_finale\">
        <input type=\"hidden\" name=\"page_style\" value=\"Primary\">
        <input type=\"hidden\" name=\"return\" value=\"$bank_pagina_grazie\">
        <input type=\"hidden\" name=\"cancel_return\" value=\"$base_url\">
        <input type=\"hidden\" name=\"no_note\" value=\"COUPON_$couponId\">
        <input type=\"hidden\" name=\"currency_code\" value=\"EUR\">
        <input type=\"hidden\" name=\"custom\" value=\"COUPON_$couponId\">
    </form>
    ";
    
    ?>
    <script language="javascript">
        setTimeout(function() {
            document.getElementById('form_paypal').submit();
        }, 3000);
    </script>
    <?php
    
} elseif ($metodo_pagamento == "stripe") {
    
    Stripe::setApiKey($config::$StripeSecretKey);
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Coupon Bertoldi Boats',
                    'description' => 'Codice: ' . $codice_coupon,
                ],
                'unit_amount' => $importo_finale * 100,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $bank_pagina_grazie.'&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $base_url,
        'metadata' => [
            'type' => 'coupon',
            'coupon_id' => $couponId,
            'coupon_code' => $codice_coupon
        ]
    ]);
    ?>
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/javascript">
        setTimeout(function() {
            var stripe = Stripe('<?php echo $config::$StripePublicKey?>');
            stripe.redirectToCheckout({ sessionId: '<?php echo $session->id;?>' });
        }, 3000);
    </script>
    <?php   
}
?>

<!-- Aggiungi le voci del dizionario se non esistono -->
<script>
// Aggiungi un timeout di sicurezza per evitare che la pagina rimanga bloccata
setTimeout(function() {
    if(document.getElementById('form_paypal') || window.stripe) {
        // Il redirect dovrebbe essere già avvenuto
    } else {
        // Fallback: torna alla homepage
        window.location.href = '/';
    }
}, 10000); // 10 secondi di timeout
</script>

</html>