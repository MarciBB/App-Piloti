<?php
/**
 * Sistema di Login e Gestione Password
 * Gestisce l'autenticazione degli operatori, recupero password e rilevamento browser
 * 
 * @package Bertoldi Gestionale
 * @version 1.0
 */

// Inizializzazione sistema
$basepath = $_SERVER['DOCUMENT_ROOT'];
include($basepath . "/main_include.php");

$config = new Config();
$run = $config->load();
$classespath_ = Config::$classespath;
include_once($classespath_ . "class.Operatore.php");

/**
 * Rileva il tipo di browser dell'utente
 * 
 * @param string $which_test Tipo di test da eseguire ('browser', 'number', 'full')
 * @return string|array Informazioni sul browser
 */
function browser_detection($which_test)
{
    // Inizializza variabili
    $browser_name = '';
    $browser_number = '';
    
    // Ottiene la stringa User Agent del browser
    $browser_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    
    // Array dei tipi di browser supportati
    // Struttura: [user agent identifier, dom browser, shorthand]
    $a_browser_types[] = array('opera', true, 'op');
    $a_browser_types[] = array('msie', true, 'ie');
    $a_browser_types[] = array('konqueror', true, 'konq');
    $a_browser_types[] = array('safari', true, 'saf');
    $a_browser_types[] = array('gecko', true, 'moz');
    $a_browser_types[] = array('mozilla/4', false, 'ns4');
    $a_browser_types[] = array('Chrome', false, 'ch');

    // Scansiona i tipi di browser per trovare una corrispondenza
    for ($i = 0; $i < count($a_browser_types); $i++) {
        $s_browser = $a_browser_types[$i][0];
        $b_dom = $a_browser_types[$i][1];
        $browser_name = $a_browser_types[$i][2];
        
        // Se l'identificatore del browser è trovato nella stringa User Agent
        if (stristr($browser_user_agent, $s_browser)) {
            // Per Mozilla, cerca 'rv' invece di 'gecko' per la versione
            if ($browser_name == 'moz') {
                $s_browser = 'rv';
            }
            $browser_number = browser_version($browser_user_agent, $s_browser);
            break;
        }
    }
    
    // Restituisce il risultato basato sul tipo di test richiesto
    if ($which_test == 'browser') {
        return $browser_name;
    } elseif ($which_test == 'number') {
        return $browser_number;
    } elseif ($which_test == 'full') {
        // Restituisce array con entrambe le informazioni
        $a_browser_info = array($browser_name, $browser_number);
        return $a_browser_info;
    }
    
    // Fallback per parametri non validi
    return '';
}/**
 * Estrae il numero di versione del browser dalla stringa User Agent
 * 
 * @param string $browser_user_agent Stringa User Agent del browser
 * @param string $search_string Stringa da cercare per trovare la versione
 * @return string Numero di versione del browser
 */
function browser_version($browser_user_agent, $search_string)
{
    $string_length = 8; // Lunghezza massima per cercare un numero di versione
    $browser_number = ''; // Inizializza il numero del browser
    
    // Trova la posizione della stringa di ricerca
    $start_pos = strpos($browser_user_agent, $search_string);
    
    // Inizia la ricerca 1 carattere dopo la stringa di ricerca
    $start_pos += strlen($search_string) + 1;
    
    // Estrae il pezzo numerico più lungo, riducendo fino a zero
    for ($i = $string_length; $i > 0; $i--) {
        // Verifica che l'intera sottostringa sia numerica
        if (is_numeric(substr($browser_user_agent, $start_pos, $i))) {
            $browser_number = substr($browser_user_agent, $start_pos, $i);
            break;
        }
    }
    
    return $browser_number;
}

/**
 * Genera il form di login principale
 * Visualizza il modulo per l'autenticazione dell'operatore
 */
