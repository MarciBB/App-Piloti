<?php
$basepath = $_SERVER['DOCUMENT_ROOT'];
include_once($basepath . "/main_include.php");
$config = new Config();
$run = $config->load();
$modulespath_ = Config::$modulespath;
$classespath_ = Config::$classespath;
$errors = new Errors();
error_reporting(E_ALL);
ini_set('display_errors', 0);

global $user, $dizionario;

// Funzione per mostrare la schermata di errore
function show_payment_error() {
    ?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Pagamento non valido / Payment not valid</title>
        <link rel="stylesheet" type="text/css" href="/css/reset.css" />
        <link rel="stylesheet" type="text/css" href="/css/style.css" />
        <style>
            body {
                background: #f7f7f7;
                font-family: 'Open Sans', Arial, sans-serif;
                color: #333;
                text-align: center;
            }
            .pg_grazie {
                margin: 60px auto 0 auto;
                padding: 40px 30px;
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 2px 12px rgba(0,0,0,0.08);
                max-width: 420px;
            }
            .pg_grazie h2 {
                color: #c0392b;
                font-size: 1.5em;
                margin-bottom: 18px;
            }
            .pg_grazie .desc {
                color: #555;
                font-size: 1em;
                margin-bottom: 28px;
            }
            .btn-home {
                display: inline-block;
                padding: 12px 32px;
                background: #2980b9;
                color: #fff;
                border: none;
                border-radius: 6px;
                font-size: 1em;
                cursor: pointer;
                text-decoration: none;
                transition: background 0.2s;
            }
            .btn-home:hover {
                background: #1c5d8f;
            }
            .logo {
                width: 120px;
                margin-bottom: 22px;
            }
            .countdown {
                font-weight: bold;
                color: #c0392b;
            }
        </style>
        <script>
            var seconds = 10;
            function updateCountdown() {
                document.getElementById('countdown').textContent = seconds;
                if (seconds > 0) {
                    seconds--;
                    setTimeout(updateCountdown, 1000);
                } else {
                    window.location.href = "<?php echo Config::$UrlDominio; ?>";
                }
            }
            window.onload = updateCountdown;
        </script>
    </head>
    <body>
        <div class="pg_grazie">
            <img src="https://booking.bertoldiboats.com/images/logo.png" alt="Bertoldi Boats" class="logo" />
            <h2>
                Il link al pagamento non è più valido<br>
                <span style="font-size:1em;color:#555;">Payment link is no longer valid</span>
            </h2>
            <div class="desc">
                Contatta lo staff per assistenza.<br>
                Se hai bisogno di aiuto, puoi chiamarci o scriverci alla 
                email <a href="mailto:info@bertoldiboats.com">info@bertoldiboats.com</a>
                <hr style="margin:18px 0;">
                Please contact our staff for assistance.<br>
                If you need help, you can call or email us at
                email <a href="mailto:info@bertoldiboats.com">info@bertoldiboats.com</a>
            </div>
            <a class="btn-home" href="<?php echo Config::$UrlDominio; ?>">Torna alla Home / Back to Home</a>
            <div style="margin-top:18px;font-size:0.95em;color:#888;">
                Verrai reindirizzato automaticamente tra <span class="countdown" id="countdown">10</span> secondi.<br>
                You will be automatically redirected in <span class="countdown" id="countdown_en">10</span> seconds.
            </div>
        </div>
        <script>
            // Aggiorna anche il countdown inglese
            var seconds_en = 10;
            function updateCountdownEn() {
                document.getElementById('countdown_en').textContent = seconds_en;
                if (seconds_en > 0) {
                    seconds_en--;
                    setTimeout(updateCountdownEn, 1000);
                }
            }
            window.onload = function() {
                updateCountdown();
                updateCountdownEn();
            };
        </script>
    </body>
    </html>
    <?php
    exit();
}

// Controllo session_id
if (!isset($_REQUEST['session_id'])) {
    show_payment_error();
}

$sessionId = $_REQUEST['session_id'];

// --- CONTROLLO STATO PRENOTAZIONE ---
$db = new Database();
$db->connect();

// Recupera la prenotazione associata al sessionId
$row = $db->query_first("SELECT PrenotazioneId FROM RT_PrenotazioneStripeLink WHERE SessionId = '" . $db->escape($sessionId) . "'");
if (!$row || !isset($row['PrenotazioneId'])) {
    show_payment_error();
}
$prenotazioneId = $row['PrenotazioneId'];

// Recupera lo stato della prenotazione
$rowStato = $db->query_first("SELECT PrenotazioneStato FROM RT_Prenotazione WHERE PrenotazioneId = " . intval($prenotazioneId));
if (!$rowStato || !isset($rowStato['PrenotazioneStato'])) {
    show_payment_error();
}
$stato = $rowStato['PrenotazioneStato'];

// Se lo stato è 4 o 6 mostra schermata di errore
if ($stato == 4 || $stato == 6 || $stato == 7 || $stato == 8 || $stato == 5 || $stato == 16) {
	// Se lo stato è 4, 6, 7, 8, 5 o 16, mostra la schermata di errore
	// Non è possibile effettuare il pagamento
	show_payment_error();
}

// --- FINE CONTROLLO ---

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?=Config::$application_name?> Ver. <?=Config::$application_version?> - <?=Config::$application_company?></title>
        <link rel="stylesheet" type="text/css" href="/css/reset.css" />
        <link rel="stylesheet" type="text/css" href="/css/style.css" />
        <link href="/js/jquery-ui-1.8.13.custom/css/smoothness/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css"/>
        <script type="text/javascript" src="/js/jquery.min.js"></script>
        <script src="/js/jquery-ui-1.8.13.custom/js/jquery-ui-1.8.13.custom.min.js"></script>
        <script src="https://js.stripe.com/v3/"></script>
    </head>
    <style>
        #body{
            margin:20px;
        }
        .pg_grazie{
            padding-top:50px;
        }
    </style>
    <body style="text-align: center;">
        <div class="pg_grazie"><span id="body"><h2>Pagamento in corso di registrazione...</h2></span></div>
    </body>
</html>

<script type="text/javascript">
    $(document).ready( function() {
        var stripe = Stripe('<?php echo Config::$StripeLinkPublicKey?>');
        stripe.redirectToCheckout({ sessionId: '<?php echo $sessionId;?>' });
    });
</script>