function load()
{
?>
    <div id="brain_loginformcenter" class="brain_boxLogin">
        <!-- Header con logo -->
        <div class="brain_titolo_login" style="width: 100%; margin: 0px 0px 50px 0px;">
            <img src="/images/logo_bertoldi.png" title="<?=Config::$application_name?>">
        </div>
        
        <!-- Contenuto principale del form di login -->
        <div id="brain_data-content" class="brain_data-contentLogin">   
            <h1>LOGIN</h1>
            <fieldset>		
                <form id="application_form" method="post">
                    <input type="hidden" name="action" value="login" />
                    
                    <dl>
                        <!-- Campo Username -->
                        <dt><label for="username">Username:</label></dt>
                        <dd><input id="username" name="username" type="text" class="required" /></dd>
                        
                        <!-- Campo Password -->
                        <dt><label for="password">Password:</label></dt>
                        <dd>
                            <input id="password" name="password" type="password" class="required" />
                        </dd>
                        
                        <!-- Codice Sede -->
                        <dt><label for="sede">Code Resale:</label></dt>
                        <dd><input id="sede" name="sede" type="text" class="required" /></dd>
                        
                        <!-- Selezione Lingua (nascosto) -->
                        <dt style="display:none;"><label for="lingua">Language:</label></dt>
                        <dd style="display:none;">
                            <select id="lingua" name="lingua" class="required">
                                <option value="it" selected>italian</option>
                                <option value="de">german</option>
                            </select>
                        </dd>
                        
                        <dd>
                            <!-- Link per recupero password (commentato) -->
                            <!--<a class="forgotpwd" href="http://<?=Config::$ServerName?>/?do=new_password">Ho dimenticato la password</a>-->
                        </dd>
                    </dl>
                    
                    <input type="submit" value="LOGIN" class="inputSubmitBtn" />
                </form>
            </fieldset>
        </div>
        
        <!-- Footer -->
        <div class="loginFooter">
            <p><?=Config::$application_name?> - Online Ticket</p>
            <p>&copy; 2012 - <?=(date('Y', time()));?> - All rights reserved</p>
            <p></p>
            <p>powered by 
                <br/><a href="http://www.braincomputing.com" title="vai al sito di Brain Computing" target="_blank">
                    <img style="width: 120px; margin: 5px;" src="/images/company_logo.png"/>
                </a>
            </p>
        </div>
    </div>        
<?php    
}

/**
 * Genera il form per il recupero password
 * Visualizza il modulo per richiedere il reset della password
 */
function new_password_load()
{
?>
    <div id="brain_loginformcenter" class="brain_boxLogin">
        <h1><?=Config::$application_name?></h1>
        
        <div id="brain_data-content" class="brain_data-contentLogin">   
            <h2>Recupera password</h2>
            <fieldset>		
                <p>Per recuperare la password, inserire la propria username. Verrà inviata una email associata alla username contenente la nuova password.</p>
                
                <form id="application_form" method="post">
                    <input type="hidden" name="action" value="new_password" />
                    
                    <dl>
                        <dt><label for="username">Username:</label></dt>
                        <dd><input id="username" name="username" type="text" class="required" /></dd>
                    </dl>
                    
                    <dl>
                        <dt>&nbsp;</dt>
                        <dd>
                            <input type="submit" value="RECUPERA PASSWORD" class="ForgotSubmitBtn" /> 
                            <a class="forgotpwd" href="?do=login">Indietro</a>
                        </dd>
                    </dl>
                </form>
            </fieldset>
        </div>  
    </div>        
<?php   
}

/**
 * Gestisce il form per l'inserimento di una nuova password
 * Visualizza il modulo per impostare una nuova password dopo il reset
 */
function get_new_password()
{
    $user_rnd = $_REQUEST['rnd'];

    $db = new Database();
    $db->connect();
    $user = new Operatore();
    $user->conn = $db;
    
    // Verifica il codice di reset password
    $username = $user->PwdResetByRnd($user_rnd);
    
    if ($username != '0') {
?>
        <div id="brain_loginformcenter" class="brain_boxLogin">
            <h1><?=Config::$application_name?></h1>
            
            <div id="brain_data-content" class="brain_data-contentLogin">   
                <h2>Inserisci la nuova password</h2>                   
                <fieldset>            	
                    <form id="application_form" method="post">
                        <input type="hidden" name="action" value="set_password" id="action" />
                        
                        <dl>
                            <!-- Username (pre-compilato) -->
                            <dt><label for="username">Username:</label></dt>
                            <dd><input id="username" name="username" type="text" value="<?=$username?>" class="required" /></dd>
                            
                            <!-- Nuova Password -->
                            <dt><label for="password">Password:</label></dt>
                            <dd><input id="password" name="password" type="password" class="required"/></dd>
                            
                            <!-- Conferma Password -->
                            <dt><label for="password">Conferma Password:</label></dt>
                            <dd><input id="co_password" name="co_password" type="password" class="required"/></dd>
                        </dl>
                        
                        <dl>
                            <dt>&nbsp;</dt>
                            <dd>
                                <input type="submit" value="Invia richiesta" class="ForgotSubmitBtn" /> 
                                <a class="forgotpwd" href="?do=login">Vai al login</a>
                            </dd>
                        </dl>                                         
                    </form>
                </fieldset>    
            </div>  
        </div>
<?php
    } else {
        // Se il codice non è valido, mostra il form di login
        load();
    }
}

/**
 * Verifica le credenziali di login
 * Autentica l'operatore e lo reindirizza alla pagina principale
 * 
 * @return boolean True se il login è riuscito, false altrimenti
 */
function check()
{
    // Recupera i dati dal form
    $user_post = $_POST['username'];
    $pw_post = $_POST['password'];
    $sede_post = $_POST['sede']; 
    $lingua_post = $_POST['lingua'];

    // Connessione al database
    $db = new Database();
    $db->connect();
    
    // Crea oggetto Operatore per l'autenticazione
    $user = new Operatore();
    $user->conn = $db;
    
    // Tentativo di login
    if ($user->login($user_post, $pw_post, $sede_post)) {
        // Verifica se l'utente è moroso
        if ($user->Moroso == 1) {
?>
            <script type="text/javascript">
                alert("A causa di problemi amministrativi l'accesso al sistema è stato temporaneamente sospeso. Contattare Brain Computing S.p.A al numero 06.452217064");
            </script>
<?php
        } else {
            // Login riuscito: salva l'operatore in sessione
            $_SESSION['OPERATORE'] = serialize($user);
            $_SESSION['LINGUA'] = $lingua_post;
            
            // Reindirizza alla pagina principale
?>
            <script language="javascript">
                document.location.href = "principale.php?start=1";
            </script>
<?php
        }
        return true;
    } else {
        // Login fallito
?>
        <script type="text/javascript">
            alert("Username o Password non validi!");
        </script>
<?php
        return false;
    }	
}

/**
 * Gestisce la richiesta di recupero password
 * Invia una email con il link per il reset della password
 * 
 * @return int 1 se l'email è stata inviata, 0 altrimenti
 */
function check_password()
{
    $user_post = $_POST['username'];   
    
    if ($user_post != "") {
        // Connessione al database
        $db = new Database();
        $db->connect();
        
        // Crea oggetto Operatore
        $user = new Operatore();
        $user->conn = $db;
        
        // Verifica se l'username esiste (nota: potrebbe servire un secondo parametro email)
        $OpeId = $user->UsernameExistByUser($user_post, ''); // Aggiunto parametro vuoto per email
        
        if ($OpeId > 0) {
            // Username trovato: invia email di reset
            $user->SendMailResetPassword($OpeId);
?>
            <script type="text/javascript">
                alert("Email inviata con il link per il recupero password");
            </script>
<?php
            return 1;
        } else {
            // Username non trovato
?>
            <script type="text/javascript">
                alert("Username non valido!");
            </script>
<?php 
            return 0;
        }
    }
    return 0;
}

/**
 * Imposta una nuova password per l'utente
 * Aggiorna la password nel database
 * 
 * @return int 1 se la password è stata cambiata con successo
 */
function set_password()
{
    $user_post = $_POST['username'];
    $pw_post = $_POST['password'];  

    // Connessione al database
    $db = new Database();
    $db->connect();
    
    // Crea oggetto Operatore
    $user = new Operatore();
    $user->conn = $db;
    
    // Imposta la nuova password
    $user->SetPasswordByUser($user_post, $pw_post);    
?>
    <script type="text/javascript">
        alert("Password cambiata con successo");
    </script>
<?php
    return 1;
}

// Inizializza la pagina HTML
$page = new Html_Page();
$page->html_header();
?>
<!-- Stili CSS per la pagina di login -->
<link rel="stylesheet" type="text/css" href="/css/login.css" />

<!-- JavaScript per validazione form -->
<script type="text/javascript">
    /**
     * Valida che le password corrispondano durante il reset
     * @return {boolean} True se le password corrispondono o non è un reset password
     */
    function validate_password() {
        var password = $('#password').val();
        var confirmPassword = $('#co_password').val();
        var action = $('#action').val();
        
        // Se è un'azione di set password, verifica che le password corrispondano
        if (((password == confirmPassword) && (action == 'set_password')) || (action != 'set_password')) {
            return true;
        } else {
            return false;
        }
    }
    
    // Inizializzazione jQuery
    $(document).ready(function() {
        // Validazione form con AJAX
        $("#application_form").validate({
            submitHandler: function(form) {
                if (validate_password()) {
                    $(form).ajaxSubmit();
                } else {
                    alert("Le password non corrispondono. Riprova.");
                }
            }
        });      
    }); 
</script>
<?php

/* ===== GESTIONE ROUTING PRINCIPALE ===== */

// Gestione delle azioni POST (form submission)
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case "login":
            // Tentativo di login
            if (check()) {	
                header("location: principale.php?start=1");
                exit();
            } else {
                load(true);
            }
            break;
            
        case "new_password":
            // Richiesta recupero password
            if (!(check_password())) {
                new_password_load();
            } else {
                header("location: principale.php?start=1");    
            }
            break;
            
        case "set_password":
            // Impostazione nuova password
            if (set_password()) {
                header("location: principale.php?start=1");
            }
            break; 
    }
}
// Gestione delle azioni GET (navigazione)
else if (isset($_REQUEST['do'])) {    
    switch ($_REQUEST['do']) {
        case "new_password":
            // Mostra form recupero password
            new_password_load();
            break;
            
        case "get_password":
            // Mostra form impostazione nuova password
            get_new_password();
            break;
            
        default:
            // Mostra form di login di default
            load(false);   
    }
}
// Se l'utente è già loggato, logout e redirect
else if (is_object($user)) {    
    unset($_SESSION['OPERATORE']);
    // session_destroy(); // Commentato per preservare altre variabili di sessione
    header("location: principale.php?start=1");
    exit();
}
// Caso di default: mostra il form di login
else {   
    load(false);   
}

// Chiusura pagina HTML
$page->html_footer();
?>